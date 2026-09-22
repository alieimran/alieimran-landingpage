<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreSocialLinkRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->is_admin ?? false;
    }

    /**
     * Unchecked HTML checkboxes are omitted from the request entirely,
     * not sent as false — without this, unchecking "Enabled" and
     * saving would silently leave the social link enabled.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'enabled' => $this->boolean('enabled'),
            'featured' => $this->boolean('featured'),
        ]);
    }

    public function rules(): array
    {
        return [
            'platform' => ['required', 'string', 'max:100'],
            'display_name' => ['nullable', 'string', 'max:255'],
            'url' => ['required', 'url', 'max:2048'],
            'icon' => ['nullable', 'string', 'max:255'],
            'enabled' => ['boolean'],
            'featured' => ['boolean'],
            'sort_order' => ['integer', 'min:0'],
        ];
    }
}
