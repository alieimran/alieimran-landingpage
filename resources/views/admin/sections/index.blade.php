<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Homepage Sections') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="bg-green-50 dark:bg-green-900 text-green-800 dark:text-green-200 text-sm rounded-md p-4">
                    {{ session('status') }}
                </div>
            @endif

            <p class="text-sm text-gray-600 dark:text-gray-400">
                Controls which blocks appear on the homepage, their order, and their labels. Sections themselves are fixed to what the homepage template supports — you can hide, reorder, and relabel them here, not add new ones.
            </p>

            <div class="space-y-4">
                @foreach ($sections as $section)
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg p-6">
                        <form method="POST" action="{{ route('admin.sections.update', $section) }}" class="space-y-4">
                            @csrf
                            @method('PUT')

                            <div class="flex items-center justify-between">
                                <span class="font-mono text-xs uppercase tracking-widest text-gray-400 dark:text-gray-600">{{ $section->key }}</span>
                                <span class="font-mono text-xs text-gray-400 dark:text-gray-600">#{{ $section->sort_order }}</span>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <x-input-label value="Title" />
                                    <x-text-input name="title" type="text" class="mt-1 block w-full" :value="old('title', $section->title)" required />
                                </div>
                                <div>
                                    <x-input-label value="Sort order" />
                                    <x-text-input name="sort_order" type="number" min="0" class="mt-1 block w-full" :value="old('sort_order', $section->sort_order)" />
                                </div>
                            </div>

                            <div>
                                <x-input-label value="Description (internal note)" />
                                <x-text-input name="description" type="text" class="mt-1 block w-full" :value="old('description', $section->description)" />
                            </div>

                            <div class="flex flex-wrap gap-5">
                                <label class="flex items-center gap-2">
                                    <input type="checkbox" name="enabled" value="1" @checked(old('enabled', $section->enabled))>
                                    <span class="text-sm text-gray-700 dark:text-gray-300">Enabled</span>
                                </label>
                                <label class="flex items-center gap-2">
                                    <input type="checkbox" name="homepage_visible" value="1" @checked(old('homepage_visible', $section->homepage_visible))>
                                    <span class="text-sm text-gray-700 dark:text-gray-300">Visible on homepage</span>
                                </label>
                                <label class="flex items-center gap-2">
                                    <input type="checkbox" name="nav_visible" value="1" @checked(old('nav_visible', $section->nav_visible))>
                                    <span class="text-sm text-gray-700 dark:text-gray-300">Visible in navigation</span>
                                </label>
                            </div>

                            <x-primary-button>{{ __('Save') }}</x-primary-button>
                        </form>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>
