<?php

use App\Models\ContactInquiry;
use App\Models\PageView;
use App\Models\SiteSetting;
use App\Models\User;
use App\Services\HijriCalendar;
use App\Services\Holidays;
use App\Support\PrayerZones;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Factory;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

function icsEvent(string $date, string $summary): string
{
    return "BEGIN:VEVENT\r\nDTSTART;VALUE=DATE:{$date}\r\nSUMMARY:{$summary}\r\nEND:VEVENT\r\n";
}

function icsFeed(string ...$events): string
{
    return "BEGIN:VCALENDAR\r\n".implode('', $events)."END:VCALENDAR\r\n";
}

/** The next date (MYT) falling on the given weekday (0 = Sunday ... 6 = Saturday), at least $min days ahead. */
function futureDay(int $weekday, int $min = 5): CarbonImmutable
{
    $d = CarbonImmutable::now('Asia/Kuala_Lumpur')->startOfDay()->addDays($min);
    while ($d->dayOfWeek !== $weekday) {
        $d = $d->addDay();
    }

    return $d;
}

function fakeFeed(string ...$events): void
{
    Cache::flush();
    Http::swap(new Factory);
    Http::fake([
        'calendar.google.com/*' => Http::response(icsFeed(...$events)),
        'www.e-solat.gov.my/*' => Http::response([], 503),
        '*' => Http::response([], 503),
    ]);
}

beforeEach(function () {
    // All fixtures sit on weekdays so no replacement rows appear unless a test asks for them.
    fakeFeed(
        icsEvent(futureDay(3, 10)->format('Ymd'), 'Labour Day'),
        icsEvent(futureDay(3, 10)->addDay()->format('Ymd'), 'Valentine\'s Day'),
        icsEvent(futureDay(3, 10)->addDays(2)->format('Ymd'), 'Second Day of Harvest Festival (regional holiday)'),
        icsEvent(futureDay(4, 20)->format('Ymd'), 'Hari Raya Puasa (tentative)'),
        icsEvent(futureDay(2, 30)->format('Ymd'), 'Diwali (regional holiday)'),
    );
});

test('national holiday feed keeps gazetted holidays only and flags tentative dates', function () {
    $items = app(Holidays::class)->national(5)['items'];
    expect(collect($items)->pluck('name')->all())->toBe(['Hari Pekerja', 'Hari Raya Aidilfitri', 'Deepavali'])
        ->and($items[1]['tentative'])->toBeTrue()
        ->and($items[0]['tentative'])->toBeFalse()
        ->and($items[0]['days'])->toBe((int) CarbonImmutable::now('Asia/Kuala_Lumpur')->startOfDay()->diffInDays(futureDay(3, 10)));
});

test('holiday feed outage degrades gracefully', function () {
    Cache::flush();
    Http::swap(new Factory);
    Http::fake(['calendar.google.com/*' => Http::response('nope', 500)]);
    $result = app(Holidays::class)->national();
    expect($result['available'])->toBeFalse()->and($result['items'])->toBeEmpty();
});

test('state holidays follow the configured prayer zone', function () {
    config(['dashboard.prayer_zone' => 'SGR01']);
    $holidays = app(Holidays::class);
    expect($holidays->stateCode())->toBe('MY-10')->and($holidays->stateName())->toBe('Selangor')
        ->and($holidays->state(1)[0]['name'])->toBe('Hari Keputeraan Sultan Selangor');

    config(['dashboard.prayer_zone' => 'WLY02']);
    expect($holidays->stateCode())->toBe('MY-15');

    config(['dashboard.prayer_zone' => 'PLS01']);
    expect($holidays->state())->toBeEmpty();
});

