<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
    <head>
        <meta charset="utf-8">
        @include('partials.theme-init')
        @include('partials.favicon')
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="theme-color" content="#05080a">

        <title>Contact — {{ $profile->display_name }}</title>
        <meta name="description" content="Get in touch with {{ $profile->display_name }}.">
        <meta name="robots" content="noindex">
        <link rel="canonical" href="{{ url('/contact') }}">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|jetbrains-mono:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-white dark:bg-gray-950 text-gray-900 dark:text-gray-100 font-sans antialiased">
        <div class="pointer-events-none fixed inset-0 -z-10 hidden dark:block" aria-hidden="true">
            <div class="absolute inset-0 opacity-[0.07]" style="background-image: linear-gradient(to right, #34d399 1px, transparent 1px), linear-gradient(to bottom, #34d399 1px, transparent 1px); background-size: 44px 44px;"></div>
            <div class="absolute inset-0 bg-[radial-gradient(ellipse_60%_50%_at_50%_0%,rgba(16,185,129,0.15),transparent)]"></div>
        </div>

        <div class="min-h-screen flex flex-col items-center px-5 sm:px-6 py-14 sm:py-20">
            <main class="w-full max-w-lg">
                <div class="flex items-center justify-between">
                    <a href="{{ route('home') }}" class="inline-flex items-center gap-1.5 font-mono text-xs uppercase tracking-widest text-gray-600 dark:text-gray-400 hover:text-emerald-600 dark:hover:text-emerald-400 transition">
                        &larr; back
                    </a>
                    <x-theme-toggle class="w-9 h-9" />
                </div>

                <h1 class="mt-6 text-2xl sm:text-3xl font-bold tracking-tight">Get in touch</h1>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                    Have a question, opportunity, or just want to say hi? Fill in the form below.
                </p>

                @if (session('status') === 'sent')
                    <div class="mt-6 rounded-lg border border-emerald-500/30 bg-emerald-500/5 px-4 py-3 text-sm text-emerald-700 dark:text-emerald-400">
                        <span class="font-mono">$</span> Message sent. Thanks for reaching out — I'll get back to you soon.
                    </div>
                @else
                    <form method="POST" action="{{ route('contact.store') }}" class="mt-8 space-y-5">
                        @csrf

                        {{-- Honeypot: visually hidden (not display:none) so it stays in the DOM for
                             bots that fill every field, but off-screen for sighted users and
                             announced to screen readers so they know to leave it blank. --}}
                        <div class="sr-only">
                            <label for="website">Leave this field empty</label>
                            <input type="text" name="website" id="website" tabindex="-1" autocomplete="off">
                        </div>

                        <div>
                            <x-input-label for="name" value="Name" />
                            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name')" required />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="email" value="Email" />
                            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email')" required />
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="phone" value="Phone (optional)" />
                            <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full" :value="old('phone')" />
                            <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="category" value="Category" />
                            <select id="category" name="category" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                                @foreach ($categories as $value => $label)
                                    <option value="{{ $value }}" @selected(old('category') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('category')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="subject" value="Subject" />
                            <x-text-input id="subject" name="subject" type="text" class="mt-1 block w-full" :value="old('subject')" required />
                            <x-input-error :messages="$errors->get('subject')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="message" value="Message" />
                            <textarea id="message" name="message" rows="5" required class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500">{{ old('message') }}</textarea>
                            <x-input-error :messages="$errors->get('message')" class="mt-2" />
                        </div>

                        <button type="submit" class="w-full inline-flex justify-center items-center px-5 py-2.5 rounded-lg bg-emerald-600 text-white text-sm font-semibold hover:bg-emerald-500 hover:shadow-[0_0_20px_rgba(16,185,129,0.35)] transition">
                            Send Message
                        </button>
                    </form>
                @endif
            </main>

            <footer class="mt-16 flex items-center gap-2 font-mono text-[11px] text-gray-600 dark:text-gray-400">
                <span class="inline-flex h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                system nominal &middot; &copy; {{ now()->year }} {{ $profile->full_name }}
            </footer>
        </div>
    </body>
</html>
