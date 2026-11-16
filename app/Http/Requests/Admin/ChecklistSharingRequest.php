<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ChecklistSharingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->is_admin ?? false;
    }

    /** Normalise what the admin typed ("My Wedding!" -> "my-wedding") and treat an unchecked box as false. */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'slug' => Str::slug((string) $this->input('slug')),
            'is_public' => $this->boolean('is_public'),
            'allow_ticking' => $this->boolean('allow_ticking'),
        ]);
    }

    public function rules(): array
    {
        return [
            // The slug is always required, whether or not the list is shared.
            'slug' => [
                'required', 'string', 'min:3', 'max:60', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('checklists', 'slug')->ignore($this->route('checklist')?->id),
            ],
            'is_public' => ['boolean'],
            'allow_ticking' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'slug.required' => 'A link slug is required to share a checklist.',
            'slug.regex' => 'Use only lowercase letters, numbers and single dashes.',
            'slug.unique' => 'That link is already used by another checklist.',
            'slug.min' => 'The slug needs at least 3 characters.',
        ];
    }
}
