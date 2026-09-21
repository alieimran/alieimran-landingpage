<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ $profile->display_name }}{{ $profile->tagline ? ' — '.$profile->tagline : '' }}</title>
        <meta name="description" content="{{ $profile->biography ?? $profile->tagline }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

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
    <body class="bg-[#FDFDFC] dark:bg-[#0a0a0a] text-[#1b1b18] dark:text-[#EDEDEC] font-sans antialiased">
        <div class="min-h-screen flex flex-col items-center px-6 py-16 sm:py-24">
            <main class="w-full max-w-2xl">

                @if ($sections->has('hero') && $profile->profile_visible)
                    <section class="text-center">
                        @if ($profile->profile_photo)
                            <img
                                src="{{ \Illuminate\Support\Facades\Storage::url($profile->profile_photo) }}"
                                alt="{{ $profile->display_name }}"
                                class="w-28 h-28 rounded-full object-cover mx-auto mb-6 ring-1 ring-black/10 dark:ring-white/10"
                            >
                        @endif

                        <h1 class="text-2xl sm:text-3xl font-semibold tracking-tight">{{ $profile->display_name }}</h1>

                        @if ($profile->job_title)
                            <p class="mt-1 text-sm sm:text-base text-[#706f6c] dark:text-[#A1A09A]">{{ $profile->job_title }}</p>
                        @endif

                        @if ($profile->tagline)
                            <p class="mt-2 text-sm text-[#706f6c] dark:text-[#A1A09A]">{{ $profile->tagline }}</p>
                        @endif

                        @if ($profile->location)
                            <p class="mt-1 text-xs text-[#706f6c] dark:text-[#A1A09A]">{{ $profile->location }}</p>
                        @endif

                        @if ($profile->biography)
                            <p class="mt-6 text-sm leading-relaxed text-[#3E3E3A] dark:text-[#C7C6C2] max-w-xl mx-auto">{{ $profile->biography }}</p>
                        @endif

                        @if ($profile->primary_cta_url || $profile->secondary_cta_url)
                            <div class="mt-6 flex flex-wrap justify-center gap-3">
                                @if ($profile->primary_cta_url)
                                    <a href="{{ $profile->primary_cta_url }}" class="inline-flex items-center px-5 py-2 rounded-md bg-[#1b1b18] dark:bg-[#EDEDEC] text-white dark:text-[#1b1b18] text-sm font-medium hover:opacity-90 transition">
                                        {{ $profile->primary_cta_label ?? 'Learn more' }}
                                    </a>
                                @endif
                                @if ($profile->secondary_cta_url)
                                    <a href="{{ $profile->secondary_cta_url }}" class="inline-flex items-center px-5 py-2 rounded-md border border-[#19140035] dark:border-[#3E3E3A] text-sm font-medium hover:border-[#1915014a] dark:hover:border-[#62605b] transition">
                                        {{ $profile->secondary_cta_label ?? 'Contact' }}
                                    </a>
                                @endif
                            </div>
                        @endif
                    </section>
                @endif

                @if ($sections->has('featured_links') && $featuredLinks->isNotEmpty())
                    <section class="mt-14">
                        <h2 class="text-xs font-semibold uppercase tracking-widest text-[#706f6c] dark:text-[#A1A09A] mb-4">Featured</h2>
                        <div class="grid gap-3 sm:grid-cols-2">
                            @foreach ($featuredLinks as $link)
                                <a
                                    href="{{ $link->url }}"
                                    @if ($link->open_in_new_tab) target="_blank" rel="noopener noreferrer" @endif
                                    class="block rounded-lg border border-[#19140035] dark:border-[#3E3E3A] p-4 hover:border-[#1915014a] dark:hover:border-[#62605b] transition"
                                >
                                    <p class="text-sm font-medium">{{ $link->title }}</p>
                                    @if ($link->description)
                                        <p class="mt-1 text-xs text-[#706f6c] dark:text-[#A1A09A]">{{ $link->description }}</p>
                                    @endif
                                </a>
                            @endforeach
                        </div>
                    </section>
                @endif

                @if ($sections->has('links') && $links->isNotEmpty())
                    <section class="mt-14">
                        <h2 class="text-xs font-semibold uppercase tracking-widest text-[#706f6c] dark:text-[#A1A09A] mb-4">Links</h2>
                        <div class="space-y-2">
                            @foreach ($links as $link)
                                <a
                                    href="{{ $link->url }}"
                                    @if ($link->open_in_new_tab) target="_blank" rel="noopener noreferrer" @endif
                                    class="flex items-center justify-between rounded-lg border border-[#19140035] dark:border-[#3E3E3A] px-4 py-3 hover:border-[#1915014a] dark:hover:border-[#62605b] transition"
                                >
                                    <span class="text-sm font-medium">{{ $link->title }}</span>
                                    <span class="text-xs text-[#706f6c] dark:text-[#A1A09A]">{{ $link->category?->name }}</span>
                                </a>
                            @endforeach
                        </div>
                    </section>
                @endif

                @if ($sections->has('social_links') && $socialLinks->isNotEmpty())
                    <section class="mt-14">
                        <h2 class="text-xs font-semibold uppercase tracking-widest text-[#706f6c] dark:text-[#A1A09A] mb-4 text-center">Elsewhere</h2>
                        <div class="flex flex-wrap justify-center gap-3">
                            @foreach ($socialLinks as $social)
                                <a
                                    href="{{ $social->url }}"
                                    target="_blank" rel="noopener noreferrer"
                                    class="inline-flex items-center px-4 py-2 rounded-md border border-[#19140035] dark:border-[#3E3E3A] text-sm hover:border-[#1915014a] dark:hover:border-[#62605b] transition"
                                >
                                    {{ $social->display_name ?? $social->platform }}
                                </a>
                            @endforeach
                        </div>
                    </section>
                @endif

                @if ($sections->has('contact') && $profile->contact_notification_email)
                    <section class="mt-14 text-center">
                        <h2 class="text-xs font-semibold uppercase tracking-widest text-[#706f6c] dark:text-[#A1A09A] mb-4">Get in touch</h2>
                        <a href="mailto:{{ $profile->contact_notification_email }}" class="text-sm underline decoration-[#19140035] dark:decoration-[#3E3E3A] hover:decoration-current">
                            {{ $profile->contact_notification_email }}
                        </a>
                    </section>
                @endif

            </main>

            <footer class="mt-20 text-xs text-[#706f6c] dark:text-[#A1A09A]">
                &copy; {{ now()->year }} {{ $profile->full_name }}
            </footer>
        </div>
    </body>
</html>
