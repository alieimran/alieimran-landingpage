@php($link = $link ?? null)

<div>
    <x-input-label for="link_category_id" value="Category" />
    <select id="link_category_id" name="link_category_id" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm">
        <option value="">— None —</option>
        @foreach ($categories as $category)
            <option value="{{ $category->id }}" @selected(old('link_category_id', $link?->link_category_id) == $category->id)>
                {{ $category->name }}
            </option>
        @endforeach
    </select>
    <x-input-error :messages="$errors->get('link_category_id')" class="mt-2" />
</div>

<div class="mt-4">
    <x-input-label for="title" value="Title" />
    <x-text-input id="title" name="title" type="text" class="mt-1 block w-full" :value="old('title', $link?->title)" required />
    <x-input-error :messages="$errors->get('title')" class="mt-2" />
</div>

<div class="mt-4">
    <x-input-label for="description" value="Description" />
    <textarea id="description" name="description" rows="3" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm">{{ old('description', $link?->description) }}</textarea>
    <x-input-error :messages="$errors->get('description')" class="mt-2" />
</div>

<div class="mt-4">
    <x-input-label for="url" value="URL" />
    <x-text-input id="url" name="url" type="url" class="mt-1 block w-full" :value="old('url', $link?->url)" required />
    <x-input-error :messages="$errors->get('url')" class="mt-2" />
</div>

<div class="mt-4">
    <x-input-label for="icon" value="Icon (identifier)" />
    <x-text-input id="icon" name="icon" type="text" class="mt-1 block w-full" :value="old('icon', $link?->icon)" />
    <x-input-error :messages="$errors->get('icon')" class="mt-2" />
</div>

<div class="mt-4">
    <x-input-label for="image" value="Image" />
    <input id="image" name="image" type="file" accept="image/png,image/jpeg,image/webp" class="mt-1 block w-full text-sm text-gray-600 dark:text-gray-400" />
    @if ($link?->image)
        <img src="{{ \Illuminate\Support\Facades\Storage::url($link->image) }}" alt="" class="mt-2 h-16 w-16 object-cover rounded">
    @endif
    <x-input-error :messages="$errors->get('image')" class="mt-2" />
</div>

<div class="mt-4 grid grid-cols-2 gap-4">
    <div>
        <x-input-label for="start_date" value="Start date" />
        <x-text-input id="start_date" name="start_date" type="date" class="mt-1 block w-full" :value="old('start_date', $link?->start_date?->format('Y-m-d'))" />
        <x-input-error :messages="$errors->get('start_date')" class="mt-2" />
    </div>
    <div>
        <x-input-label for="end_date" value="End date" />
        <x-text-input id="end_date" name="end_date" type="date" class="mt-1 block w-full" :value="old('end_date', $link?->end_date?->format('Y-m-d'))" />
        <x-input-error :messages="$errors->get('end_date')" class="mt-2" />
    </div>
</div>

<div class="mt-4">
    <x-input-label for="sort_order" value="Sort order" />
    <x-text-input id="sort_order" name="sort_order" type="number" min="0" class="mt-1 block w-full sm:w-32" :value="old('sort_order', $link?->sort_order ?? 0)" />
    <x-input-error :messages="$errors->get('sort_order')" class="mt-2" />
</div>

<div class="mt-4 space-y-2">
    <label class="flex items-center gap-2">
        <input type="checkbox" name="enabled" value="1" @checked(old('enabled', $link?->enabled ?? true))>
        <span class="text-sm text-gray-700 dark:text-gray-300">Enabled</span>
    </label>
    <label class="flex items-center gap-2">
        <input type="checkbox" name="featured" value="1" @checked(old('featured', $link?->featured))>
        <span class="text-sm text-gray-700 dark:text-gray-300">Featured</span>
    </label>
    <label class="flex items-center gap-2">
        <input type="checkbox" name="open_in_new_tab" value="1" @checked(old('open_in_new_tab', $link?->open_in_new_tab ?? true))>
        <span class="text-sm text-gray-700 dark:text-gray-300">Open in new tab</span>
    </label>
</div>
