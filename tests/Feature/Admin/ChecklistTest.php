<?php

use App\Models\Checklist;
use App\Models\ChecklistItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function checklistAdmin(): User
{
    return User::factory()->create(['is_admin' => true]);
}

test('the checklist page is admin only', function () {
    $this->get('/admin/checklists')->assertRedirect('/login');
    $this->actingAs(User::factory()->create(['is_admin' => false]))->get('/admin/checklists')->assertForbidden();
    $this->actingAs(checklistAdmin())->get('/admin/checklists')->assertOk()->assertSee('No checklists yet');
});

test('every write action is blocked for non-admins', function () {
    $user = User::factory()->create(['is_admin' => false]);
    $list = Checklist::create(['title' => 'Work']);
    $item = $list->items()->create(['title' => 'Task']);

    $this->actingAs($user);
    $this->post('/admin/checklists', ['title' => 'x'])->assertForbidden();
    $this->patch("/admin/checklists/{$list->id}", ['title' => 'x'])->assertForbidden();
    $this->delete("/admin/checklists/{$list->id}")->assertForbidden();
    $this->post("/admin/checklists/{$list->id}/reset")->assertForbidden();
    $this->post("/admin/checklists/{$list->id}/items", ['title' => 'x'])->assertForbidden();
    $this->patch("/admin/checklist-items/{$item->id}", ['title' => 'x'])->assertForbidden();
    $this->patch("/admin/checklist-items/{$item->id}/toggle")->assertForbidden();
    $this->delete("/admin/checklist-items/{$item->id}")->assertForbidden();
    expect(Checklist::count())->toBe(1)->and($item->fresh()->is_done)->toBeFalse();
});

test('admin can create a blank checklist', function () {
    $this->actingAs(checklistAdmin())->post('/admin/checklists', ['title' => 'Personal', 'description' => 'Misc'])->assertRedirect();
    $list = Checklist::first();
    expect($list->title)->toBe('Personal')->and($list->description)->toBe('Misc')->and($list->items)->toBeEmpty();
});

test('work and marriage preparation templates create ready-made items', function () {
    $admin = checklistAdmin();
    $this->actingAs($admin)->post('/admin/checklists', ['template' => 'work']);
    $this->actingAs($admin)->post('/admin/checklists', ['template' => 'marriage', 'title' => 'Our wedding']);

    $work = Checklist::where('title', 'Work checklist')->first();
    $marriage = Checklist::where('title', 'Our wedding')->first();
    expect($work->items)->toHaveCount(count(config('checklists.templates.work.items')))
        ->and($marriage->items->count())->toBeGreaterThan(8)
        ->and($marriage->items->pluck('title'))->toContain('Attend the pre-marriage course')
        ->and($marriage->items->firstWhere('title', 'Attend the pre-marriage course')->note)->toBe('Book early; slots can fill up.')
        ->and($marriage->items->pluck('sort_order')->all())->toBe(range(1, $marriage->items->count()));
});

test('a title or a template is required, and unknown templates are rejected', function () {
    $admin = checklistAdmin();
    $this->actingAs($admin)->post('/admin/checklists', [])->assertSessionHasErrors('title');
    $this->actingAs($admin)->post('/admin/checklists', ['template' => 'nope'])->assertSessionHasErrors('template');
    expect(Checklist::count())->toBe(0);
});

test('items can carry a personalised link and note and render as a safe external link', function () {
    $admin = checklistAdmin();
    $list = Checklist::create(['title' => 'Work']);

    $this->actingAs($admin)->post("/admin/checklists/{$list->id}/items", ['title' => 'Book the hall', 'url' => 'https://example.com/hall?id=7', 'note' => 'Call first'])->assertRedirect();
    $item = $list->items()->first();
    expect($item->url)->toBe('https://example.com/hall?id=7')->and($item->note)->toBe('Call first')->and($item->is_done)->toBeFalse();

    $this->actingAs($admin)->get('/admin/checklists')->assertOk()
        ->assertSee('href="https://example.com/hall?id=7"', false)
        ->assertSee('rel="noopener noreferrer"', false)->assertSee('target="_blank"', false)->assertSee('Call first');
});

