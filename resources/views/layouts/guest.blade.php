<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        @include('partials.theme-init')
        @include('partials.favicon')
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }} · Admin</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|jetbrains-mono:500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="h-0.5 bg-gradient-to-r from-emerald-500 via-cyan-400 to-emerald-500"></div>
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100 dark:bg-gray-950">
            <div class="w-full sm:max-w-md flex items-center justify-between px-6 sm:px-0">
                <a href="/" class="flex items-center gap-2">
                    <x-application-logo class="w-12 h-12 text-gray-800 dark:text-emerald-400" />
                    <span class="font-mono text-xs uppercase tracking-widest text-gray-500 dark:text-gray-400">admin console</span>
                </a>
                <x-theme-toggle class="w-9 h-9" />
            </div>

            <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white dark:bg-gray-900 dark:border dark:border-gray-800 shadow-md overflow-hidden sm:rounded-lg">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