test('hijri occasions are aligned to the official JAKIM date and sorted soonest first', function () {
    $hijri = new HijriCalendar('1448-06-07');
    expect($hijri->today()['month_name'])->toBe('Jamadilakhir');
    $upcoming = $hijri->upcoming(9);
    expect($upcoming)->not->toBeEmpty();
    $dates = array_map(fn ($o) => $o['date']->getTimestamp(), $upcoming);
    $sorted = $dates;
    sort($sorted);
    expect($dates)->toBe($sorted)->and($upcoming[0]['days'])->toBeGreaterThanOrEqual(0);
    expect(collect($upcoming)->pluck('name'))->toContain('Maulidur Rasul', 'Hari Raya Aidiladha');
    // Every occasion carries its Hijri date and a future Gregorian date.
    foreach ($upcoming as $o) {
        expect($o['hijri'])->toMatch('/^\d+ \w+ \d{4}$/')->and($o['date']->gte(CarbonImmutable::now('Asia/Kuala_Lumpur')->startOfDay()))->toBeTrue();
    }
});

test('hijri calendar still works when JAKIM is unavailable', function () {
    $hijri = new HijriCalendar(null);
    expect($hijri->today())->toBeNull()->and($hijri->upcoming(3))->toHaveCount(3);
});

test('redesigned dashboard shows overview, traffic, inbox, holidays and hijri calendar', function () {
    $admin = User::factory()->create(['is_admin' => true, 'name' => 'Alie Imran']);
    ContactInquiry::create(['name' => 'Siti', 'email' => 's@example.com', 'subject' => 'Hello there', 'message' => 'Hi', 'category' => 'general_inquiry', 'status' => 'new']);
    PageView::create(['path' => '/', 'visitor_hash' => 'a', 'device_type' => 'mobile', 'created_at' => now()]);

    $this->actingAs($admin)->get('/admin')->assertOk()
        ->assertSee('Alie.')
        ->assertSee('Traffic')->assertSee('Inbox')->assertSee('Siti')->assertSee('Hello there')
        ->assertSee('Next public holidays')->assertSee('Hari Pekerja')->assertSee('tentative')
        ->assertSee('State holidays')->assertSee('Hijri calendar')
        ->assertSee('Estimated. Official dates follow moon sighting')
        ->assertSee('1 new inquiry', false)
        ->assertSee('Top pages')->assertSee('Devices')->assertSee('Mobile')
        ->assertSee('trafficChart', false);
});

test('dashboard empty states render without data and all-clear shows when nothing needs attention', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $this->actingAs($admin)->get('/admin')->assertOk()
        ->assertSee('No messages yet')->assertSee('No visits recorded yet')->assertSee('No device data yet');
});

test('dashboard is admin only', function () {
    $this->get('/admin')->assertRedirect('/login');
    $this->actingAs(User::factory()->create(['is_admin' => false]))->get('/admin')->assertForbidden();
});

test('admin can change the prayer zone and it drives prayer times and the holiday state', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $this->actingAs($admin)->patch('/admin/dashboard/prayer-zone', ['prayer_zone' => 'SGR01'])
        ->assertRedirect('/admin')->assertSessionHas('status', 'Prayer zone set to SGR01 · Selangor.');

    expect(PrayerZones::current())->toBe('SGR01')
        ->and(app(Holidays::class)->stateCode())->toBe('MY-10');
    $this->actingAs($admin)->get('/admin')->assertOk()->assertSee('SGR01')->assertSee('Shah Alam')->assertSee('Change zone');
});

test('invalid or unknown prayer zones are rejected and non-admins cannot change it', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    foreach (['XXX99', '', 'sgr01; DROP', str_repeat('A', 40)] as $bad) {
        $this->actingAs($admin)->patch('/admin/dashboard/prayer-zone', ['prayer_zone' => $bad])->assertSessionHasErrors('prayer_zone');
    }
    expect(PrayerZones::current())->toBe(config('dashboard.prayer_zone'));

    $this->actingAs(User::factory()->create(['is_admin' => false]))->patch('/admin/dashboard/prayer-zone', ['prayer_zone' => 'SGR01'])->assertForbidden();
});