test('only http and https links are accepted', function () {
    $admin = checklistAdmin();
    $list = Checklist::create(['title' => 'Work']);
    foreach (['javascript:alert(1)', 'data:text/html,hi', 'ftp://example.com/file', 'not a url', 'file:///etc/passwd'] as $bad) {
        $this->actingAs($admin)->post("/admin/checklists/{$list->id}/items", ['title' => 'x', 'url' => $bad])->assertSessionHasErrors('url');
    }
    expect($list->items()->count())->toBe(0);
    $this->actingAs($admin)->post("/admin/checklists/{$list->id}/items", ['title' => 'No link'])->assertSessionHasNoErrors();
    expect($list->items()->first()->url)->toBeNull();
});

test('user supplied text is escaped', function () {
    $admin = checklistAdmin();
    $list = Checklist::create(['title' => '<script>alert(1)</script>']);
    $list->items()->create(['title' => '<img src=x onerror=alert(2)>', 'note' => '<b>bold</b>']);

    $html = $this->actingAs($admin)->get('/admin/checklists')->getContent();
    expect($html)->not->toContain('<script>alert(1)</script>')->not->toContain('<img src=x onerror=alert(2)>')->not->toContain('<b>bold</b>')
        ->and($html)->toContain('&lt;script&gt;alert(1)&lt;/script&gt;');
});

test('toggling an item records the completion time and updates progress', function () {
    $admin = checklistAdmin();
    $list = Checklist::create(['title' => 'Work']);
    $a = $list->items()->create(['title' => 'A', 'sort_order' => 1]);
    $list->items()->create(['title' => 'B', 'sort_order' => 2]);

    $this->actingAs($admin)->patch("/admin/checklist-items/{$a->id}/toggle")->assertRedirect();
    expect($a->fresh()->is_done)->toBeTrue()->and($a->fresh()->done_at)->not->toBeNull()
        ->and($list->fresh()->load('items')->progress())->toBe(['done' => 1, 'total' => 2, 'percent' => 50]);
    $this->actingAs($admin)->get('/admin/checklists')->assertSee('1/2')->assertSee('aria-valuenow="50"', false);

    $this->actingAs($admin)->patch("/admin/checklist-items/{$a->id}/toggle");
    expect($a->fresh()->is_done)->toBeFalse()->and($a->fresh()->done_at)->toBeNull();
});

test('items can be edited and deleted', function () {
    $admin = checklistAdmin();
    $list = Checklist::create(['title' => 'Work']);
    $item = $list->items()->create(['title' => 'Old', 'url' => 'https://old.example']);

    $this->actingAs($admin)->patch("/admin/checklist-items/{$item->id}", ['title' => 'New', 'url' => '', 'note' => 'n'])->assertRedirect();
    expect($item->fresh()->title)->toBe('New')->and($item->fresh()->url)->toBeNull()->and($item->fresh()->note)->toBe('n');
    $this->actingAs($admin)->patch("/admin/checklist-items/{$item->id}", ['title' => ''])->assertSessionHasErrors('title');

    $this->actingAs($admin)->delete("/admin/checklist-items/{$item->id}")->assertRedirect();
    expect(ChecklistItem::count())->toBe(0);
});

test('a list can be renamed, reset for reuse and deleted with its items', function () {
    $admin = checklistAdmin();
    $list = Checklist::create(['title' => 'Weekly']);
    $list->items()->createMany([['title' => 'A', 'is_done' => true, 'done_at' => now()], ['title' => 'B', 'is_done' => true, 'done_at' => now()]]);

    $this->actingAs($admin)->patch("/admin/checklists/{$list->id}", ['title' => 'Weekly review', 'description' => 'Fridays'])->assertRedirect();
    expect($list->fresh()->title)->toBe('Weekly review');

    $this->actingAs($admin)->post("/admin/checklists/{$list->id}/reset")->assertRedirect();
    expect($list->items()->where('is_done', true)->count())->toBe(0)->and($list->items()->whereNotNull('done_at')->count())->toBe(0);

    $this->actingAs($admin)->delete("/admin/checklists/{$list->id}")->assertRedirect('/admin/checklists');
    expect(Checklist::count())->toBe(0)->and(ChecklistItem::count())->toBe(0);
});

