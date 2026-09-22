<?php

use App\Models\Link;
use App\Models\LinkCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('admin can create a link', function () {
    $this->actingAs(admin())->post(route('admin.links.store'), [
        'title' => 'GitHub',
        'url' => 'https://github.com/alieimran',
        'enabled' => '1',
    ])->assertRedirect(route('admin.links.index'));

    expect(Link::where('title', 'GitHub')->exists())->toBeTrue();
});

test('link creation requires a valid url', function () {
    $this->actingAs(admin())->post(route('admin.links.store'), [
        'title' => 'Bad Link',
        'url' => 'not-a-url',
    ])->assertSessionHasErrors('url');
});

test('admin can update a link', function () {
    $link = Link::create(['title' => 'Old', 'url' => 'https://example.com']);

    $this->actingAs(admin())->put(route('admin.links.update', $link), [
        'title' => 'New Title',
        'url' => 'https://example.com',
    ])->assertRedirect(route('admin.links.index'));

    expect($link->fresh()->title)->toBe('New Title');
});

test('unchecking enabled and featured on update actually disables them', function () {
    // Regression test: HTML browsers omit unchecked checkboxes from
    // the submitted request entirely rather than sending false, so
    // this must simulate omission, not an explicit '0' — sending '0'
    // would pass even with the bug present, since PHP casts the
    // string "0" to false anyway and masks the real failure mode.
    $link = Link::create([
        'title' => 'Toggle Me', 'url' => 'https://example.com',
        'enabled' => true, 'featured' => true, 'open_in_new_tab' => true,
    ]);

    $this->actingAs(admin())->put(route('admin.links.update', $link), [
        'title' => 'Toggle Me',
        'url' => 'https://example.com',
        // enabled, featured, open_in_new_tab intentionally omitted
    ])->assertRedirect(route('admin.links.index'));

    $link->refresh();
    expect($link->enabled)->toBeFalse()
        ->and($link->featured)->toBeFalse()
        ->and($link->open_in_new_tab)->toBeFalse();
});

test('admin can delete a link', function () {
    $link = Link::create(['title' => 'Delete Me', 'url' => 'https://example.com']);

    $this->actingAs(admin())->delete(route('admin.links.destroy', $link))
        ->assertRedirect(route('admin.links.index'));

    expect(Link::find($link->id))->toBeNull();
});

test('non-admins cannot store links', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('admin.links.store'), [
        'title' => 'Nope',
        'url' => 'https://example.com',
    ])->assertForbidden();
});

test('links respect start and end date visibility window', function () {
    $expired = Link::create([
        'title' => 'Expired', 'url' => 'https://example.com',
        'end_date' => now()->subDay(),
    ]);
    $future = Link::create([
        'title' => 'Future', 'url' => 'https://example.com',
        'start_date' => now()->addDay(),
    ]);
    $active = Link::create(['title' => 'Active', 'url' => 'https://example.com']);

    $visible = Link::visible()->pluck('title');

    expect($visible)->toContain('Active')
        ->and($visible)->not->toContain('Expired')
        ->and($visible)->not->toContain('Future');
});

test('link category slugs are unique', function () {
    LinkCategory::create(['name' => 'Projects', 'slug' => 'projects']);

    $this->actingAs(admin())->post(route('admin.link-categories.store'), [
        'name' => 'Projects',
    ])->assertRedirect(route('admin.link-categories.index'));

    expect(LinkCategory::where('name', 'Projects')->count())->toBe(2)
        ->and(LinkCategory::pluck('slug')->all())->toContain('projects-1');
});

test('admin can rename a link category from the index page', function () {
    // Regression test: the rename form was previously missing from
    // the UI entirely — the update() route/controller worked, but
    // nothing on the page could reach it, so admins had no way to fix
    // a typo without deleting and recreating the category (which
    // orphans any links using it).
    $category = LinkCategory::create(['name' => 'Projcets', 'slug' => 'projcets', 'sort_order' => 0]);

    $this->actingAs(admin())->get(route('admin.link-categories.index'))
        ->assertSee('id="cat-update-'.$category->id.'"', false);

    $this->actingAs(admin())->put(route('admin.link-categories.update', $category), [
        'name' => 'Projects',
        'sort_order' => 1,
    ])->assertRedirect(route('admin.link-categories.index'));

    $category->refresh();
    expect($category->name)->toBe('Projects')
        ->and($category->sort_order)->toBe(1)
        ->and($category->slug)->toBe('projects');
});
