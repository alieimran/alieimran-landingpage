@php($page = $page ?? null)
@php($images = $page?->images ?? [])

<div>
    <x-input-label for="slug" value="Link" />
    <div class="mt-1 flex rounded-md shadow-sm">
        <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 font-mono text-sm text-gray-500">{{ preg_replace('#^https?://#', '', rtrim(config('app.url'), '/')) }}/</span>
        <input id="slug" name="slug" type="text" value="{{ old('slug', $page?->slug) }}" required maxlength="100" placeholder="gambartunang"
               class="block w-full min-w-0 rounded-none rounded-r-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 font-mono text-sm focus:border-emerald-500 focus:ring-emerald-500">
    </div>
    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Lowercase letters, numbers and dashes.</p>
    <x-input-error :messages="$errors->get('slug')" class="mt-2" />
</div>

<div class="mt-4">
    <x-input-label for="title" value="Title" />
    <x-text-input id="title" name="title" type="text" class="mt-1 block w-full" :value="old('title', $page?->title)" required />
    <x-input-error :messages="$errors->get('title')" class="mt-2" />
</div>

<div class="mt-4">
    <x-input-label for="description" value="Description (optional)" />
    <textarea id="description" name="description" rows="3" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm">{{ old('description', $page?->description) }}</textarea>
    <x-input-error :messages="$errors->get('description')" class="mt-2" />
</div>

<div class="mt-4">
    <x-input-label for="target_url" value="Button goes to (e.g. Google Photos album link)" />
    <x-text-input id="target_url" name="target_url" type="url" class="mt-1 block w-full" :value="old('target_url', $page?->target_url)" required />
    <x-input-error :messages="$errors->get('target_url')" class="mt-2" />
</div>

<div class="mt-4">
    <x-input-label for="button_label" value="Button text" />
    <x-text-input id="button_label" name="button_label" type="text" class="mt-1 block w-full" :value="old('button_label', $page?->button_label)" placeholder="View full album" maxlength="60" />
    <x-input-error :messages="$errors->get('button_label')" class="mt-2" />
</div>

<div class="mt-6">
    <x-input-label for="images" value="Photos (up to {{ \App\Models\SharePage::MAX_IMAGES }})" />
    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
        Photos are resized and compressed automatically. The first photo is the cover.
        If you add none, the album's own cover photo is used when the link provides one.
    </p>

    @if ($images !== [])
        <div class="mt-3 grid grid-cols-3 sm:grid-cols-5 gap-2">
            @foreach ($images as $path)
                <label class="relative block cursor-pointer">
                    <img src="{{ \Illuminate\Support\Facades\Storage::url($path) }}" alt="" class="w-full aspect-square object-cover rounded">
                    <span class="absolute inset-x-0 bottom-0 flex items-center gap-1 bg-black/60 px-1.5 py-1 text-[11px] text-white rounded-b">
                        <input type="checkbox" name="remove_images[]" value="{{ $path }}" class="rounded" @checked(in_array($path, old('remove_images', []), true))>
                        Remove
                    </span>
                </label>
            @endforeach
        </div>
    @elseif ($page?->preview_image_url)
        <div class="mt-3">
            <img src="{{ $page->preview_image_url }}" alt="" class="h-28 rounded object-cover" referrerpolicy="no-referrer">
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Preview taken from the link.</p>
        </div>
    @endif

    <input id="images" name="images[]" type="file" multiple accept="image/*" data-compress-images
           class="mt-3 block w-full text-sm text-gray-600 dark:text-gray-400" />
    <x-input-error :messages="$errors->get('images')" class="mt-2" />
    <x-input-error :messages="collect($errors->get('images.*'))->flatten()->unique()->all()" class="mt-2" />
</div>

<div class="mt-6 space-y-2">
    <label class="flex items-center gap-2">
        <input type="checkbox" name="enabled" value="1" @checked(old('enabled', $page?->enabled ?? true))>
        <span class="text-sm text-gray-700 dark:text-gray-300">Enabled (page is publicly reachable)</span>
    </label>
    @if ($page)
        <label class="flex items-center gap-2">
            <input type="checkbox" name="refresh_preview" value="1">
            <span class="text-sm text-gray-700 dark:text-gray-300">Re-fetch cover photo from the link</span>
        </label>
    @endif
</div>