test('new items are appended in order and lists show items in that order', function () {
    $admin = checklistAdmin();
    $list = Checklist::create(['title' => 'Work']);
    foreach (['First', 'Second', 'Third'] as $title) {
        $this->actingAs($admin)->post("/admin/checklists/{$list->id}/items", ['title' => $title]);
    }
    expect($list->fresh()->items->pluck('title')->all())->toBe(['First', 'Second', 'Third']);
});

test('the navigation and dashboard link to the checklists with an open-item count', function () {
    $admin = checklistAdmin();
    $list = Checklist::create(['title' => 'Work']);
    $list->items()->createMany([['title' => 'A'], ['title' => 'B', 'is_done' => true]]);

    $this->actingAs($admin)->get('/admin')->assertOk()->assertSee('/admin/checklists', false)->assertSee('1 open item');
});

test('toggling in place returns JSON with fresh progress, so the page does not need to reload', function () {
    $admin = checklistAdmin();
    $list = Checklist::create(['title' => 'Work']);
    $a = $list->items()->create(['title' => 'A', 'sort_order' => 1]);
    $list->items()->create(['title' => 'B', 'sort_order' => 2]);
    $other = Checklist::create(['title' => 'Other']);
    $other->items()->create(['title' => 'C']);

    $this->actingAs($admin)->patchJson("/admin/checklist-items/{$a->id}/toggle")
        ->assertOk()
        ->assertExactJson(['is_done' => true, 'progress' => ['done' => 1, 'total' => 2, 'percent' => 50], 'open_items' => 2]);
    expect($a->fresh()->is_done)->toBeTrue();

    $this->actingAs($admin)->patchJson("/admin/checklist-items/{$a->id}/toggle")->assertOk()->assertJsonPath('is_done', false)->assertJsonPath('progress.done', 0);
    // A plain form post still redirects (no-JavaScript fallback).
    $this->actingAs($admin)->patch("/admin/checklist-items/{$a->id}/toggle")->assertRedirect();
});

test('the toggle JSON endpoint is admin only', function () {
    $item = Checklist::create(['title' => 'Work'])->items()->create(['title' => 'A']);
    $this->patchJson("/admin/checklist-items/{$item->id}/toggle")->assertUnauthorized();
    $this->actingAs(User::factory()->create(['is_admin' => false]))->patchJson("/admin/checklist-items/{$item->id}/toggle")->assertForbidden();
    expect($item->fresh()->is_done)->toBeFalse();
});

test('checklist rows carry the hooks the in-page toggle script needs', function () {
    $list = Checklist::create(['title' => 'Work']);
    $list->items()->create(['title' => 'A']);
    $this->actingAs(checklistAdmin())->get('/admin/checklists')->assertOk()
        ->assertSee('data-checklist-toggle', false)->assertSee('data-progress-text', false)->assertSee('data-progress-fill', false)->assertSee('data-item-title', false);
});

// ---- Sharing: personalised link / slug ----

function sharedList(array $attrs = []): Checklist
{
    $list = Checklist::create(['title' => 'Our wedding', 'slug' => 'our-wedding'] + $attrs);
    $list->items()->createMany([
        ['title' => 'Book venue', 'url' => 'https://example.com/venue', 'note' => 'Call first', 'sort_order' => 1],
        ['title' => 'Send invitations', 'sort_order' => 2],
    ]);

    return $list;
}

test('every checklist always has a unique slug, generated from the title with a random suffix', function () {
    $a = Checklist::create(['title' => 'Marriage Prep!']);
    $b = Checklist::create(['title' => 'Marriage Prep!']);
    $c = Checklist::create(['title' => '!!!']);

    expect($a->slug)->toMatch('/^marriage-prep-[a-z0-9]{5}$/')->and($b->slug)->not->toBe($a->slug)
        ->and($c->slug)->toMatch('/^list-[a-z0-9]{5}$/')
        ->and($a->is_public)->toBeFalse()->and($a->allow_ticking)->toBeFalse();
});

test('a custom link can be chosen when creating a checklist and is normalised', function () {
    $admin = checklistAdmin();
    $this->actingAs($admin)->post('/admin/checklists', ['title' => 'Wedding', 'slug' => 'Our Wedding 2027!'])->assertRedirect();
    expect(Checklist::first()->slug)->toBe('our-wedding-2027');

    $this->actingAs($admin)->post('/admin/checklists', ['title' => 'Other', 'slug' => 'our-wedding-2027'])->assertSessionHasErrors('slug');
    $this->actingAs($admin)->post('/admin/checklists', ['title' => 'Other', 'slug' => 'ab'])->assertSessionHasErrors('slug');
    expect(Checklist::count())->toBe(1);
});

