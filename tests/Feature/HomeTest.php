<?php

use App\Models\Link;
use App\Models\SocialLink;
use Database\Seeders\SiteSectionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(SiteSectionSeeder::class);
});

test('homepage renders successfully', function () {
    $this->get('/')->assertOk();
});

test('homepage shows the profile display name', function () {
    $this->get('/')->assertSee(config('app.name'));
});

test('homepage only shows visible links', function () {
    Link::create(['title' => 'Visible Link', 'url' => 'https://example.com', 'enabled' => true]);
    Link::create(['title' => 'Hidden Link', 'url' => 'https://example.com', 'enabled' => false]);
    Link::create([
        'title' => 'Expired Link', 'url' => 'https://example.com',
        'enabled' => true, 'end_date' => now()->subDay(),
    ]);

    $response = $this->get('/');

    $response->assertSee('Visible Link');
    $response->assertDontSee('Hidden Link');
    $response->assertDontSee('Expired Link');
});

test('homepage only shows enabled social links', function () {
    SocialLink::create(['platform' => 'GitHub', 'url' => 'https://github.com/alieimran', 'enabled' => true]);
    SocialLink::create(['platform' => 'Old', 'url' => 'https://example.com', 'enabled' => false]);

    $response = $this->get('/');

    $response->assertSee('GitHub');
    $response->assertDontSee('Old');
});
