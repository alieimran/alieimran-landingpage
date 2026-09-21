<?php

use App\Models\ContactInquiry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('admin can view the inquiry list', function () {
    ContactInquiry::create([
        'name' => 'Jane', 'email' => 'jane@example.com', 'subject' => 'Hi', 'message' => 'Hello there', 'category' => 'general_inquiry',
    ]);

    $this->actingAs(admin())->get(route('admin.contact-inquiries.index'))->assertOk()->assertSee('Jane');
});

test('viewing an inquiry marks it as read', function () {
    $inquiry = ContactInquiry::create([
        'name' => 'Jane', 'email' => 'jane@example.com', 'subject' => 'Hi', 'message' => 'Hello there', 'category' => 'general_inquiry',
    ])->fresh();

    expect($inquiry->status)->toBe('new');

    $this->actingAs(admin())->get(route('admin.contact-inquiries.show', $inquiry))->assertOk();

    expect($inquiry->fresh()->status)->toBe('read');
});

test('admin can update inquiry status', function () {
    $inquiry = ContactInquiry::create([
        'name' => 'Jane', 'email' => 'jane@example.com', 'subject' => 'Hi', 'message' => 'Hello there', 'category' => 'general_inquiry',
    ]);

    $this->actingAs(admin())->put(route('admin.contact-inquiries.update', $inquiry), ['status' => 'archived'])
        ->assertRedirect(route('admin.contact-inquiries.show', $inquiry));

    expect($inquiry->fresh()->status)->toBe('archived');
});

test('admin can delete an inquiry', function () {
    $inquiry = ContactInquiry::create([
        'name' => 'Jane', 'email' => 'jane@example.com', 'subject' => 'Hi', 'message' => 'Hello there', 'category' => 'general_inquiry',
    ]);

    $this->actingAs(admin())->delete(route('admin.contact-inquiries.destroy', $inquiry))
        ->assertRedirect(route('admin.contact-inquiries.index'));

    expect(ContactInquiry::find($inquiry->id))->toBeNull();
});

test('non-admins cannot access the inquiry inbox', function () {
    $this->actingAs(User::factory()->create())->get(route('admin.contact-inquiries.index'))->assertForbidden();
});

test('guests cannot access the inquiry inbox', function () {
    $this->get(route('admin.contact-inquiries.index'))->assertRedirect('/login');
});
