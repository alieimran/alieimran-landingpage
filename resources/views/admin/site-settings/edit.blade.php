<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Site Settings') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="bg-green-50 dark:bg-green-900 text-green-800 dark:text-green-200 text-sm rounded-md p-4 mb-6">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('admin.site-settings.update') }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div>
                        <x-input-label for="full_name" value="Full name" />
                        <x-text-input id="full_name" name="full_name" type="text" class="mt-1 block w-full" :value="old('full_name', $setting->full_name)" required />
                        <x-input-error :messages="$errors->get('full_name')" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <x-input-label for="display_name" value="Display name" />
                        <x-text-input id="display_name" name="display_name" type="text" class="mt-1 block w-full" :value="old('display_name', $setting->display_name)" required />
                        <x-input-error :messages="$errors->get('display_name')" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <x-input-label for="job_title" value="Job title" />
                        <x-text-input id="job_title" name="job_title" type="text" class="mt-1 block w-full" :value="old('job_title', $setting->job_title)" />
                        <x-input-error :messages="$errors->get('job_title')" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <x-input-label for="tagline" value="Tagline" />
                        <x-text-input id="tagline" name="tagline" type="text" class="mt-1 block w-full" :value="old('tagline', $setting->tagline)" />
                        <x-input-error :messages="$errors->get('tagline')" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <x-input-label for="biography" value="Short biography" />
                        <textarea id="biography" name="biography" rows="4" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm">{{ old('biography', $setting->biography) }}</textarea>
                        <x-input-error :messages="$errors->get('biography')" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <x-input-label for="profile_photo" value="Profile photo" />
                        <input id="profile_photo" name="profile_photo" type="file" accept="image/png,image/jpeg,image/webp" class="mt-1 block w-full text-sm text-gray-600 dark:text-gray-400" />
                        @if ($setting->profile_photo)
                            <img src="{{ \Illuminate\Support\Facades\Storage::url($setting->profile_photo) }}" alt="" class="mt-2 h-20 w-20 object-cover rounded-full">
                        @endif
                        <x-input-error :messages="$errors->get('profile_photo')" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <x-input-label for="location" value="Location" />
                        <x-text-input id="location" name="location" type="text" class="mt-1 block w-full" :value="old('location', $setting->location)" />
                        <x-input-error :messages="$errors->get('location')" class="mt-2" />
                    </div>

                    <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="primary_cta_label" value="Primary CTA label" />
                            <x-text-input id="primary_cta_label" name="primary_cta_label" type="text" class="mt-1 block w-full" :value="old('primary_cta_label', $setting->primary_cta_label)" />
                            <x-input-error :messages="$errors->get('primary_cta_label')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="primary_cta_url" value="Primary CTA URL" />
                            <x-text-input id="primary_cta_url" name="primary_cta_url" type="url" class="mt-1 block w-full" :value="old('primary_cta_url', $setting->primary_cta_url)" />
                            <x-input-error :messages="$errors->get('primary_cta_url')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="secondary_cta_label" value="Secondary CTA label" />
                            <x-text-input id="secondary_cta_label" name="secondary_cta_label" type="text" class="mt-1 block w-full" :value="old('secondary_cta_label', $setting->secondary_cta_label)" />
                            <x-input-error :messages="$errors->get('secondary_cta_label')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="secondary_cta_url" value="Secondary CTA URL" />
                            <x-text-input id="secondary_cta_url" name="secondary_cta_url" type="url" class="mt-1 block w-full" :value="old('secondary_cta_url', $setting->secondary_cta_url)" />
                            <x-input-error :messages="$errors->get('secondary_cta_url')" class="mt-2" />
                        </div>
                    </div>

                    <div class="mt-4">
                        <x-input-label for="portfolio_url" value="Portfolio URL" />
                        <x-text-input id="portfolio_url" name="portfolio_url" type="url" class="mt-1 block w-full" :value="old('portfolio_url', $setting->portfolio_url)" placeholder="https://www.alieimran.com/portfolio" />
                        <x-input-error :messages="$errors->get('portfolio_url')" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <x-input-label for="contact_notification_email" value="Contact notification email" />
                        <x-text-input id="contact_notification_email" name="contact_notification_email" type="email" class="mt-1 block w-full" :value="old('contact_notification_email', $setting->contact_notification_email)" />
                        <x-input-error :messages="$errors->get('contact_notification_email')" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <x-input-label for="ga_tracking_id" value="Google Analytics tracking ID" />
                        <x-text-input id="ga_tracking_id" name="ga_tracking_id" type="text" class="mt-1 block w-full" :value="old('ga_tracking_id', $setting->ga_tracking_id)" placeholder="G-XXXXXXXXXX" />
                        <x-input-error :messages="$errors->get('ga_tracking_id')" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="profile_visible" value="1" @checked(old('profile_visible', $setting->profile_visible))>
                            <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('Profile visible on homepage') }}</span>
                        </label>
                    </div>

                    <div class="mt-6">
                        <x-primary-button>{{ __('Save Changes') }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
