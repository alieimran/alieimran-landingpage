<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateThemeSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->is_admin ?? false;
    }

    public function rules(): array
    {
        return [
            'primary_color' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            // SVG deliberately excluded, same reasoning as link/profile
            // image uploads: a directly-navigated SVG can execute
            // embedded scripts, even though an <img src="..."> reference
            // can't.
            'logo' => ['nullable', 'image', 'mimes:png,webp', 'max:1024'],
            'favicon' => ['nullable', 'image', 'mimes:png', 'max:512'],
        ];
    }
}
