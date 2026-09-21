<?php

test('registration routes do not exist', function () {
    $this->get('/register')->assertNotFound();
    $this->post('/register')->assertNotFound();
});
