<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
    <head>
        <meta charset="utf-8">
        @include('partials.theme-init')
        @include('partials.favicon')
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="theme-color" content="#05080a">

        @php
            $seoTitle = $seo->title ?? ($profile->display_name.($profile->tagline ? ' — '.$profile->tagline : ''));
            $seoDescription = $seo->description ?? ($profile->biography ?? $profile->tagline);
            $seoImage = $seo->og_image ?? ($profile->profile_photo ? url(\Illuminate\Support\Facades\Storage::url($profile->profile_photo)) : null);
            $seoCanonical = $seo->canonical_url ?? url('/');
            $seoRobots = $seo->robots ?? 'index,follow';
        @endphp

        <title>{{ $seoTitle }}</title>
        <meta name="description" content="{{ $seoDescription }}">
        <meta name="robots" content="{{ $seoRobots }}">
        <link rel="canonical" href="{{ $seoCanonical }}">

        <!-- Open Graph -->
        <meta property="og:title" content="{{ $seoTitle }}">
        <meta property="og:description" content="{{ $seoDescription }}">
        <meta property="og:type" content="profile">
        <meta property="og:url" content="{{ $seoCanonical }}">
        @if ($seoImage)
            <meta property="og:image" content="{{ $seoImage }}">
        @endif

        <!-- Twitter Card -->
        <meta name="twitter:card" content="{{ $seoImage ? 'summary_large_image' : 'summary' }}">
        <meta name="twitter:title" content="{{ $seoTitle }}">
        <meta name="twitter:description" content="{{ $seoDescription }}">
        @if ($seoImage)
            <meta name="twitter:image" content="{{ $seoImage }}">
        @endif

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|jetbrains-mono:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        @if ($profile->ga_tracking_id)
            <script async src="https://www.googletagmanager.com/gtag/js?id={{ $profile->ga_tracking_id }}"></script>
            <script>
                window.dataLayer = window.dataLayer || [];
                function gtag(){dataLayer.push(arguments);}
                gtag('js', new Date());
                gtag('config', '{{ $profile->ga_tracking_id }}');
            </script>
        @endif
    </head>
    <body class="bg-white dark:bg-gray-950 text-gray-900 dark:text-gray-100 font-sans antialiased">
        <x-theme-toggle class="fixed top-4 right-4 sm:top-6 sm:right-6 z-50 w-10 h-10 bg-white/80 dark:bg-gray-900/80 backdrop-blur" />

        <!-- Ambient grid backdrop -->
        <div class="pointer-events-none fixed inset-0 -z-10 hidden dark:block" aria-hidden="true">
            <div class="absolute inset-0 opacity-[0.07]" style="background-image: linear-gradient(to right, #34d399 1px, transparent 1px), linear-gradient(to bottom, #34d399 1px, transparent 1px); background-size: 44px 44px;"></div>
            <div class="absolute inset-0 bg-[radial-gradient(ellipse_60%_50%_at_50%_0%,rgba(16,185,129,0.15),transparent)]"></div>
        </div>

        <div class="min-h-screen flex flex-col items-center px-5 sm:px-6 py-14 sm:py-20 lg:py-28">
            <main class="w-full max-w-xl lg:max-w-2xl">

                @if ($sections->has('hero') && $profile->profile_visible)
                    <section class="text-center">
                        <div class="inline-flex items-center gap-1.5 rounded-full border border-emerald-500/30 bg-emerald-500/5 px-3 py-1 font-mono text-[11px] uppercase tracking-widest text-emerald-600 dark:text-emerald-400">
                            <span class="relative flex h-1.5 w-1.5">
                                <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                                <span class="relative inline-flex h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                            </span>
                            systems online
                        </div>

                        @if ($profile->profile_photo)
                            <div class="relative mx-auto mt-7 mb-6 w-28 h-28 sm:w-32 sm:h-32">
                                <div class="absolute -inset-2 rounded-2xl border border-emerald-500/20"></div>
                                <img
                                    src="{{ \Illuminate\Support\Facades\Storage::url($profile->profile_photo) }}"
                                    alt="{{ $profile->display_name }}"
                                    class="relative w-full h-full rounded-2xl object-cover ring-1 ring-black/10 dark:ring-white/10"
                                >
                                <span class="absolute -top-1 -left-1 w-3 h-3 border-t-2 border-l-2 border-emerald-500 rounded-tl"></span>
                                <span class="absolute -bottom-1 -right-1 w-3 h-3 border-b-2 border-r-2 border-emerald-500 rounded-br"></span>
                            </div>
                        @else
                            <div class="mt-7 mb-6"></div>
                        @endif

                        <h1 class="text-3xl sm:text-4xl font-bold tracking-tight">{{ $profile->display_name }}</h1>

                        @if ($profile->job_title)
                            <p class="mt-2 font-mono text-sm text-emerald-600 dark:text-emerald-400">{{ $profile->job_title }}</p>
                        @endif

                        @if ($profile->tagline)
                            <div class="mt-4 flex flex-wrap justify-center gap-2">
                                @foreach (array_filter(array_map('trim', explode('|', $profile->tagline))) as $tag)
                                    <span class="rounded-md border border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-900 px-2.5 py-1 font-mono text-xs text-gray-600 dark:text-gray-400">{{ $tag }}</span>
                                @endforeach
                            </div>
                        @endif

                        @if ($profile->location)
                            <p class="mt-4 flex items-center justify-center gap-1.5 text-xs text-gray-500 dark:text-gray-500">
                                <svg class="w-3.5 h-3.5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9.69 18.933l.003.001C9.89 19.02 10 19 10 19s.11.02.308-.066l.002-.001.006-.003.018-.008a5.741 5.741 0 00.281-.14c.186-.096.446-.24.757-.433.62-.384 1.445-.966 2.274-1.765C15.302 14.988 17 12.493 17 9A7 7 0 103 9c0 3.492 1.698 5.988 3.355 7.584a13.731 13.731 0 002.274 1.765 11.842 11.842 0 00.976.544l.062.029.018.008.006.003zM10 11.25a2.25 2.25 0 100-4.5 2.25 2.25 0 000 4.5z" clip-rule="evenodd" /></svg>
                                {{ $profile->location }}
                            </p>
                        @endif

                        @if ($profile->biography)
                            <p class="mt-6 text-sm leading-relaxed text-gray-600 dark:text-gray-400 max-w-lg mx-auto">{{ $profile->biography }}</p>
                        @endif

                        @if ($profile->primary_cta_url || $profile->secondary_cta_url)
                            <div class="mt-7 flex flex-wrap justify-center gap-3">
                                @if ($profile->primary_cta_url)
                                    <a href="{{ $profile->primary_cta_url }}" class="inline-flex items-center px-5 py-2.5 rounded-lg bg-emerald-600 text-white text-sm font-semibold hover:bg-emerald-500 hover:shadow-[0_0_20px_rgba(16,185,129,0.35)] transition">
                                        {{ $profile->primary_cta_label ?? 'Learn more' }}
                                    </a>
                                @endif
                                @if ($profile->secondary_cta_url)
                                    <a href="{{ $profile->secondary_cta_url }}" class="inline-flex items-center px-5 py-2.5 rounded-lg border border-gray-300 dark:border-gray-700 text-sm font-semibold hover:border-emerald-500/50 dark:hover:border-emerald-500/50 transition">
                                        {{ $profile->secondary_cta_label ?? 'Contact' }}
                                    </a>
                                @endif
                            </div>
                        @endif
                    </section>
                @endif

                @if ($sections->has('featured_links') && $featuredLinks->isNotEmpty())
                    <section class="mt-16">
                        <h2 class="mb-4 font-mono text-xs uppercase tracking-widest text-gray-600 dark:text-gray-400">
                            <span class="text-emerald-500">#</span> featured
                        </h2>
                        <div class="grid gap-3 sm:grid-cols-2">
                            @foreach ($featuredLinks as $link)
                                <a
                                    href="{{ route('go.link', $link) }}"
                                    @if ($link->open_in_new_tab) target="_blank" rel="noopener noreferrer" @endif
                                    class="group block rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-4 hover:border-emerald-500/40 hover:shadow-[0_0_16px_rgba(16,185,129,0.12)] transition"
                                >
                                    <div class="flex items-start justify-between gap-2">
                                        <p class="text-sm font-semibold">{{ $link->title }}</p>
                                        <svg class="w-4 h-4 shrink-0 text-gray-300 dark:text-gray-700 group-hover:text-emerald-500 transition" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.22 14.78a.75.75 0 001.06 0l7.22-7.22v5.69a.75.75 0 001.5 0v-7.5a.75.75 0 00-.75-.75h-7.5a.75.75 0 000 1.5h5.69l-7.22 7.22a.75.75 0 000 1.06z" clip-rule="evenodd" /></svg>
                                    </div>
                                    @if ($link->description)
                                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-500">{{ $link->description }}</p>
                                    @endif
                                </a>
                            @endforeach
                        </div>
                    </section>
                @endif

                @if ($sections->has('links') && $links->isNotEmpty())
                    <section class="mt-12">
                        <h2 class="mb-4 font-mono text-xs uppercase tracking-widest text-gray-600 dark:text-gray-400">
                            <span class="text-emerald-500">#</span> links
                        </h2>
                        <div class="space-y-2">
                            @foreach ($links as $link)
                                <a
                                    href="{{ route('go.link', $link) }}"
                                    @if ($link->open_in_new_tab) target="_blank" rel="noopener noreferrer" @endif
                                    class="group flex items-center justify-between rounded-lg border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 px-4 py-3 hover:border-emerald-500/40 transition"
                                >
                                    <span class="text-sm font-medium">{{ $link->title }}</span>
                                    <span class="flex items-center gap-2">
                                        @if ($link->category)
                                            <span class="font-mono text-[10px] uppercase tracking-wide text-gray-600 dark:text-gray-400">{{ $link->category->name }}</span>
                                        @endif
                                        <svg class="w-3.5 h-3.5 text-gray-300 dark:text-gray-700 group-hover:text-emerald-500 transition" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.22 14.78a.75.75 0 001.06 0l7.22-7.22v5.69a.75.75 0 001.5 0v-7.5a.75.75 0 00-.75-.75h-7.5a.75.75 0 000 1.5h5.69l-7.22 7.22a.75.75 0 000 1.06z" clip-rule="evenodd" /></svg>
                                    </span>
                                </a>
                            @endforeach
                        </div>
                    </section>
                @endif

                @if ($sections->has('social_links') && $socialLinks->isNotEmpty())
                    <section class="mt-12">
                        <h2 class="mb-4 text-center font-mono text-xs uppercase tracking-widest text-gray-600 dark:text-gray-400">
                            <span class="text-emerald-500">#</span> elsewhere
                        </h2>
                        <div class="flex flex-wrap justify-center gap-2.5">
                            @foreach ($socialLinks as $social)
                                <a
                                    href="{{ route('go.social', $social) }}"
                                    target="_blank" rel="noopener noreferrer"
                                    class="inline-flex items-center px-4 py-2 rounded-lg border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 text-sm font-medium hover:border-emerald-500/40 hover:text-emerald-600 dark:hover:text-emerald-400 transition"
                                >
                                    {{ $social->display_name ?? $social->platform }}
                                </a>
                            @endforeach
                        </div>
                    </section>
                @endif

                @if ($sections->has('contact'))
                    <section class="mt-16 text-center">
                        <div class="rounded-xl border border-dashed border-gray-300 dark:border-gray-800 px-6 py-8">
                            <h2 class="font-mono text-xs uppercase tracking-widest text-gray-600 dark:text-gray-400">
                                <span class="text-emerald-500">$</span> get_in_touch
                            </h2>
                            <a href="{{ route('contact.create') }}" class="mt-4 inline-flex items-center px-5 py-2.5 rounded-lg bg-emerald-600 text-white text-sm font-semibold hover:bg-emerald-500 hover:shadow-[0_0_20px_rgba(16,185,129,0.35)] transition">
                                Send a Message
                            </a>
                            @if ($profile->contact_notification_email)
                                <p class="mt-3">
                                    <a href="mailto:{{ $profile->contact_notification_email }}" class="text-xs text-gray-500 dark:text-gray-500 hover:text-emerald-600 dark:hover:text-emerald-400 hover:underline">
                                        {{ $profile->contact_notification_email }}
                                    </a>
                                </p>
                            @endif
                        </div>
                    </section>
                @endif

            </main>

            <footer class="mt-16 flex items-center gap-2 font-mono text-[11px] text-gray-600 dark:text-gray-400">
                <span class="inline-flex h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                system nominal &middot; &copy; {{ now()->year }} {{ $profile->full_name }}
            </footer>
        </div>
    </body>
</html>
