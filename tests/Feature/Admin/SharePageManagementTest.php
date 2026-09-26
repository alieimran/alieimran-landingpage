<?php

use App\Models\AnalyticsEvent;
use App\Models\SharePage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

const ALBUM_URL = 'https://photos.google.com/share/AF1Qip?key=abc';

beforeEach(function () {
    Storage::fake('public');
    Http::fake([
        'photos.google.com/*' => Http::response('<html><head><meta property="og:image" content="https://lh3.googleusercontent.com/pw/abc=w600-h315-p-k"></head></html>'),
    ]);
});

function makeSharePage(array $attributes = []): SharePage
{
    return SharePage::create($attributes + [
        'slug' => 'gambartunang',
        'title' => 'Gambar Tunang',
        'target_url' => ALBUM_URL,
    ]);
}

test('admin can create a share page and it fetches the album cover', function () {
    $this->actingAs(admin())->post(route('admin.share-pages.store'), [
        'slug' => 'GambarTunang',
        'title' => 'Gambar Tunang',
        'target_url' => ALBUM_URL,
        'enabled' => '1',
    ])->assertRedirect();

    $page = SharePage::firstOrFail();

    expect($page->slug)->toBe('gambartunang')
        ->and($page->preview_image_url)->toBe('https://lh3.googleusercontent.com/pw/abc=w1600');
});

test('uploaded images are compressed to webp and capped at five', function () {
    $this->actingAs(admin())->post(route('admin.share-pages.store'), [
        'slug' => 'album',
        'title' => 'Album',
        'target_url' => ALBUM_URL,
        'images' => [UploadedFile::fake()->image('big.jpg', 4000, 3000)],
    ])->assertRedirect();

    $page = SharePage::firstOrFail();
    $path = $page->images[0];

    expect($path)->toEndWith('.webp');
    Storage::disk('public')->assertExists($path);
    expect(getimagesizefromstring(Storage::disk('public')->get($path))[0])->toBe(1920);

    $this->actingAs(admin())->put(route('admin.share-pages.update', $page), [
        'slug' => 'album',
        'title' => 'Album',
        'target_url' => ALBUM_URL,
        'images' => array_map(fn ($i) => UploadedFile::fake()->image("p{$i}.jpg"), range(1, 5)),
    ])->assertSessionHasErrors('images');
});

test('admin can remove an image from a share page', function () {
    Storage::disk('public')->put('share-pages/a.webp', 'x');
    $page = makeSharePage(['images' => ['share-pages/a.webp']]);

    $this->actingAs(admin())->put(route('admin.share-pages.update', $page), [
        'slug' => 'gambartunang',
        'title' => 'Gambar Tunang',
        'target_url' => ALBUM_URL,
        'remove_images' => ['share-pages/a.webp', '../../.env'],
    ])->assertRedirect(route('admin.share-pages.edit', $page));

    expect($page->fresh()->images)->toBe([]);
    Storage::disk('public')->assertMissing('share-pages/a.webp');
});

test('slugs that clash with existing routes or folders are rejected', function (string $slug) {
    $this->actingAs(admin())->post(route('admin.share-pages.store'), [
        'slug' => $slug,
        'title' => 'Clash',
        'target_url' => ALBUM_URL,
    ])->assertSessionHasErrors('slug');
})->with(['admin', 'contact', 'card', 'login', 'tunang', 'build']);

test('slugs must be unique', function () {
    makeSharePage();

    $this->actingAs(admin())->post(route('admin.share-pages.store'), [
        'slug' => 'gambartunang',
        'title' => 'Duplicate',
        'target_url' => ALBUM_URL,
    ])->assertSessionHasErrors('slug');
});

test('the public share page shows the title, cover and button', function () {
    makeSharePage(['preview_image_url' => 'https://lh3.googleusercontent.com/pw/abc=w1600']);

    $this->get('/gambartunang')
        ->assertOk()
        ->assertSee('Gambar Tunang')
        ->assertSee('https://lh3.googleusercontent.com/pw/abc=w1600', false)
        ->assertSee(route('go.share-page', 'gambartunang'), false);
});

test('the button redirects to the target and records a click', function () {
    makeSharePage();

    $this->get(route('go.share-page', 'gambartunang'))->assertRedirect(ALBUM_URL);

    expect(AnalyticsEvent::where('event_type', 'share_page_click')->count())->toBe(1);
});

test('disabled and unknown share pages return 404', function () {
    makeSharePage(['enabled' => false]);

    $this->get('/gambartunang')->assertNotFound();
    $this->get(route('go.share-page', 'gambartunang'))->assertNotFound();
    $this->get('/does-not-exist')->assertNotFound();
});

test('existing pages still work alongside the catch-all slug route', function () {
    $this->get('/')->assertOk();
    $this->get('/contact')->assertOk();
});

test('non-admins cannot manage share pages', function () {
    $this->actingAs(User::factory()->create())->post(route('admin.share-pages.store'), [
        'slug' => 'nope',
        'title' => 'Nope',
        'target_url' => ALBUM_URL,
    ])->assertForbidden();
});

test('deleting a share page removes its images', function () {
    Storage::disk('public')->put('share-pages/a.webp', 'x');
    $page = makeSharePage(['images' => ['share-pages/a.webp']]);

    $this->actingAs(admin())->delete(route('admin.share-pages.destroy', $page))
        ->assertRedirect(route('admin.share-pages.index'));

    expect(SharePage::count())->toBe(0);
    Storage::disk('public')->assertMissing('share-pages/a.webp');
});
