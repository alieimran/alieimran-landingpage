<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

// GET /register now falls through to the share-page slug route, which
// queries the database before 404ing — and because that route is
// GET-only, POST /register is answered with 405 rather than 404.
uses(RefreshDatabase::class);

test('registration routes do not exist', function () {
    $this->get('/register')->assertNotFound();
    $this->post('/register')->assertMethodNotAllowed();
});
