<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactInquiryRequest;
use App\Mail\NewContactInquiryMail;
use App\Models\ContactInquiry;
use App\Models\SiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class ContactController extends Controller
{
    public const CATEGORIES = [
        'general_inquiry' => 'General Inquiry',
        'feedback' => 'Feedback',
        'complaint' => 'Complaint',
        'collaboration' => 'Collaboration',
        'job_opportunity' => 'Job Opportunity',
        'project_inquiry' => 'Project Inquiry',
        'other' => 'Other',
    ];

    public function create(): View
    {
        return view('contact', [
            'profile' => SiteSetting::current(),
            'categories' => self::CATEGORIES,
        ]);
    }

    public function store(StoreContactInquiryRequest $request): RedirectResponse
    {
        if ($request->isHoneypotTriggered()) {
            return redirect()->route('contact.create')->with('status', 'sent');
        }

        $inquiry = ContactInquiry::create($request->validated());

        $notifyEmail = SiteSetting::current()->contact_notification_email;

        if ($notifyEmail) {
            try {
                Mail::to($notifyEmail)->send(new NewContactInquiryMail($inquiry));
            } catch (\Throwable $e) {
                Log::warning('Failed to send contact inquiry notification email.', [
                    'inquiry_id' => $inquiry->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return redirect()->route('contact.create')->with('status', 'sent');
    }
}
