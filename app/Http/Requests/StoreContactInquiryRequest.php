<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreContactInquiryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
            'category' => ['required', 'in:general_inquiry,feedback,complaint,collaboration,job_opportunity,project_inquiry,other'],
        ];
    }

    /**
     * Honeypot field: real visitors never see or fill it. Checked
     * separately from validation so a bot that fills it gets a normal
     * "success" response instead of a validation error that would
     * reveal the trap.
     */
    public function isHoneypotTriggered(): bool
    {
        return filled($this->input('website'));
    }
}
