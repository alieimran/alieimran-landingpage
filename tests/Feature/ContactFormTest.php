<?php

use App\Mail\NewContactInquiryMail;
use App\Models\ContactInquiry;
use App\Models\SiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

function validContactPayload(array $overrides = []): array
{
    return array_merge([
        'name' => 'Jane Visitor',
        'email' => 'jane@example.com',
        'phone' => '+60123456789',
        'subject' => 'Let\'s work together',
        'message' => 'I saw your portfolio and would like to discuss a project.',
        'category' => 'project_inquiry',
    ], $overrides);
}

test('contact page renders', function () {
    $this->get(route('contact.create'))->assertOk();
});

test('a visitor can submit the contact form', function () {
    Mail::fake();

    $this->post(route('contact.store'), validContactPayload())
        ->assertRedirect(route('contact.create'));

    $this->assertDatabaseHas('contact_inquiries', [
        'email' => 'jane@example.com',
        'category' => 'project_inquiry',
        'status' => 'new',
    ]);
});

test('the site notification email is sent when configured', function () {
    Mail::fake();

    SiteSetting::current()->update(['contact_notification_email' => 'admin@example.com']);

    $this->post(route('contact.store'), validContactPayload());

    Mail::assertSent(NewContactInquiryMail::class, fn ($mail) => $mail->hasTo('admin@example.com'));
});

test('mail failure does not prevent the inquiry from being stored', function () {
    Mail::shouldReceive('to->send')->andThrow(new Exception('SMTP down'));

    SiteSetting::current()->update(['contact_notification_email' => 'admin@example.com']);

    $this->post(route('contact.store'), validContactPayload())
        ->assertRedirect(route('contact.create'));

    $this->assertDatabaseHas('contact_inquiries', ['email' => 'jane@example.com']);
});

test('invalid category is rejected', function () {
    $this->post(route('contact.store'), validContactPayload(['category' => 'not-a-real-category']))
        ->assertSessionHasErrors('category');
});

test('missing required fields are rejected', function () {
    $this->post(route('contact.store'), [])
        ->assertSessionHasErrors(['name', 'email', 'subject', 'message', 'category']);
});

test('honeypot field silently blocks submission without erroring', function () {
    Mail::fake();

    $this->post(route('contact.store'), validContactPayload(['website' => 'http://spam.example']))
        ->assertRedirect(route('contact.create'))
        ->assertSessionMissing('errors');

    expect(ContactInquiry::count())->toBe(0);
});

test('the contact form is rate limited', function () {
    for ($i = 0; $i < 5; $i++) {
        $this->post(route('contact.store'), validContactPayload(['email' => "visitor{$i}@example.com"]));
    }

    $this->post(route('contact.store'), validContactPayload(['email' => 'toomany@example.com']))
        ->assertStatus(429);
});
