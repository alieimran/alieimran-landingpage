<?php

use App\Models\ThemeSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

test('admin can update the primary color', function () {
    $this->actingAs(admin())->put(route('admin.theme.update'), [
        'primary_color' => '#ff0000',
    ])->assertRedirect(route('admin.theme.edit'));

    expect(ThemeSetting::current()->primary_color)->toBe('#ff0000');
});

test('invalid color values are rejected', function () {
    $this->actingAs(admin())->put(route('admin.theme.update'), [
        'primary_color' => 'not-a-color',
    ])->assertSessionHasErrors('primary_color');
});

test('admin can upload a logo', function () {
    Storage::fake('public');

    $this->actingAs(admin())->put(route('admin.theme.update'), [
        'primary_color' => '#10b981',
        'logo' => UploadedFile::fake()->image('logo.png', 200, 200),
    ])->assertRedirect(route('admin.theme.edit'));

    expect(ThemeSetting::current()->logo_path)->not->toBeNull();
});

test('svg logo uploads are rejected', function () {
    $this->actingAs(admin())->put(route('admin.theme.update'), [
        'primary_color' => '#10b981',
        'logo' => UploadedFile::fake()->create('logo.svg', 10, 'image/svg+xml'),
    ])->assertSessionHasErrors('logo');
});

test('non-admins cannot update the theme', function () {
    $this->actingAs(User::factory()->create())->put(route('admin.theme.update'), [
        'primary_color' => '#ff0000',
    ])->assertForbidden();
});
