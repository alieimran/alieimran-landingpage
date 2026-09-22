<?php

use App\Models\SocialLink;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('admin can create a social link', function () {
    $this->actingAs(admin())->post(route('admin.social-links.store'), [
        'platform' => 'GitHub',
        'url' => 'https://github.com/alieimran',
        'enabled' => '1',
    ])->assertRedirect(route('admin.social-links.index'));

    expect(SocialLink::where('platform', 'GitHub')->exists())->toBeTrue();
});

test('social link creation requires a valid url', function () {
    $this->actingAs(admin())->post(route('admin.social-links.store'), [
        'platform' => 'GitHub',
        'url' => 'not-a-url',
    ])->assertSessionHasErrors('url');
});

test('admin can update a social link', function () {
    $social = SocialLink::create(['platform' => 'Old', 'url' => 'https://example.com']);

    $this->actingAs(admin())->put(route('admin.social-links.update', $social), [
        'platform' => 'New',
        'url' => 'https://example.com',
    ])->assertRedirect(route('admin.social-links.index'));

    expect($social->fresh()->platform)->toBe('New');
});

test('unchecking enabled and featured on update actually disables them', function () {
    $social = SocialLink::create([
        'platform' => 'Toggle Me', 'url' => 'https://example.com',
        'enabled' => true, 'featured' => true,
    ]);

    $this->actingAs(admin())->put(route('admin.social-links.update', $social), [
        'platform' => 'Toggle Me',
        'url' => 'https://example.com',
        // enabled, featured intentionally omitted
    ])->assertRedirect(route('admin.social-links.index'));

    $social->refresh();
    expect($social->enabled)->toBeFalse()
        ->and($social->featured)->toBeFalse();
});

test('admin can delete a social link', function () {
    $social = SocialLink::create(['platform' => 'Delete Me', 'url' => 'https://example.com']);

    $this->actingAs(admin())->delete(route('admin.social-links.destroy', $social))
        ->assertRedirect(route('admin.social-links.index'));

    expect(SocialLink::find($social->id))->toBeNull();
});

test('non-admins cannot store social links', function () {
    $this->actingAs(User::factory()->create())->post(route('admin.social-links.store'), [
        'platform' => 'Nope',
        'url' => 'https://example.com',
    ])->assertForbidden();
});