test('state holidays can combine two states, each tagged, soonest first', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $this->actingAs($admin)->patch('/admin/dashboard/prayer-zone', ['prayer_zone' => 'SGR01']);
    $this->actingAs($admin)->patch('/admin/dashboard/holiday-state', ['holiday_extra_state' => 'MY-14'])->assertRedirect('/admin');

    $holidays = app(Holidays::class);
    expect($holidays->stateCodes())->toBe(['MY-10', 'MY-14']);
    $items = $holidays->state(10);
    expect(collect($items)->pluck('states')->flatten()->unique()->sort()->values()->all())->toBe(['Kuala Lumpur / Putrajaya', 'Selangor'])
        ->and(collect($items)->pluck('date')->map->getTimestamp()->all())->toBe(collect($items)->pluck('date')->map->getTimestamp()->sort()->values()->all());

    $this->actingAs($admin)->get('/admin')->assertOk()
        ->assertSee('Selangor + Kuala Lumpur / Putrajaya')
        ->assertSee('Hari Keputeraan Sultan Selangor')->assertSee('Hari Wilayah Persekutuan');
});

test('the second holiday state is ignored when it equals the prayer-zone state and can be cleared', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $this->actingAs($admin)->patch('/admin/dashboard/prayer-zone', ['prayer_zone' => 'SGR01']);
    $this->actingAs($admin)->patch('/admin/dashboard/holiday-state', ['holiday_extra_state' => 'MY-10']);
    expect(app(Holidays::class)->stateCodes())->toBe(['MY-10']);

    $this->actingAs($admin)->patch('/admin/dashboard/holiday-state', ['holiday_extra_state' => 'MY-14']);
    $this->actingAs($admin)->patch('/admin/dashboard/holiday-state', ['holiday_extra_state' => ''])->assertSessionHasNoErrors();
    expect(app(Holidays::class)->stateCodes())->toBe(['MY-10']);

    $this->actingAs($admin)->patch('/admin/dashboard/holiday-state', ['holiday_extra_state' => 'MY-99'])->assertSessionHasErrors('holiday_extra_state');
    $this->actingAs(User::factory()->create(['is_admin' => false]))->patch('/admin/dashboard/holiday-state', ['holiday_extra_state' => 'MY-14'])->assertForbidden();
});

test('every configured prayer zone maps to a known state', function () {
    foreach (PrayerZones::codes() as $code) {
        $state = config('holidays.zone_override.'.$code) ?? config('holidays.state_by_zone_prefix.'.substr($code, 0, 3));
        expect(array_key_exists((string) $state, config('holidays.state_names')))->toBeTrue("Zone {$code} has no state mapping");
    }
});

test('a holiday on Sunday gets a replacement on the next working day', function () {
    $sunday = futureDay(0, 6);
    fakeFeed(icsEvent($sunday->format('Ymd'), 'Christmas Day'));

    $items = app(Holidays::class)->national(5)['items'];
    expect($items)->toHaveCount(2)
        ->and($items[0]['name'])->toBe('Hari Krismas')->and($items[0]['replacement'])->toBeFalse()
        ->and($items[1]['name'])->toBe('Cuti ganti Hari Krismas')->and($items[1]['replacement'])->toBeTrue()
        ->and($items[1]['date']->toDateString())->toBe($sunday->addDay()->toDateString());
});

test('the replacement skips a Monday that is already a holiday', function () {
    $sunday = futureDay(0, 6);
    fakeFeed(icsEvent($sunday->format('Ymd'), 'Hari Raya Puasa'), icsEvent($sunday->addDay()->format('Ymd'), 'Hari Raya Puasa Holiday Day 2'));

    $names = collect(app(Holidays::class)->national(5)['items']);
    $replacement = $names->firstWhere('replacement', true);
    expect($names)->toHaveCount(3)->and($replacement['name'])->toBe('Cuti ganti Hari Raya Aidilfitri')
        ->and($replacement['date']->toDateString())->toBe($sunday->addDays(2)->toDateString());
});

