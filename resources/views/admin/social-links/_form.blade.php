@php($socialLink = $socialLink ?? null)

<div>
    <x-input-label for="platform" value="Platform" />
    <x-text-input id="platform" name="platform" type="text" class="mt-1 block w-full" :value="old('platform', $socialLink?->platform)" required placeholder="LinkedIn, GitHub, Instagram..." />
    <x-input-error :messages="$errors->get('platform')" class="mt-2" />
</div>

<div class="mt-4">
    <x-input-label for="display_name" value="Display name" />
    <x-text-input id="display_name" name="display_name" type="text" class="mt-1 block w-full" :value="old('display_name', $socialLink?->display_name)" />
    <x-input-error :messages="$errors->get('display_name')" class="mt-2" />
</div>

<div class="mt-4">
    <x-input-label for="url" value="URL" />
    <x-text-input id="url" name="url" type="url" class="mt-1 block w-full" :value="old('url', $socialLink?->url)" required />
    <x-input-error :messages="$errors->get('url')" class="mt-2" />
</div>

<div class="mt-4">
    <x-input-label for="icon" value="Icon (identifier)" />
    <x-text-input id="icon" name="icon" type="text" class="mt-1 block w-full" :value="old('icon', $socialLink?->icon)" />
    <x-input-error :messages="$errors->get('icon')" class="mt-2" />
</div>

<div class="mt-4">
    <x-input-label for="sort_order" value="Sort order" />
    <x-text-input id="sort_order" name="sort_order" type="number" min="0" class="mt-1 block w-full sm:w-32" :value="old('sort_order', $socialLink?->sort_order ?? 0)" />
    <x-input-error :messages="$errors->get('sort_order')" class="mt-2" />
</div>

<div class="mt-4 space-y-2">
    <label class="flex items-center gap-2">
        <input type="checkbox" name="enabled" value="1" @checked(old('enabled', $socialLink?->enabled ?? true))>
        <span class="text-sm text-gray-700 dark:text-gray-300">Enabled</span>
    </label>
    <label class="flex items-center gap-2">
        <input type="checkbox" name="featured" value="1" @checked(old('featured', $socialLink?->featured))>
        <span class="text-sm text-gray-700 dark:text-gray-300">Featured</span>
    </label>
</div>
