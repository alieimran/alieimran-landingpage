<?php

use App\Models\PageView;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

test('public visitor IP is stored but untrusted forwarded IPs are ignored', function () {
    $this->withServerVariables(['REMOTE_ADDR' => '8.8.8.8'])
        ->withHeaders(['User-Agent' => 'Mozilla/5.0 Chrome/120.0', 'X-Forwarded-For' => '1.1.1.1', 'CF-Connecting-IP' => '1.1.1.1'])->get('/');
    expect(PageView::first()->ip_address)->toBe('8.8.8.8');
});

test('visitor location enrichment stores approximate location without lookup on public request', function () {
    PageView::create(['path' => '/', 'ip_address' => '8.8.8.8']);
    Http::fake(['ipwho.is/*' => Http::response(['success' => true, 'ip' => '8.8.8.8', 'city' => 'Example City', 'region' => 'Example Region', 'country' => 'Example Country'])]);
    $this->artisan('analytics:locate')->assertSuccessful();
    expect(PageView::first()->location)->toBe('Example City, Example Region, Example Country');
    $this->artisan('analytics:locate')->assertSuccessful();
    Http::assertSentCount(1);
});

test('geolocation failure leaves a useful unavailable state', function () {
    PageView::create(['path' => '/', 'ip_address' => '8.8.8.8']);
    Http::fake(['ipwho.is/*' => Http::failedConnection()]);
    $this->artisan('analytics:locate')->assertSuccessful();
    expect(PageView::first()->location)->toBeNull()->and(PageView::first()->geo_checked_at)->not->toBeNull();
});

test('visitor details expire after 30 days without deleting aggregates', function () {
    PageView::create(['path' => '/', 'ip_address' => '8.8.8.8', 'location' => 'Old City', 'created_at' => now()->subDays(31)]);
    PageView::create(['path' => '/', 'ip_address' => '1.1.1.1', 'location' => 'Recent City', 'created_at' => now()->subDays(2)]);
    $this->artisan('analytics:prune-visitor-details')->assertSuccessful();
    expect(PageView::count())->toBe(2)->and(PageView::oldest('id')->first()->ip_address)->toBeNull()
        ->and(PageView::oldest('id')->first()->location)->toBeNull()
        ->and(PageView::latest('id')->first()->ip_address)->toBe('1.1.1.1');
});

test('admin visitor table excludes visits beyond 30 days and escapes provider content', function () {
    PageView::create(['path' => '/', 'ip_address' => '8.8.8.8', 'location' => '<script>alert(1)</script>']);
    PageView::create(['path' => '/old-visit', 'ip_address' => '1.1.1.1', 'created_at' => now()->subDays(31)]);
    $this->actingAs(admin())->get(route('admin.analytics'))->assertOk()->assertSee('8.8.8.8')
        ->assertDontSee('1.1.1.1')->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
        ->assertDontSee('<script>alert(1)</script>', false);
    $this->post('/logout');
    $this->get(route('admin.analytics'))->assertRedirect('/login');
});