test('a holiday on Saturday is not replaced', function () {
    fakeFeed(icsEvent(futureDay(6, 6)->format('Ymd'), 'Malaysia Day'));
    expect(app(Holidays::class)->national(5)['items'])->toHaveCount(1);
});

test('Kedah, Kelantan and Terengganu replace Friday holidays and skip the Saturday weekend', function () {
    config(['dashboard.prayer_zone' => 'KTN01']);
    $friday = futureDay(5, 6);
    fakeFeed(icsEvent($friday->format('Ymd'), 'Labour Day'), icsEvent($friday->addDays(9)->format('Ymd'), 'Wesak Day'));

    $items = collect(app(Holidays::class)->national(10)['items']);
    $replacement = $items->firstWhere('name', 'Cuti ganti Hari Pekerja');
    expect($replacement['date']->toDateString())->toBe($friday->addDays(2)->toDateString())->and($replacement['date']->dayOfWeek)->toBe(0)
        ->and($items->pluck('name'))->not->toContain('Cuti ganti Hari Wesak'); // Sunday is a working day there
});

test('regional New Year, Thaipusam and Nuzul Al-Quran show for KL and Selangor, merged and tagged', function () {
    config(['dashboard.prayer_zone' => 'SGR01']);
    $newYear = CarbonImmutable::create(now()->year + 1, 1, 1, 0, 0, 0, 'Asia/Kuala_Lumpur');
    fakeFeed(icsEvent($newYear->format('Ymd'), "New Year's Day (regional holiday)"), icsEvent(futureDay(2, 3)->format('Ymd'), 'Thaipusam (regional holiday)'), icsEvent(futureDay(2, 40)->format('Ymd'), "Isra and Mi'raj (regional holiday)"));
    SiteSettingFixture::set('MY-14');

    $items = collect(app(Holidays::class)->state(20));
    $ny = $items->firstWhere('name', 'Tahun Baru');
    expect($ny)->not->toBeNull()->and($ny['states'])->toBe(['Selangor', 'Kuala Lumpur / Putrajaya'])
        ->and($items->firstWhere('name', 'Thaipusam')['states'])->toHaveCount(2)
        // Isra and Mi'raj applies to Selangor only
        ->and($items->firstWhere('name', 'Israk dan Mikraj')['states'])->toBe(['Selangor'])
        // each holiday appears once even though two states are selected
        ->and($items->where('name', 'Tahun Baru'))->toHaveCount(1);
});

test('regional holidays do not appear for states they are not configured for', function () {
    config(['dashboard.prayer_zone' => 'PNG01']);
    fakeFeed(icsEvent(futureDay(2, 3)->format('Ymd'), 'Thaipusam (regional holiday)'));
    expect(collect(app(Holidays::class)->state(20))->pluck('name'))->not->toContain('Thaipusam');
});

test("the feed's own replacement entries and observances are ignored; replacements are computed", function () {
    fakeFeed(
        icsEvent(futureDay(3, 5)->format('Ymd'), 'Day off for Thaipusam (regional holiday)'),
        icsEvent(futureDay(3, 6)->format('Ymd'), 'Hari Raya Haji holiday'),
        icsEvent(futureDay(3, 7)->format('Ymd'), 'Easter Sunday'),
        icsEvent(futureDay(3, 8)->format('Ymd'), 'Malaysia Day'),
    );
    expect(collect(app(Holidays::class)->national(10)['items'])->pluck('name')->all())->toBe(['Hari Malaysia']);
});

test('the dashboard labels replacement holidays', function () {
    fakeFeed(icsEvent(futureDay(0, 6)->format('Ymd'), 'Christmas Day'));
    $admin = User::factory()->create(['is_admin' => true]);
    $this->actingAs($admin)->get('/admin')->assertOk()->assertSee('Cuti ganti Hari Krismas')->assertSee('replacement');
});

final class SiteSettingFixture
{
    public static function set(string $extraState): void
    {
        SiteSetting::current()->update(['holiday_extra_state' => $extraState]);
    }
}
