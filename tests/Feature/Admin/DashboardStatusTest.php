<?php

use App\Models\User;
use App\Services\DependencyAdvisories;
use App\Services\PrayerTimes;
use App\Services\SystemStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Factory;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(function () {
    Cache::flush();
    Http::preventStrayRequests();
});

test('prayers use configured zone Malaysia date and cache valid JAKIM responses', function () {
    config(['dashboard.prayer_zone' => 'SGR01']);
    Http::fake(['www.e-solat.gov.my/*' => Http::response([
        'serverTime' => now('Asia/Kuala_Lumpur')->toDateString().' 10:00:00',
        'prayerTime' => [array_fill_keys(['imsak', 'fajr', 'syuruk', 'dhuha', 'dhuhr', 'asr', 'maghrib', 'isha'], '06:00:00')],
    ])]);
    $service = app(PrayerTimes::class);
    expect($service->today()['times']['Subuh'])->toBe('06:00');
    expect($service->today()['zone'])->toBe('SGR01');
    Http::assertSentCount(1);
    Http::assertSent(fn ($request) => $request['zone'] === 'SGR01' && $request['period'] === 'today');
});

test('stale and unavailable prayer times never display as today', function () {
    Http::fake(['www.e-solat.gov.my/*' => Http::response(['serverTime' => '2020-01-01 10:00:00', 'prayerTime' => [['fajr' => '06:00:00']]])]);
    expect(app(PrayerTimes::class)->today()['times'])->toBeNull();
    Cache::flush();
    Http::fake(['www.e-solat.gov.my/*' => Http::failedConnection()]);
    expect(app(PrayerTimes::class)->today()['times'])->toBeNull();
});

test('version labels require a maintained exact patch and flag outdated unsupported and unknown versions', function () {
    $service = app(SystemStatus::class);
    foreach ([
        ['8.4.27', false, true, 'Up to date'],
        ['8.4.26', false, true, 'Update available'],
        ['8.4.27', true, false, 'End of support'],
        ['8.4.28', false, true, 'Unknown'],
    ] as [$installed, $eol, $maintained, $status]) {
        Cache::flush();
        Http::swap(new Factory);
        Http::fake(['endoflife.date/*' => Http::response(['result' => ['latest' => ['name' => '8.4.27'], 'isEol' => $eol, 'isMaintained' => $maintained]])]);
        expect($service->describe('PHP', $installed, 'php', 'Web', 'https://www.php.net/security/')['status'])->toBe($status);
    }
    Cache::flush();
    Http::swap(new Factory);
    Http::fake(['endoflife.date/*' => Http::failedConnection()]);
    expect($service->describe('PHP', '8.4.27', 'php', 'Web', 'https://www.php.net/security/')['status'])->toBe('Unknown');
});

test('advisory failures and truncated results never receive an all clear', function () {
    Http::fake(['api.osv.dev/*' => Http::response(['results' => []])]);
    expect(app(DependencyAdvisories::class)->report()['complete'])->toBeFalse();
    Cache::flush();
    Http::swap(new Factory);
    Http::fake(['api.osv.dev/*' => Http::failedConnection()]);
    expect(app(DependencyAdvisories::class)->report()['complete'])->toBeFalse();
});

test('admin dashboard renders unavailable providers and uses native POST logout buttons', function () {
    Http::fake(fn () => Http::response([], 503));
    $admin = User::factory()->create(['is_admin' => true]);
    $this->actingAs($admin)->get('/admin')->assertOk()
        ->assertSee('Waktu solat')->assertSee('System versions')
        ->assertSee('Advisory check unavailable')
        ->assertDontSee('onclick="event.preventDefault()', false)
        ->assertSee('type="submit"', false);
    $this->post('/logout')->assertRedirect('/');
    $this->assertGuest();
});

test('advisory reports identify affected packages and distinguish a completed clean lookup', function () {
    Http::fake(['api.osv.dev/*' => function ($request) {
        $rows = array_fill(0, count($request['queries']), []);
        $rows[0] = ['vulns' => [['id' => 'GHSA-example-advisory']]];

        return Http::response(['results' => $rows]);
    }]);
    $report = app(DependencyAdvisories::class)->report();
    expect($report['complete'])->toBeTrue()->and($report['findings'])->toHaveCount(1)
        ->and($report['findings'][0]['id'])->toBe('GHSA-example-advisory');
    Cache::flush();
    Http::swap(new Factory);
    Http::fake(['api.osv.dev/*' => fn ($request) => Http::response(['results' => array_fill(0, count($request['queries']), [])])]);
    $report = app(DependencyAdvisories::class)->report();
    expect($report['complete'])->toBeTrue()->and($report['findings'])->toBeEmpty();
});
