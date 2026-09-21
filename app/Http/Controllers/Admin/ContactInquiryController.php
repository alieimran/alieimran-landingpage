<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactInquiry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactInquiryController extends Controller
{
    public function index(Request $request): View
    {
        $inquiries = ContactInquiry::query()
            ->when($request->get('status'), fn ($q, $status) => $q->where('status', $status))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.contact-inquiries.index', [
            'inquiries' => $inquiries,
            'statusFilter' => $request->get('status'),
        ]);
    }

    public function show(ContactInquiry $contactInquiry): View
    {
        $contactInquiry->markAsRead();

        return view('admin.contact-inquiries.show', ['inquiry' => $contactInquiry]);
    }

    public function update(Request $request, ContactInquiry $contactInquiry): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:new,read,replied,archived'],
        ]);

        $contactInquiry->update($data);

        return redirect()->route('admin.contact-inquiries.show', $contactInquiry)->with('status', 'Inquiry updated.');
    }

    public function destroy(ContactInquiry $contactInquiry): RedirectResponse
    {
        $contactInquiry->delete();

        return redirect()->route('admin.contact-inquiries.index')->with('status', 'Inquiry deleted.');
    }
}
