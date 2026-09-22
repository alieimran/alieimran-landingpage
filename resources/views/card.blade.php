<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        @include('partials.theme-init')
        @include('partials.favicon')
        @include('partials.theme-overrides')
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="theme-color" content="#05080a">
        <title>{{ $card->name }} — Digital Business Card</title>
        <meta name="robots" content="noindex">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|jetbrains-mono:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-white dark:bg-gray-950 text-gray-900 dark:text-gray-100 font-sans antialiased">
        <x-theme-toggle class="fixed top-4 right-4 sm:top-6 sm:right-6 z-50 w-10 h-10 bg-white/80 dark:bg-gray-900/80 backdrop-blur" />

        <div class="min-h-screen flex flex-col items-center justify-center px-5 py-14">
            <div class="w-full max-w-sm rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-sm overflow-hidden">
                <div class="h-1.5 bg-gradient-to-r from-emerald-500 via-cyan-400 to-emerald-500"></div>

                <div class="p-6 text-center">
                    <p class="font-mono text-[10px] uppercase tracking-widest text-emerald-600 dark:text-emerald-400">
                        <span class="text-emerald-500">$</span> digital_card
                    </p>
                    <h1 class="mt-3 text-xl font-bold tracking-tight">{{ $card->name }}</h1>
                    @if ($card->job_title)
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ $card->job_title }}</p>
                    @endif

                    <div class="mt-5 flex justify-center">
                        <img src="{{ $qrDataUri }}" alt="QR code linking to this card" class="rounded-lg border border-gray-200 dark:border-gray-800" width="220" height="220">
                    </div>

                    <dl class="mt-6 space-y-3 text-left text-sm">
                        @if ($card->email)
                            <div class="flex items-center gap-2">
                                <dt class="w-16 shrink-0 font-mono text-[10px] uppercase text-gray-400 dark:text-gray-600">Email</dt>
                                <dd><a href="mailto:{{ $card->email }}" class="text-emerald-600 dark:text-emerald-400 hover:underline">{{ $card->email }}</a></dd>
                            </div>
                        @endif
                        @if ($card->phone)
                            <div class="flex items-center gap-2">
                                <dt class="w-16 shrink-0 font-mono text-[10px] uppercase text-gray-400 dark:text-gray-600">Phone</dt>
                                <dd><a href="tel:{{ $card->phone }}" class="text-emerald-600 dark:text-emerald-400 hover:underline">{{ $card->phone }}</a></dd>
                            </div>
                        @endif
                        @if ($card->website)
                            <div class="flex items-center gap-2">
                                <dt class="w-16 shrink-0 font-mono text-[10px] uppercase text-gray-400 dark:text-gray-600">Web</dt>
                                <dd><a href="{{ $card->website }}" target="_blank" rel="noopener noreferrer" class="text-emerald-600 dark:text-emerald-400 hover:underline truncate block">{{ $card->website }}</a></dd>
                            </div>
                        @endif
                        @if ($card->linkedin_url)
                            <div class="flex items-center gap-2">
                                <dt class="w-16 shrink-0 font-mono text-[10px] uppercase text-gray-400 dark:text-gray-600">LinkedIn</dt>
                                <dd><a href="{{ $card->linkedin_url }}" target="_blank" rel="noopener noreferrer" class="text-emerald-600 dark:text-emerald-400 hover:underline truncate block">{{ $card->linkedin_url }}</a></dd>
                            </div>
                        @endif
                        @if ($card->github_url)
                            <div class="flex items-center gap-2">
                                <dt class="w-16 shrink-0 font-mono text-[10px] uppercase text-gray-400 dark:text-gray-600">GitHub</dt>
                                <dd><a href="{{ $card->github_url }}" target="_blank" rel="noopener noreferrer" class="text-emerald-600 dark:text-emerald-400 hover:underline truncate block">{{ $card->github_url }}</a></dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>

            <a href="{{ route('home') }}" class="mt-6 font-mono text-xs uppercase tracking-widest text-gray-500 dark:text-gray-500 hover:text-emerald-600 dark:hover:text-emerald-400 transition">
                &larr; back to site
            </a>
        </div>
    </body>
</html>
