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