test('sharing needs a slug, accepts a personalised one, and rejects duplicates', function () {
    $admin = checklistAdmin();
    $list = Checklist::create(['title' => 'Wedding']);
    $other = Checklist::create(['title' => 'Work', 'slug' => 'taken-slug']);

    $this->actingAs($admin)->patch("/admin/checklists/{$list->id}/sharing", ['slug' => '', 'is_public' => 1])->assertSessionHasErrors('slug');
    $this->actingAs($admin)->patch("/admin/checklists/{$list->id}/sharing", ['slug' => 'taken-slug', 'is_public' => 1])->assertSessionHasErrors('slug');
    $this->actingAs($admin)->patch("/admin/checklists/{$list->id}/sharing", ['slug' => 'x', 'is_public' => 1])->assertSessionHasErrors('slug');

    $this->actingAs($admin)->patch("/admin/checklists/{$list->id}/sharing", ['slug' => 'Our Wedding!', 'is_public' => 1, 'allow_ticking' => 1])->assertSessionHasNoErrors();
    $list->refresh();
    expect($list->slug)->toBe('our-wedding')->and($list->is_public)->toBeTrue()->and($list->allow_ticking)->toBeTrue()->and($list->publicUrl())->toEndWith('/checklist/our-wedding');

    // Keeping its own slug is fine; unchecked boxes mean off.
    $this->actingAs($admin)->patch("/admin/checklists/{$list->id}/sharing", ['slug' => 'our-wedding'])->assertSessionHasNoErrors();
    expect($list->fresh()->is_public)->toBeFalse()->and($list->fresh()->allow_ticking)->toBeFalse();
});

test('only the admin can change sharing', function () {
    $list = Checklist::create(['title' => 'Wedding', 'slug' => 'our-wedding']);
    $this->patch("/admin/checklists/{$list->id}/sharing", ['slug' => 'our-wedding', 'is_public' => 1])->assertRedirect('/login');
    $this->actingAs(User::factory()->create(['is_admin' => false]))->patch("/admin/checklists/{$list->id}/sharing", ['slug' => 'our-wedding', 'is_public' => 1])->assertForbidden();
    expect($list->fresh()->is_public)->toBeFalse();
});

test('a private checklist is a 404 at its link, so its existence is not revealed', function () {
    sharedList();
    $this->get('/checklist/our-wedding')->assertNotFound();
    $this->get('/checklist/does-not-exist')->assertNotFound();
});

test('a view-only shared checklist shows items to anyone but offers no way to tick', function () {
    sharedList(['is_public' => true]);

    $response = $this->get('/checklist/our-wedding')->assertOk()
        ->assertSee('Our wedding')->assertSee('Book venue')->assertSee('Call first')->assertSee('Read-only')
        ->assertSee('href="https://example.com/venue"', false)->assertSee('rel="noopener noreferrer nofollow"', false)
        ->assertDontSee('data-checklist-toggle', false);
    expect($response->headers->get('X-Robots-Tag'))->toBe('noindex, nofollow');
    $response->assertSee('noindex', false);

    $item = Checklist::first()->items()->first();
    $this->patchJson("/checklist/our-wedding/items/{$item->id}/toggle")->assertNotFound();
    expect($item->fresh()->is_done)->toBeFalse();
});

test('when ticking is allowed a visitor can tick and untick without logging in, and sees progress update', function () {
    $list = sharedList(['is_public' => true, 'allow_ticking' => true]);
    $item = $list->items()->first();

    $this->get('/checklist/our-wedding')->assertOk()->assertSee('data-checklist-toggle', false)->assertSee('Tap a box to tick it off')
        ->assertSee('csrf-token', false)->assertSee('role="checkbox"', false);

    $this->patchJson("/checklist/our-wedding/items/{$item->id}/toggle")->assertOk()
        ->assertExactJson(['is_done' => true, 'progress' => ['done' => 1, 'total' => 2, 'percent' => 50]]);
    expect($item->fresh()->is_done)->toBeTrue()->and($item->fresh()->done_at)->not->toBeNull();

    $this->patchJson("/checklist/our-wedding/items/{$item->id}/toggle")->assertOk()->assertJsonPath('is_done', false);
    // No-JavaScript fallback redirects back.
    $this->patch("/checklist/our-wedding/items/{$item->id}/toggle")->assertRedirect();
});

