<?php

namespace App\Http\Requests;

use App\Services\Turnstile;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

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

    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            if ($validator->errors()->isNotEmpty() || $this->isHoneypotTriggered() || ! config('services.turnstile.enabled')) {
                return;
            }

            if (! app(Turnstile::class)->verify($this->input('cf-turnstile-response'), 'contact', $this->ip())) {
                $validator->errors()->add('cf-turnstile-response', 'Please complete the security check and try again. If it has expired, refresh this page.');
            }
        });
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
