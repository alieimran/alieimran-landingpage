<?php

use App\Models\AnalyticsEvent;
use App\Models\Link;
use App\Models\PageView;
use App\Models\SocialLink;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('visiting the homepage records a page view', function () {
    $this->withHeaders(['User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0'])
        ->get('/');

    expect(PageView::count())->toBe(1);
    expect(PageView::first())->path->toBe('/')
        ->browser->toBe('Chrome')
        ->os->toBe('Windows')
        ->device_type->toBe('desktop');
});

test('bot traffic is not recorded', function () {
    $this->withHeaders(['User-Agent' => 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)'])
        ->get('/');

    expect(PageView::count())->toBe(0);
});

test('no raw ip address is ever stored', function () {
    $this->withHeaders(['User-Agent' => 'Mozilla/5.0 Chrome/120.0'])->get('/');

    $view = PageView::first();

    expect($view->getAttributes())->not->toHaveKey('ip')
        ->and($view->visitor_hash)->not->toBe(request()->ip());
});

test('visiting a link redirect records a click and redirects to the destination', function () {
    $link = Link::create(['title' => 'GitHub', 'url' => 'https://github.com/example']);

    $response = $this->get(route('go.link', $link));

    $response->assertRedirect('https://github.com/example');
    $this->assertDatabaseHas('analytics_events', [
        'event_type' => 'link_click',
        'label' => 'GitHub',
        'url' => 'https://github.com/example',
    ]);
});

test('visiting a social link redirect records a click and redirects to the destination', function () {
    $social = SocialLink::create(['platform' => 'LinkedIn', 'url' => 'https://linkedin.com/in/example']);

    $response = $this->get(route('go.social', $social));

    $response->assertRedirect('https://linkedin.com/in/example');
    $this->assertDatabaseHas('analytics_events', [
        'event_type' => 'social_click',
        'label' => 'LinkedIn',
    ]);
});

test('a disabled link redirect 404s instead of redirecting', function () {
    $link = Link::create(['title' => 'Disabled', 'url' => 'https://example.com', 'enabled' => false]);

    $this->get(route('go.link', $link))->assertNotFound();
    expect(AnalyticsEvent::count())->toBe(0);
});

test('an expired link redirect 404s instead of redirecting', function () {
    $link = Link::create(['title' => 'Expired', 'url' => 'https://example.com', 'end_date' => now()->subDay()]);

    $this->get(route('go.link', $link))->assertNotFound();
});

test('a disabled social link redirect 404s instead of redirecting', function () {
    $social = SocialLink::create(['platform' => 'Disabled', 'url' => 'https://example.com', 'enabled' => false]);

    $this->get(route('go.social', $social))->assertNotFound();
    expect(AnalyticsEvent::count())->toBe(0);
});

test('admin can view the analytics dashboard', function () {
    $this->actingAs(admin())->get(route('admin.analytics'))->assertOk();
});

test('non-admins cannot view the analytics dashboard', function () {
    $this->actingAs(User::factory()->create())->get(route('admin.analytics'))->assertForbidden();
});

test('analytics prune command deletes old records', function () {
    PageView::create(['path' => '/', 'created_at' => now()->subDays(100)]);
    PageView::create(['path' => '/', 'created_at' => now()->subDays(10)]);
    AnalyticsEvent::create(['event_type' => 'link_click', 'label' => 'Old', 'created_at' => now()->subDays(100)]);

    $this->artisan('analytics:prune', ['--days' => 90])->assertSuccessful();

    expect(PageView::count())->toBe(1);
    expect(AnalyticsEvent::count())->toBe(0);
});
