@props(['code', 'title', 'message'])

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        @include('partials.theme-init')
        @include('partials.favicon')
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="robots" content="noindex">
        <title>{{ $code }} — {{ $title }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|jetbrains-mono:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-white dark:bg-gray-950 text-gray-900 dark:text-gray-100 font-sans antialiased">
        <div class="pointer-events-none fixed inset-0 -z-10 hidden dark:block" aria-hidden="true">
            <div class="absolute inset-0 opacity-[0.07]" style="background-image: linear-gradient(to right, #34d399 1px, transparent 1px), linear-gradient(to bottom, #34d399 1px, transparent 1px); background-size: 44px 44px;"></div>
        </div>

        <div class="min-h-screen flex flex-col items-center justify-center px-5 text-center">
            <p class="font-mono text-xs uppercase tracking-widest text-emerald-600 dark:text-emerald-400">
                <span class="text-emerald-500">$</span> error {{ $code }}
            </p>
            <h1 class="mt-4 text-5xl sm:text-6xl font-bold tracking-tight">{{ $code }}</h1>
            <p class="mt-3 text-lg font-medium">{{ $title }}</p>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400 max-w-sm">{{ $message }}</p>

            <a href="{{ route('home') }}" class="mt-8 inline-flex items-center px-5 py-2.5 rounded-lg bg-emerald-600 text-white text-sm font-semibold hover:bg-emerald-500 transition">
                Back to home
            </a>
        </div>
    </body>
</html>
