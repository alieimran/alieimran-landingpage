@php
    $images = $page->displayImages();
    $cover = $images[0] ?? null;
    $description = $page->description ?: 'Tap to view.';
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        @include('partials.theme-init')
        @include('partials.favicon')
        @include('partials.theme-overrides')
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="theme-color" content="#05080a">
        <title>{{ $page->title }}</title>
        <meta name="description" content="{{ $description }}">
        <meta name="robots" content="noindex">

        {{-- So the link unfurls with a photo when shared on WhatsApp etc. --}}
        <meta property="og:title" content="{{ $page->title }}">
        <meta property="og:description" content="{{ $description }}">
        <meta property="og:type" content="website">
        <meta property="og:url" content="{{ route('share-page.show', $page) }}">
        @if ($cover)
            <meta property="og:image" content="{{ url($cover) }}">
            <meta name="twitter:card" content="summary_large_image">
            <meta name="twitter:image" content="{{ url($cover) }}">
        @endif

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|jetbrains-mono:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-white dark:bg-gray-950 text-gray-900 dark:text-gray-100 font-sans antialiased">
        <x-theme-toggle class="fixed top-4 right-4 sm:top-6 sm:right-6 z-50 w-10 h-10 bg-white/80 dark:bg-gray-900/80 backdrop-blur" />

        <div class="min-h-screen flex flex-col items-center justify-center px-5 py-14">
            <div class="w-full max-w-lg rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-sm overflow-hidden">
                <div class="h-1.5 bg-gradient-to-r from-emerald-500 via-cyan-400 to-emerald-500"></div>

                @if ($cover)
                    <a href="{{ route('go.share-page', $page) }}" target="_blank" rel="noopener noreferrer" class="block bg-gray-100 dark:bg-gray-800">
                        <img src="{{ $cover }}" alt="{{ $page->title }}" class="w-full aspect-[4/3] object-cover" referrerpolicy="no-referrer">
                    </a>
                @endif

                @if (count($images) > 1)
                    <div class="grid grid-cols-4 gap-1 p-1 bg-gray-100 dark:bg-gray-800">
                        @foreach (array_slice($images, 1) as $image)
                            <a href="{{ route('go.share-page', $page) }}" target="_blank" rel="noopener noreferrer">
                                <img src="{{ $image }}" alt="" loading="lazy" class="w-full aspect-square object-cover rounded-sm">
                            </a>
                        @endforeach
                    </div>
                @endif

                <div class="p-6 text-center">
                    <p class="font-mono text-[10px] uppercase tracking-widest text-emerald-600 dark:text-emerald-400">
                        <span class="text-emerald-500">$</span> {{ $page->slug }}
                    </p>
                    <h1 class="mt-3 text-xl font-bold tracking-tight">{{ $page->title }}</h1>
                    @if ($page->description)
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400 whitespace-pre-line">{{ $page->description }}</p>
                    @endif

                    <a href="{{ route('go.share-page', $page) }}" target="_blank" rel="noopener noreferrer"
                       class="mt-6 inline-flex w-full items-center justify-center gap-2 rounded-lg bg-emerald-600 px-5 py-3 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500 transition">
                        {{ $page->button_label ?: 'View full album' }}
                        <span aria-hidden="true">&rarr;</span>
                    </a>
                </div>
            </div>

            <a href="{{ route('home') }}" class="mt-6 font-mono text-xs uppercase tracking-widest text-gray-500 dark:text-gray-500 hover:text-emerald-600 dark:hover:text-emerald-400 transition">
                &larr; {{ parse_url(config('app.url'), PHP_URL_HOST) ?: 'back to site' }}
            </a>
        </div>
    </body>
</html>
