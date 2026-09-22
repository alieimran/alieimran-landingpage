<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreLinkRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->is_admin ?? false;
    }

    /**
     * Unchecked HTML checkboxes are omitted from the request entirely,
     * not sent as false — without this, unchecking "Enabled" and
     * saving would silently leave the link enabled, since validated()
     * only returns keys that were actually present in the input.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'enabled' => $this->boolean('enabled'),
            'featured' => $this->boolean('featured'),
            'open_in_new_tab' => $this->boolean('open_in_new_tab'),
        ]);
    }

    public function rules(): array
    {
        return [
            'link_category_id' => ['nullable', 'exists:link_categories,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'url' => ['required', 'url', 'max:2048'],
            'icon' => ['nullable', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'enabled' => ['boolean'],
            'featured' => ['boolean'],
            'sort_order' => ['integer', 'min:0'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'open_in_new_tab' => ['boolean'],
        ];
    }
}
