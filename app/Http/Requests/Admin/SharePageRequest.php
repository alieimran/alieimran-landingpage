<?php

namespace App\Http\Requests\Admin;

use App\Models\SharePage;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class SharePageRequest extends FormRequest
{
    /**
     * Real folders in the live public_html that LiteSpeed serves
     * before Laravel ever sees the request, so a share page with one
     * of these slugs would be unreachable. They don't exist locally,
     * so they can't be discovered from public_path().
     */
    private const SERVER_RESERVED = ['tunang', 'cgi-bin', 'home', 'storage', 'build'];

    public function authorize(): bool
    {
        return $this->user()?->is_admin ?? false;
    }

    /**
     * Unchecked HTML checkboxes are omitted from the request entirely,
     * not sent as false — see StoreLinkRequest.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'slug' => Str::slug((string) $this->input('slug')),
            'enabled' => $this->boolean('enabled'),
            'refresh_preview' => $this->boolean('refresh_preview'),
        ]);
    }

    public function rules(): array
    {
        /** @var SharePage|null $page */
        $page = $this->route('share_page');

        return [
            'slug' => [
                'required', 'string', 'max:100', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('share_pages', 'slug')->ignore($page?->id),
                function (string $attribute, mixed $value, Closure $fail) {
                    if (in_array($value, self::reservedSlugs(), true)) {
                        $fail('This slug is already used by another part of the site.');
                    }
                },
            ],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'target_url' => ['required', 'url:http,https', 'max:2048'],
            'button_label' => ['nullable', 'string', 'max:60'],
            'images' => ['nullable', 'array'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'remove_images' => ['nullable', 'array'],
            'remove_images.*' => ['string'],
            'enabled' => ['boolean'],
            'refresh_preview' => ['boolean'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                /** @var SharePage|null $page */
                $page = $this->route('share_page');

                $kept = array_diff($page?->images ?? [], $this->input('remove_images', []));
                $total = count($kept) + count($this->file('images', []));

                if ($total > SharePage::MAX_IMAGES) {
                    $validator->errors()->add('images', 'A page can have at most '.SharePage::MAX_IMAGES.' images.');
                }
            },
        ];
    }

    public function messages(): array
    {
        return [
            'slug.regex' => 'Use only lowercase letters, numbers and single dashes.',
            'images.*.max' => 'Each image must be 10 MB or smaller.',
        ];
    }

    /**
     * First path segment of every other route (admin, login, card,
     * contact, go, ...) plus every real file/folder under public/.
     *
     * @return list<string>
     */
    public static function reservedSlugs(): array
    {
        $fromRoutes = collect(Route::getRoutes()->getRoutes())
            ->map(fn ($route) => strtolower(explode('/', $route->uri())[0]))
            ->reject(fn (string $segment) => $segment === '' || str_starts_with($segment, '{'));

        $fromPublic = collect(File::glob(public_path('*')))->map(fn (string $path) => strtolower(basename($path)));

        return $fromRoutes->merge($fromPublic)->merge(self::SERVER_RESERVED)->unique()->values()->all();
    }
}
