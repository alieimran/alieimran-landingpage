<?php

use App\Models\DigitalCard;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('the digital card page is hidden until enabled', function () {
    $this->get(route('card'))->assertNotFound();
});

test('the digital card page shows a qr code once enabled', function () {
    DigitalCard::current()->update(['name' => 'Alie Imran', 'enabled' => true]);

    $response = $this->get(route('card'));

    $response->assertOk();
    $response->assertSee('Alie Imran');
    $response->assertSee('data:image', false);
});

test('admin can update the digital card', function () {
    $this->actingAs(admin())->put(route('admin.digital-card.update'), [
        'name' => 'Alie Imran',
        'email' => 'alieimran@outlook.com',
        'enabled' => '1',
    ])->assertRedirect(route('admin.digital-card.edit'));

    expect(DigitalCard::current()->name)->toBe('Alie Imran');
});

test('non-admins cannot update the digital card', function () {
    $this->actingAs(User::factory()->create())->put(route('admin.digital-card.update'), [
        'name' => 'Nope',
    ])->assertForbidden();
});
