<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">Maintenance</h2></x-slot>
    @php $content = array_replace(config('maintenance.content'), $setting->maintenance_content ?? []); @endphp
    <div class="py-12"><div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        @if (session('status'))<p role="status" class="rounded-lg bg-emerald-50 dark:bg-emerald-900 p-4 text-sm text-emerald-800 dark:text-emerald-200">{{ session('status') }}</p>@endif
        <section class="rounded-lg bg-white dark:bg-gray-800 p-6 shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Public website availability</h3>
                <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $setting->maintenance_enabled ? 'bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200' : 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900 dark:text-emerald-200' }}">{{ $setting->maintenance_enabled ? 'Maintenance active' : 'Website live' }}</span>
            </div>
            <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">Show visitors a temporary page while you make changes. Admins can keep using the website and CMS. Login and account recovery stay available.</p>
            <form method="POST" action="{{ route('admin.maintenance.update') }}" class="mt-6 space-y-5">
                @csrf @method('PUT')
                <label class="flex min-h-11 items-center gap-3 text-sm font-medium text-gray-900 dark:text-gray-100">
                    <input type="checkbox" name="maintenance_enabled" value="1" @checked(old('maintenance_enabled', $setting->maintenance_enabled)) class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                    Enable maintenance mode
                </label>
                <x-input-error :messages="$errors->get('maintenance_enabled')" />
                <div>
                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">Suggested reasons</p>
                    <div class="mt-2 flex flex-wrap gap-2">
                        @foreach (config('maintenance.suggestions') as $label => $message)
                            <button type="button" data-maintenance-reason="{{ $message }}" class="rounded-lg border border-gray-300 dark:border-gray-600 min-h-11 px-3 py-2 text-xs text-gray-700 dark:text-gray-200 hover:border-emerald-500">{{ $label }}</button>
                        @endforeach
                    </div>
                    <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Choose a starting point, then edit the message below. Suggestions are not saved until you save settings.</p>
                </div>
                <div>
                    <x-input-label for="maintenance_reason" value="Reason shown to visitors" />
                    <textarea id="maintenance_reason" name="maintenance_reason" rows="4" maxlength="2000" class="mt-1 block w-full min-w-0 rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100" placeholder="We are updating the website to improve your experience.">{{ old('maintenance_reason', $setting->maintenance_reason) }}</textarea>
                    <x-input-error :messages="$errors->get('maintenance_reason')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="maintenance_duration" value="How long do you expect to need?" />
                    <select id="maintenance_duration" name="maintenance_duration" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                        <option value="">Choose a custom return time below</option>
                        @foreach (config('maintenance.durations') as $minutes => $label)
                            <option value="{{ $minutes }}" @selected((string) old('maintenance_duration') === (string) $minutes)>{{ $label }} from saving</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('maintenance_duration')" class="mt-2" />
                    <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">15-30 minutes for small content changes; 1-2 hours for updates; 4-24 hours for larger work. Allow time for testing.</p>
                </div>
                <div>
                    <x-input-label for="maintenance_until" value="Expected return time (Malaysia time, UTC+08:00)" />
                    <x-text-input id="maintenance_until" name="maintenance_until" type="datetime-local" class="mt-1 block w-full min-w-0" :value="old('maintenance_until', $setting->maintenance_until?->timezone('Asia/Kuala_Lumpur')->format('Y-m-d\TH:i'))" />
                    <x-input-error :messages="$errors->get('maintenance_until')" class="mt-2" />
                    <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Reason and a future return time are required when enabling maintenance. This is an estimate; maintenance stays on until you disable it.</p>
                </div>
                @if ($setting->maintenance_started_at)<p class="text-xs text-gray-500 dark:text-gray-400">Started {{ $setting->maintenance_started_at->timezone('Asia/Kuala_Lumpur')->format('d M Y, h:i A') }} MYT.</p>@endif
                <fieldset class="space-y-4 border-t border-gray-200 dark:border-gray-700 pt-6">
                    <legend class="text-base font-semibold text-gray-900 dark:text-gray-100">Customize the public page</legend>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Edit the text and choose which details visitors see. Site colors and branding follow your Theme settings. Text is displayed as plain text.</p>
                    @foreach (['label' => 'Status label', 'heading' => 'Main heading', 'button_text' => 'Check-again button text', 'footer' => 'Footer message', 'delayed_message' => 'Message when the estimated time has passed'] as $key => $label)
                        <div>
                            <x-input-label :for="'maintenance_content_'.$key" :value="$label" />
                            <x-text-input :id="'maintenance_content_'.$key" :name="'maintenance_content['.$key.']'" type="text" class="mt-1 block w-full min-w-0" :value="old('maintenance_content.'.$key, $content[$key])" />
                            <x-input-error :messages="$errors->get('maintenance_content.'.$key)" class="mt-2" />
                        </div>
                    @endforeach
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @foreach (['show_spinner' => 'Show animated loader', 'show_countdown' => 'Show countdown (when return time is visible)', 'show_logo' => 'Show site logo', 'show_start' => 'Show maintenance start time', 'show_return' => 'Show expected return time', 'show_theme_toggle' => 'Show light/dark theme switch'] as $key => $label)
                            <div>
                                <input type="hidden" name="maintenance_content[{{ $key }}]" value="0">
                                <label class="flex min-h-11 items-center gap-2 text-sm text-gray-700 dark:text-gray-200"><input type="checkbox" name="maintenance_content[{{ $key }}]" value="1" @checked(old('maintenance_content.'.$key, $content[$key])) class="rounded border-gray-300 text-emerald-600">{{ $label }}</label>
                                <x-input-error :messages="$errors->get('maintenance_content.'.$key)" class="mt-2" />
                            </div>
                        @endforeach
                    </div>
                </fieldset>
                <div class="flex flex-col sm:flex-row sm:flex-wrap items-stretch sm:items-center gap-4">
                    <x-primary-button>Save maintenance settings</x-primary-button>
                    <a href="{{ route('admin.maintenance.preview') }}" target="_blank" rel="noopener noreferrer" class="inline-flex min-h-11 items-center justify-center text-sm text-emerald-600 dark:text-emerald-400 underline">Preview saved page &nearr;</a>
                </div>
            </form>
        </section>
    </div></div>
</x-app-layout>
