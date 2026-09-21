<?php

use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('admin can update site settings with a mailto secondary cta', function () {
    $this->actingAs(admin())->put(route('admin.site-settings.update'), [
        'full_name' => 'Alie Imran',
        'display_name' => 'Alie Imran',
        'primary_cta_url' => 'https://www.alieimran.com/portfolio',
        'secondary_cta_url' => 'mailto:alieimran@outlook.com',
    ])->assertRedirect(route('admin.site-settings.edit'));

    $setting = SiteSetting::current();
    expect($setting->secondary_cta_url)->toBe('mailto:alieimran@outlook.com');
});

test('site settings reject a garbage cta url', function () {
    $this->actingAs(admin())->put(route('admin.site-settings.update'), [
        'full_name' => 'Alie Imran',
        'display_name' => 'Alie Imran',
        'primary_cta_url' => 'not a url',
    ])->assertSessionHasErrors('primary_cta_url');
});

test('non-admins cannot update site settings', function () {
    $this->actingAs(User::factory()->create())->put(route('admin.site-settings.update'), [
        'full_name' => 'Nope',
        'display_name' => 'Nope',
    ])->assertForbidden();
});
