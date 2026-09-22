<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSiteSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->is_admin ?? false;
    }

    /**
     * Unchecked HTML checkboxes are omitted from the request entirely,
     * not sent as false — without this, unchecking "Profile visible"
     * and saving would silently leave the profile visible.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'profile_visible' => $this->boolean('profile_visible'),
        ]);
    }

    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:255'],
            'display_name' => ['required', 'string', 'max:255'],
            'job_title' => ['nullable', 'string', 'max:255'],
            'tagline' => ['nullable', 'string', 'max:255'],
            'biography' => ['nullable', 'string', 'max:2000'],
            'profile_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'location' => ['nullable', 'string', 'max:255'],
            'primary_cta_label' => ['nullable', 'string', 'max:100'],
            'primary_cta_url' => ['nullable', 'max:2048', 'regex:/^(https?:\/\/|mailto:|\/).+/i'],
            'secondary_cta_label' => ['nullable', 'string', 'max:100'],
            'secondary_cta_url' => ['nullable', 'max:2048', 'regex:/^(https?:\/\/|mailto:|\/).+/i'],
            'portfolio_url' => ['nullable', 'url', 'max:2048'],
            'contact_notification_email' => ['nullable', 'email', 'max:255'],
            'ga_tracking_id' => ['nullable', 'string', 'max:50'],
            'profile_visible' => ['boolean'],
        ];
    }
}
