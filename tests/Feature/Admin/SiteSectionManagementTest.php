<?php

use App\Models\SiteSection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('admin can view the sections list', function () {
    SiteSection::create(['key' => 'hero', 'title' => 'Profile', 'sort_order' => 1]);

    $this->actingAs(admin())->get(route('admin.sections.index'))->assertOk()->assertSee('Profile');
});

test('admin can update a section', function () {
    $section = SiteSection::create(['key' => 'hero', 'title' => 'Profile', 'sort_order' => 1]);

    $this->actingAs(admin())->put(route('admin.sections.update', $section), [
        'title' => 'Intro',
        'sort_order' => 2,
        'enabled' => '1',
        'homepage_visible' => '0',
    ])->assertRedirect(route('admin.sections.index'));

    $section->refresh();
    expect($section->title)->toBe('Intro')
        ->and($section->homepage_visible)->toBeFalse();
});

test('disabling a section hides it from the homepage', function () {
    $section = SiteSection::create(['key' => 'hero', 'title' => 'Profile', 'sort_order' => 1]);

    $this->actingAs(admin())->put(route('admin.sections.update', $section), [
        'title' => 'Profile',
        'sort_order' => 1,
        'homepage_visible' => '0',
    ]);

    $sections = SiteSection::onHomepage()->pluck('key');
    expect($sections)->not->toContain('hero');
});

test('unchecking visibility boxes on update actually hides the section', function () {
    // Regression test: HTML browsers omit unchecked checkboxes from
    // the submitted request entirely rather than sending false — an
    // explicit '0' (as the test above sends) would pass even with
    // that bug present, since PHP casts the string "0" to false
    // anyway and masks the real failure mode.
    $section = SiteSection::create([
        'key' => 'hero', 'title' => 'Profile', 'sort_order' => 1,
        'enabled' => true, 'nav_visible' => true, 'homepage_visible' => true,
    ]);

    $this->actingAs(admin())->put(route('admin.sections.update', $section), [
        'title' => 'Profile',
        'sort_order' => 1,
        // enabled, nav_visible, homepage_visible intentionally omitted
    ])->assertRedirect(route('admin.sections.index'));

    $section->refresh();
    expect($section->enabled)->toBeFalse()
        ->and($section->nav_visible)->toBeFalse()
        ->and($section->homepage_visible)->toBeFalse();
});

test('non-admins cannot update sections', function () {
    $section = SiteSection::create(['key' => 'hero', 'title' => 'Profile', 'sort_order' => 1]);

    $this->actingAs(User::factory()->create())->put(route('admin.sections.update', $section), [
        'title' => 'Nope',
    ])->assertForbidden();
});
