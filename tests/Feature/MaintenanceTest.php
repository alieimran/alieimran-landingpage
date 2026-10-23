<?php

use App\Models\ContactInquiry;
use App\Models\PageView;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function maintenancePayload(): array
{
    return ['maintenance_enabled' => '1', 'maintenance_reason' => 'Updating the website.', 'maintenance_until' => now('Asia/Kuala_Lumpur')->addHours(2)->format('Y-m-d\TH:i')];
}

test('maintenance is off by default', function () {
    $this->get('/')->assertOk();
    expect(SiteSetting::current()->maintenance_enabled)->toBeFalse();
});

test('admin can enable maintenance with Malaysia time and disable it without future time', function () {
    $payload = maintenancePayload();
    $this->actingAs(admin())->put(route('admin.maintenance.update'), $payload)->assertSessionHasNoErrors()->assertRedirect(route('admin.maintenance.edit'));
    $setting = SiteSetting::current();
    expect($setting->maintenance_enabled)->toBeTrue()->and($setting->maintenance_started_at)->not->toBeNull()
        ->and($setting->maintenance_until->timezone('Asia/Kuala_Lumpur')->format('Y-m-d\TH:i'))->toBe($payload['maintenance_until']);
    $this->get(route('admin.maintenance.edit'))->assertOk()->assertSee('Maintenance active');
    $this->get(route('admin.maintenance.preview'))->assertOk()->assertSee('Updating the website.');
    $this->put(route('admin.maintenance.update'), ['maintenance_enabled' => '0', 'maintenance_until' => '2020-01-01T01:00'])->assertSessionHasNoErrors();
    expect(SiteSetting::current()->maintenance_enabled)->toBeFalse()->and(SiteSetting::current()->maintenance_started_at)->toBeNull();
});

test('maintenance blocks public reads and writes but preserves login and admin access', function () {
    SiteSetting::current()->update(['maintenance_enabled' => true, 'maintenance_reason' => '<script>unsafe</script>', 'maintenance_until' => now()->addHour()]);
    $this->withHeaders(['User-Agent' => 'Mozilla/5.0 Chrome/120.0'])->get('/')->assertStatus(503)->assertHeader('Content-Security-Policy')->assertHeader('Retry-After')->assertHeader('X-Robots-Tag', 'noindex, nofollow')
        ->assertSee('&lt;script&gt;unsafe&lt;/script&gt;', false)->assertDontSee('<script>unsafe</script>', false);
    $this->get('/contact')->assertStatus(503);
    $this->post('/contact', [])->assertStatus(503);
    expect(ContactInquiry::count())->toBe(0)->and(PageView::count())->toBe(0);
    $this->get('/login')->assertOk();
    $this->get('/forgot-password')->assertOk();
    $this->get(route('admin.maintenance.edit'))->assertRedirect('/login');
    $this->actingAs(admin())->get('/')->assertOk();
    $this->get(route('admin.maintenance.edit'))->assertOk();
    $this->post('/logout')->assertRedirect('/');
    $this->get('/')->assertStatus(503);
});

test('non admins cannot edit maintenance or bypass the public page', function () {
    SiteSetting::current()->update(['maintenance_enabled' => true]);
    $this->actingAs(User::factory()->create())->put(route('admin.maintenance.update'), maintenancePayload())->assertForbidden();
    $this->get('/')->assertStatus(503);
    $this->get(route('admin.maintenance.preview'))->assertForbidden();
});

test('enabling maintenance requires a reason and future return time', function () {
    $this->actingAs(admin())->put(route('admin.maintenance.update'), ['maintenance_enabled' => '1'])
        ->assertSessionHasErrors(['maintenance_reason', 'maintenance_until']);
    $this->put(route('admin.maintenance.update'), array_merge(maintenancePayload(), ['maintenance_until' => '2020-01-01T01:00']))->assertSessionHasErrors('maintenance_until');
    expect(SiteSetting::current()->maintenance_enabled)->toBeFalse();
});

test('an expired estimate does not reopen the site or display a misleading return time', function () {
    SiteSetting::current()->update(['maintenance_enabled' => true, 'maintenance_until' => now()->subHour()]);
    $this->get('/')->assertStatus(503)->assertSee('Work is still underway')->assertHeader('Retry-After', '300');
});

test('duration suggestions calculate return time from saving without requiring a custom date', function () {
    $this->freezeTime();
    $this->actingAs(admin())->put(route('admin.maintenance.update'), [
        'maintenance_enabled' => '1', 'maintenance_reason' => 'Security updates', 'maintenance_duration' => '30',
    ])->assertSessionHasNoErrors();
    expect(SiteSetting::current()->maintenance_until->timestamp)->toBe(now()->addMinutes(30)->timestamp);
    $this->put(route('admin.maintenance.update'), ['maintenance_enabled' => '1', 'maintenance_reason' => 'Updates', 'maintenance_duration' => '999999'])
        ->assertSessionHasErrors('maintenance_duration');
});

test('admins can customize page copy and hide optional sections with escaped content', function () {
    $content = [
        'label' => 'Scheduled improvements', 'heading' => '<script>custom heading</script>',
        'button_text' => 'Try the website', 'footer' => 'See you soon', 'delayed_message' => 'Taking a little longer.',
        'show_logo' => '0', 'show_start' => '0', 'show_return' => '0', 'show_theme_toggle' => '0',
    ];
    $this->actingAs(admin())->put(route('admin.maintenance.update'), array_merge(maintenancePayload(), ['maintenance_content' => $content]))->assertSessionHasNoErrors();
    $this->get(route('admin.maintenance.edit'))->assertOk()->assertSee('How long do you expect to need?')->assertSee('Customize the public page');
    $this->post('/logout');
    $this->get('/')->assertStatus(503)->assertSee('Scheduled improvements')->assertSee('&lt;script&gt;custom heading&lt;/script&gt;', false)
        ->assertSee('Try the website')->assertSee('See you soon')->assertDontSee('<script>custom heading</script>', false)
        ->assertDontSee('Maintenance started')->assertDontSee('Expected back online')->assertDontSee('Toggle theme');
});

test('countdown and animation can be switched off independently and timing visibility stays independent', function () {
    SiteSetting::current()->update([
        'maintenance_enabled' => true, 'maintenance_started_at' => now(), 'maintenance_until' => now()->addHour(),
        'maintenance_content' => ['show_spinner' => false, 'show_countdown' => false, 'show_start' => false, 'show_return' => true],
    ]);
    $this->get('/')->assertStatus(503)->assertSee('Expected back online')->assertDontSee('Maintenance started')
        ->assertDontSee('data-maintenance-countdown', false)->assertDontSee('animate-spin', false);
    SiteSetting::current()->update(['maintenance_content' => ['show_start' => true, 'show_return' => false]]);
    $this->get('/')->assertStatus(503)->assertSee('Maintenance started')->assertDontSee('Expected back online');
});
