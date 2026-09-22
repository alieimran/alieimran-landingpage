<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Theme') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="bg-green-50 dark:bg-green-900 text-green-800 dark:text-green-200 text-sm rounded-md p-4">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('admin.theme.update') }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div>
                        <x-input-label for="primary_color" value="Primary accent color" />
                        <div class="mt-1 flex items-center gap-3">
                            <input type="color" id="primary_color" name="primary_color" value="{{ old('primary_color', $theme->primary_color) }}" class="h-10 w-16 rounded border border-gray-300 dark:border-gray-700 bg-transparent">
                            <x-text-input type="text" class="block w-32 font-mono" :value="old('primary_color', $theme->primary_color)" oninput="document.getElementById('primary_color').value = this.value" onchange="this.previousElementSibling.value = this.value" />
                        </div>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Overrides the emerald accent used for buttons, links, and highlights across the whole site.</p>
                        <x-input-error :messages="$errors->get('primary_color')" class="mt-2" />
                    </div>

                    <div class="mt-6">
                        <x-input-label for="logo" value="Logo" />
                        <input id="logo" name="logo" type="file" accept="image/png,image/webp" class="mt-1 block w-full text-sm text-gray-600 dark:text-gray-400">
                        @if ($theme->logo_path)
                            <img src="{{ \Illuminate\Support\Facades\Storage::url($theme->logo_path) }}" alt="" class="mt-2 h-10">
                        @endif
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Replaces the terminal-prompt mark in the nav and login page. PNG or WebP only.</p>
                        <x-input-error :messages="$errors->get('logo')" class="mt-2" />
                    </div>

                    <div class="mt-6">
                        <x-input-label for="favicon" value="Favicon" />
                        <input id="favicon" name="favicon" type="file" accept="image/png" class="mt-1 block w-full text-sm text-gray-600 dark:text-gray-400">
                        @if ($theme->favicon_path)
                            <img src="{{ \Illuminate\Support\Facades\Storage::url($theme->favicon_path) }}" alt="" class="mt-2 h-8 w-8">
                        @endif
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">PNG only. Replaces the default browser-tab icon.</p>
                        <x-input-error :messages="$errors->get('favicon')" class="mt-2" />
                    </div>

                    <div class="mt-6">
                        <x-primary-button>{{ __('Save Changes') }}</x-primary-button>
                    </div>
                </form>
            </div>

            <p class="font-mono text-[11px] text-gray-500 dark:text-gray-500">
                Not yet configurable here: secondary/background/text colors, button style, border radius, font family, background image. Colors beyond the primary accent would need every page's color classes rewritten to read from these settings instead of fixed Tailwind utilities — a larger change than this pass covers. See DESIGN_SYSTEM.md.
            </p>
        </div>
    </div>
</x-app-layout>
