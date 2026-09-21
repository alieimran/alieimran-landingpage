<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('sitemap renders valid xml with the homepage url', function () {
    $response = $this->get('/sitemap.xml');

    $response->assertOk();
    $response->assertHeader('Content-Type', 'application/xml; charset=UTF-8');
    $response->assertSee('<urlset', false);
    $response->assertSee(url('/'), false);
});

test('sitemap does not list admin or auth routes', function () {
    $response = $this->get('/sitemap.xml');

    $response->assertDontSee('/admin', false);
    $response->assertDontSee('/login', false);
    $response->assertDontSee('/contact', false);
});

test('robots.txt disallows admin and auth routes', function () {
    // robots.txt is a static file served directly by the webserver,
    // not a Laravel route, so it's read from disk rather than
    // requested through the test HTTP client.
    $contents = file_get_contents(public_path('robots.txt'));

    expect($contents)
        ->toContain('Disallow: /admin')
        ->toContain('Disallow: /login')
        ->toContain('Sitemap:');
});
