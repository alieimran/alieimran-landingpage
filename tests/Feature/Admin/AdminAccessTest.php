<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guests are redirected to login', function () {
    $this->get('/admin')->assertRedirect('/login');
});

test('non-admin authenticated users are forbidden', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/admin')->assertForbidden();
});

test('admins can access the dashboard', function () {
    $admin = User::factory()->create();
    $admin->forceFill(['is_admin' => true])->save();

    $this->actingAs($admin)->get('/admin')->assertOk();
});

test('the generic dashboard route redirects straight to the admin dashboard', function () {
    // Kept as a named route only because Breeze's stock Auth
    // controllers redirect to route('dashboard') internally — there's
    // no separate non-admin experience in this single-Super-Admin
    // system, so visiting it directly should land you on the real
    // dashboard rather than a placeholder "you're logged in" page.
    $admin = User::factory()->create();
    $admin->forceFill(['is_admin' => true])->save();

    $this->actingAs($admin)->get('/dashboard')->assertRedirect(route('admin.dashboard'));
});
