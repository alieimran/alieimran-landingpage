<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreChecklistRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->is_admin ?? false;
    }

    protected function prepareForValidation(): void
    {
        // A blank custom link means "generate one for me".
        $this->merge(['slug' => $this->filled('slug') ? Str::slug((string) $this->input('slug')) : null]);
    }

    public function rules(): array
    {
        return [
            'title' => ['nullable', 'string', 'max:120', 'required_without:template'],
            'description' => ['nullable', 'string', 'max:300'],
            'template' => ['nullable', 'string', Rule::in(array_keys(config('checklists.templates')))],
            'slug' => ['nullable', 'string', 'min:3', 'max:60', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('checklists', 'slug')],
        ];
    }

    public function messages(): array
    {
        return [
            'slug.regex' => 'Use only lowercase letters, numbers and single dashes.',
            'slug.unique' => 'That link is already used by another checklist.',
        ];
    }
}