test('the admin sees what the visitor ticked', function () {
    $list = sharedList(['is_public' => true, 'allow_ticking' => true]);
    $item = $list->items()->first();
    $this->patchJson("/checklist/our-wedding/items/{$item->id}/toggle")->assertOk();

    $this->actingAs(checklistAdmin())->get('/admin/checklists')->assertOk()->assertSee('1/2')->assertSee('shared · can tick');
});

test('a visitor can only tick items of the list in the link, never another list', function () {
    sharedList(['is_public' => true, 'allow_ticking' => true]);
    $other = Checklist::create(['title' => 'Private work list', 'slug' => 'private-work']);
    $secret = $other->items()->create(['title' => 'Secret']);

    $this->patchJson("/checklist/our-wedding/items/{$secret->id}/toggle")->assertNotFound();
    expect($secret->fresh()->is_done)->toBeFalse();

    // Even with ticking on for another list, the private list's own link does not work.
    $this->patchJson("/checklist/private-work/items/{$secret->id}/toggle")->assertNotFound();
});

test('visitors can never add, edit or delete anything', function () {
    $list = sharedList(['is_public' => true, 'allow_ticking' => true]);
    $item = $list->items()->first();

    $this->post("/admin/checklists/{$list->id}/items", ['title' => 'Hacked'])->assertRedirect('/login');
    $this->patch("/admin/checklist-items/{$item->id}", ['title' => 'Hacked'])->assertRedirect('/login');
    $this->delete("/admin/checklist-items/{$item->id}")->assertRedirect('/login');
    $this->patch("/admin/checklists/{$list->id}/sharing", ['slug' => 'hacked', 'is_public' => 1])->assertRedirect('/login');
    $this->delete("/admin/checklists/{$list->id}")->assertRedirect('/login');
    $this->patch("/checklist/our-wedding/items/{$item->id}", ['title' => 'Hacked'])->assertNotFound();

    expect($list->fresh()->items()->pluck('title')->all())->toBe(['Book venue', 'Send invitations']);
});

test('turning sharing off immediately closes the link and ticking', function () {
    $admin = checklistAdmin();
    $list = sharedList(['is_public' => true, 'allow_ticking' => true]);
    $item = $list->items()->first();
    $this->get('/checklist/our-wedding')->assertOk();

    $this->actingAs($admin)->patch("/admin/checklists/{$list->id}/sharing", ['slug' => 'our-wedding'])->assertSessionHasNoErrors();
    $this->get('/checklist/our-wedding')->assertNotFound();
    $this->patchJson("/checklist/our-wedding/items/{$item->id}/toggle")->assertNotFound();
});

test('ticking switched off while shared keeps the page view-only', function () {
    sharedList(['is_public' => true, 'allow_ticking' => false]);
    $this->get('/checklist/our-wedding')->assertOk()->assertDontSee('data-checklist-toggle', false);
});

test('shared pages escape user content', function () {
    $list = Checklist::create(['title' => '<script>alert(1)</script>', 'slug' => 'xss-test', 'is_public' => true]);
    $list->items()->create(['title' => '<img src=x onerror=alert(2)>', 'note' => '<b>x</b>']);

    $html = $this->get('/checklist/xss-test')->assertOk()->getContent();
    expect($html)->not->toContain('<script>alert(1)</script>')->not->toContain('<img src=x onerror=alert(2)>')->not->toContain('<b>x</b>');
});

test('the admin panel shows the personalised link with a copy button once shared', function () {
    sharedList(['is_public' => true, 'allow_ticking' => true]);
    $this->actingAs(checklistAdmin())->get('/admin/checklists')->assertOk()
        ->assertSee('/checklist/our-wedding', false)->assertSee('data-copy', false)->assertSee('Copy link')
        ->assertSee('Anyone with the link can view')->assertSee('can tick items off');
});

test('the slug in the link is case sensitive-safe: uppercase paths do not match', function () {
    sharedList(['is_public' => true]);
    $this->get('/checklist/OUR-WEDDING')->assertNotFound();
});
