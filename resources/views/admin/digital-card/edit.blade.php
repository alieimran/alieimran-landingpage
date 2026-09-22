<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Digital Business Card') }}
            </h2>
            @if ($card->enabled)
                <a href="{{ route('card') }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1.5 px-3 py-2 border border-gray-200 dark:border-gray-700 rounded-md text-sm font-medium text-gray-600 dark:text-gray-300 hover:border-emerald-500/40 hover:text-emerald-600 dark:hover:text-emerald-400 transition">
                    {{ __('View Card') }}
                    <svg class="w-3.5 h-3.5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.22 14.78a.75.75 0 001.06 0l7.22-7.22v5.69a.75.75 0 001.5 0v-7.5a.75.75 0 00-.75-.75h-7.5a.75.75 0 000 1.5h5.69l-7.22 7.22a.75.75 0 000 1.06z" clip-rule="evenodd" /></svg>
                </a>
            @endif
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="bg-green-50 dark:bg-green-900 text-green-800 dark:text-green-200 text-sm rounded-md p-4 mb-6">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('admin.digital-card.update') }}">
                    @csrf
                    @method('PUT')

                    <div>
                        <x-input-label for="name" value="Name" />
                        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $card->name)" required />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <x-input-label for="job_title" value="Job title" />
                        <x-text-input id="job_title" name="job_title" type="text" class="mt-1 block w-full" :value="old('job_title', $card->job_title)" />
                        <x-input-error :messages="$errors->get('job_title')" class="mt-2" />
                    </div>

                    <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="email" value="Email" />
                            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $card->email)" />
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="phone" value="Phone" />
                            <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full" :value="old('phone', $card->phone)" />
                            <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                        </div>
                    </div>

                    <div class="mt-4">
                        <x-input-label for="website" value="Website" />
                        <x-text-input id="website" name="website" type="url" class="mt-1 block w-full" :value="old('website', $card->website)" placeholder="https://www.alieimran.com" />
                        <x-input-error :messages="$errors->get('website')" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <x-input-label for="linkedin_url" value="LinkedIn URL" />
                        <x-text-input id="linkedin_url" name="linkedin_url" type="url" class="mt-1 block w-full" :value="old('linkedin_url', $card->linkedin_url)" />
                        <x-input-error :messages="$errors->get('linkedin_url')" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <x-input-label for="github_url" value="GitHub URL" />
                        <x-text-input id="github_url" name="github_url" type="url" class="mt-1 block w-full" :value="old('github_url', $card->github_url)" />
                        <x-input-error :messages="$errors->get('github_url')" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="enabled" value="1" @checked(old('enabled', $card->enabled))>
                            <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('Card enabled (publicly visible at /card)') }}</span>
                        </label>
                    </div>

                    <div class="mt-6">
                        <x-primary-button>{{ __('Save Changes') }}</x-primary-button>
                    </div>
                </form>
            </div>

            <p class="mt-4 font-mono text-[11px] text-gray-500 dark:text-gray-500">
                A QR code pointing to <code>{{ url('/card') }}</code> is generated automatically on the card page — nothing to configure here.
            </p>
        </div>
    </div>
</x-app-layout>
