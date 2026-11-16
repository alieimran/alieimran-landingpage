<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ChecklistItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->is_admin ?? false;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            // Only http(s): rejects javascript:, data: and other schemes that would run in the admin's browser.
            'url' => ['nullable', 'url:http,https', 'max:2048'],
            'note' => ['nullable', 'string', 'max:500'],
        ];
    }
}
