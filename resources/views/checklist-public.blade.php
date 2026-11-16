<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        @include('partials.theme-init')
        @include('partials.favicon')
        @include('partials.theme-overrides')
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="#05080a">
        <title>{{ $checklist->title }}</title>
        <meta name="description" content="{{ $checklist->description ?: 'A shared checklist.' }}">
        <meta name="robots" content="noindex, nofollow">
        <meta property="og:title" content="{{ $checklist->title }}">
        <meta property="og:description" content="{{ $progress['done'] }} of {{ $progress['total'] }} done">
        <meta property="og:type" content="website">
        <meta property="og:url" content="{{ $checklist->publicUrl() }}">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|jetbrains-mono:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-gray-100 dark:bg-gray-950 text-gray-900 dark:text-gray-100 font-sans antialiased">
        <x-theme-toggle class="fixed top-4 right-4 sm:top-6 sm:right-6 z-50 w-10 h-10 bg-white/80 dark:bg-gray-900/80 backdrop-blur" />

        <main class="min-h-screen flex flex-col items-center px-4 py-12 sm:py-16">
            <article class="w-full max-w-xl rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-sm overflow-hidden" aria-labelledby="title">
                <div class="h-1.5 bg-gradient-to-r from-emerald-500 via-cyan-400 to-emerald-500"></div>

                <header class="p-6 pb-4">
                    <p class="font-mono text-[11px] uppercase tracking-widest text-gray-600 dark:text-gray-400"><span class="text-emerald-600 dark:text-emerald-400">#</span> checklist</p>
                    <h1 id="title" class="mt-1 text-2xl font-semibold tracking-tight">{{ $checklist->title }}</h1>
                    @if ($checklist->description)<p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ $checklist->description }}</p>@endif

                    <div class="mt-4 flex items-center gap-3">
                        <div data-progress-bar class="h-2 flex-1 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800" role="progressbar" aria-valuenow="{{ $progress['percent'] }}" aria-valuemin="0" aria-valuemax="100" aria-label="Progress">
                            <div data-progress-fill class="h-full rounded-full bg-emerald-500 transition-all duration-300" style="width: {{ $progress['percent'] }}%"></div>
                        </div>
                        <span data-progress-text @class(['rounded-full px-2.5 py-0.5 text-sm font-medium tabular-nums', 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' => $progress['total'] && $progress['done'] === $progress['total'], 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300' => ! ($progress['total'] && $progress['done'] === $progress['total'])])>{{ $progress['done'] }}/{{ $progress['total'] }}</span>
                    </div>
                </header>

                <ul class="divide-y divide-gray-200 dark:divide-gray-800 border-t border-gray-200 dark:border-gray-800">
                    @forelse ($checklist->items as $item)
                        <li class="flex items-start gap-3 px-6 py-3">
                            @if ($checklist->visitorsCanTick())
                                <form method="POST" action="{{ route('checklists.public.toggle', [$checklist->slug, $item->id]) }}" data-checklist-toggle>
                                    @csrf @method('PATCH')
                                    <button type="submit" role="checkbox" data-title="{{ $item->title }}" aria-checked="{{ $item->is_done ? 'true' : 'false' }}" aria-label="{{ $item->is_done ? 'Mark not done' : 'Mark done' }}: {{ $item->title }}"
                                            @class(['mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-md border transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-500', 'border-emerald-600 bg-emerald-600 text-white' => $item->is_done, 'border-gray-400 dark:border-gray-600 hover:border-emerald-500' => ! $item->is_done])>
                                        @if ($item->is_done)<svg viewBox="0 0 20 20" class="h-3.5 w-3.5" fill="currentColor"><path fill-rule="evenodd" d="M16.7 5.3a1 1 0 010 1.4l-7.5 7.5a1 1 0 01-1.4 0L3.3 9.7a1 1 0 011.4-1.4l3.8 3.8 6.8-6.8a1 1 0 011.4 0z" clip-rule="evenodd"/></svg>@endif
                                    </button>
                                </form>
                            @else
                                <span @class(['mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-md border', 'border-emerald-600 bg-emerald-600 text-white' => $item->is_done, 'border-gray-400 dark:border-gray-600' => ! $item->is_done]) aria-hidden="true">
                                    @if ($item->is_done)<svg viewBox="0 0 20 20" class="h-3.5 w-3.5" fill="currentColor"><path fill-rule="evenodd" d="M16.7 5.3a1 1 0 010 1.4l-7.5 7.5a1 1 0 01-1.4 0L3.3 9.7a1 1 0 011.4-1.4l3.8 3.8 6.8-6.8a1 1 0 011.4 0z" clip-rule="evenodd"/></svg>@endif
                                </span>
                            @endif
                            <div class="min-w-0 flex-1 text-sm">
                                <p data-item-title @class(['break-words', 'text-gray-900 dark:text-gray-100' => ! $item->is_done, 'text-gray-500 dark:text-gray-500 line-through' => $item->is_done])>
                                    @unless ($checklist->visitorsCanTick())<span class="sr-only">{{ $item->is_done ? 'Done: ' : 'To do: ' }}</span>@endunless
                                    @if ($item->url)
                                        <a href="{{ $item->url }}" target="_blank" rel="noopener noreferrer nofollow" class="font-medium text-emerald-700 dark:text-emerald-400 hover:underline">{{ $item->title }}<x-icon name="external" class="ml-1 inline w-3.5 h-3.5 align-[-2px]" /><span class="sr-only"> (opens in a new tab)</span></a>
                                    @else
                                        {{ $item->title }}
                                    @endif
                                </p>
                                @if ($item->note)<p class="mt-0.5 text-xs text-gray-600 dark:text-gray-400">{{ $item->note }}</p>@endif
                            </div>
                        </li>
                    @empty
                        <li class="px-6 py-8 text-sm text-gray-600 dark:text-gray-400">This checklist has no items yet.</li>
                    @endforelse
                </ul>

                <footer class="border-t border-gray-200 dark:border-gray-800 px-6 py-3 text-xs text-gray-600 dark:text-gray-400">
                    {{ $checklist->visitorsCanTick() ? 'Tap a box to tick it off · saved automatically' : 'Read-only' }} · updated {{ $checklist->items->max('updated_at')?->diffForHumans() ?? $checklist->updated_at->diffForHumans() }}
                </footer>
            </article>
        </main>
    </body>
</html>
