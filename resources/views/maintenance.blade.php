@php $content = array_replace(config('maintenance.content'), $setting->maintenance_content ?? []); @endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    @include('partials.theme-init')
    @include('partials.favicon')
    @include('partials.theme-overrides')
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#05080a">
    <title>Maintenance &middot; {{ $setting->display_name ?: config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|jetbrains-mono:400,500,600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-white dark:bg-gray-950 text-gray-900 dark:text-gray-100 font-sans antialiased">
    <div class="pointer-events-none fixed inset-0 -z-10 hidden dark:block" aria-hidden="true">
        <div class="absolute inset-0 opacity-[0.07]" style="background-image: linear-gradient(to right, #34d399 1px, transparent 1px), linear-gradient(to bottom, #34d399 1px, transparent 1px); background-size: 44px 44px;"></div>
        <div class="absolute inset-0 bg-[radial-gradient(ellipse_60%_50%_at_50%_0%,rgba(16,185,129,0.15),transparent)]"></div>
    </div>
    <main class="min-h-screen flex items-center justify-center px-5 py-12">
        <div class="w-full max-w-xl">
            <div class="flex items-center justify-between gap-4">
                <div class="flex min-w-0 items-center gap-3">@if ($content['show_logo'])<x-site-logo class="h-10 w-10 shrink-0" />@endif<span class="min-w-0 break-words text-sm font-semibold">{{ $setting->display_name ?: config('app.name') }}</span></div>
                @if ($content['show_theme_toggle'])<x-theme-toggle class="w-11 h-11 shrink-0" />@endif
            </div>
            <section class="mt-8 rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-6 sm:p-10 shadow-sm">
                @if ($content['show_spinner'])
                    <div class="mb-5 inline-flex h-12 w-12 items-center justify-center rounded-full bg-emerald-500/10 text-emerald-600 dark:text-emerald-400" aria-hidden="true">
                        <svg class="h-6 w-6 animate-spin motion-reduce:animate-none" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2" opacity="0.2" /><path d="M12 3a9 9 0 019 9" stroke="currentColor" stroke-width="2" stroke-linecap="round" /></svg>
                    </div>
                @endif
                <p class="break-words font-mono text-xs uppercase tracking-widest text-emerald-600 dark:text-emerald-400">{{ $content['label'] }}</p>
                <h1 class="mt-5 break-words text-3xl sm:text-4xl font-bold tracking-tight">{{ $content['heading'] }}</h1>
                <p class="mt-4 whitespace-pre-line break-words text-sm leading-relaxed text-gray-600 dark:text-gray-400">{{ $setting->maintenance_reason ?: 'We are making a few improvements. Thank you for your patience.' }}</p>
                @if ($content['show_start'] || $content['show_return'])
                <dl class="mt-8 space-y-5 border-t border-gray-200 dark:border-gray-800 pt-6">
                    @if ($content['show_start'] && $setting->maintenance_started_at)
                        <div><dt class="font-mono text-xs uppercase tracking-widest text-gray-500 dark:text-gray-400">Maintenance started</dt><dd class="mt-2 text-sm"><time datetime="{{ $setting->maintenance_started_at->toIso8601String() }}">{{ $setting->maintenance_started_at->timezone('Asia/Kuala_Lumpur')->format('d M Y, h:i A') }}</time> MYT</dd></div>
                    @endif
                    @if ($content['show_return'])
                        <div><dt class="font-mono text-xs uppercase tracking-widest text-gray-500 dark:text-gray-400">{{ $setting->maintenance_until?->isFuture() ? 'Expected back online' : 'Status update' }}</dt>
                            <dd class="mt-2 text-sm">
                                @if ($setting->maintenance_until?->isFuture())
                                    <time datetime="{{ $setting->maintenance_until->toIso8601String() }}">{{ $setting->maintenance_until->timezone('Asia/Kuala_Lumpur')->format('d M Y, h:i A') }}</time> MYT
                                @else
                                    {{ $content['delayed_message'] }}
                                @endif
                            </dd>
                        </div>
                        @if ($content['show_countdown'] && $setting->maintenance_until?->isFuture())
                            <div data-maintenance-countdown data-until="{{ $setting->maintenance_until->toIso8601String() }}" data-server-now="{{ now()->toIso8601String() }}" data-expired-message="{{ $content['delayed_message'] }}" class="rounded-xl bg-gray-50 dark:bg-gray-950 p-4">
                                <dt class="text-xs text-gray-500 dark:text-gray-400">Time until estimated return</dt>
                                <dd data-countdown-display role="timer" aria-live="off" class="mt-2 break-words font-mono text-2xl sm:text-3xl font-semibold text-emerald-600 dark:text-emerald-400">{{ max(1, (int) now()->diffInMinutes($setting->maintenance_until)) }} minutes remaining</dd>
                                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Estimated time only. The site reopens when maintenance is complete.</p>
                            </div>
                        @endif
                    @endif
                </dl>
                @endif
                <a href="{{ route('home') }}" class="mt-8 inline-flex min-h-11 max-w-full items-center justify-center break-words rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-emerald-500 transition">{{ $content['button_text'] }}</a>
            </section>
            <p class="mt-6 break-words text-center font-mono text-xs text-gray-500 dark:text-gray-400">{{ $content['footer'] }}</p>
        </div>
    </main>
</body>
</html>
