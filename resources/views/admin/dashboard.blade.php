@php
    $card = 'rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900';
    $eyebrow = 'font-mono text-[11px] uppercase tracking-widest text-gray-600 dark:text-gray-400';
    $firstName = \Illuminate\Support\Str::of(auth()->user()->name)->before(' ');
    $deviceTotal = max(1, $devices->sum());
@endphp
<x-app-layout>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

        {{-- 1. Header: orientation + primary actions --}}
        <header class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="{{ $eyebrow }}"><span class="text-emerald-600 dark:text-emerald-400">#</span> overview · {{ $today }}</p>
                <h1 class="mt-1 text-2xl sm:text-3xl font-semibold tracking-tight text-gray-900 dark:text-gray-50">{{ $greeting }}, {{ $firstName }}.</h1>
                <p class="mt-1 flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                    <span @class(['inline-block h-2 w-2 rounded-full', 'bg-amber-500' => $maintenance, 'bg-emerald-500 animate-pulse' => ! $maintenance])></span>
                    {{ $maintenance ? 'Site is in maintenance mode' : 'Site is live' }}
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('home') }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 dark:border-gray-700 px-3.5 py-2 text-sm font-medium text-gray-800 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800">View site <x-icon name="external" class="w-4 h-4" /></a>
                <a href="{{ route('admin.links.create') }}" class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 px-3.5 py-2 text-sm font-semibold text-white hover:bg-emerald-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-500"><x-icon name="plus" class="w-4 h-4" /> New link</a>
            </div>
        </header>

        @if (session('status'))
            <p role="status" class="rounded-2xl border border-emerald-200 dark:border-emerald-900 bg-emerald-50 dark:bg-emerald-950/40 px-4 py-3 text-sm text-emerald-900 dark:text-emerald-200">{{ session('status') }}</p>
        @endif

        {{-- 2. Attention: only what needs a decision, otherwise a calm all-clear --}}
        @if (count($attention))
            <section aria-label="Needs attention" class="{{ $card }} divide-y divide-gray-200 dark:divide-gray-800">
                @foreach ($attention as $item)
                    <a href="{{ $item['href'] }}" class="flex items-center gap-3 px-4 py-3 text-sm hover:bg-gray-50 dark:hover:bg-gray-800/60 first:rounded-t-2xl last:rounded-b-2xl">
                        <span @class(['h-2 w-2 shrink-0 rounded-full', 'bg-amber-500' => $item['tone'] === 'amber', 'bg-red-500' => $item['tone'] === 'red', 'bg-emerald-500' => $item['tone'] === 'emerald'])></span>
                        <span class="flex-1 text-gray-800 dark:text-gray-200">{{ $item['text'] }}</span>
                        <span class="inline-flex items-center gap-1 font-medium text-emerald-700 dark:text-emerald-400">{{ $item['cta'] }} <x-icon name="chevron" class="w-4 h-4" /></span>
                    </a>
                @endforeach
            </section>
        @else
            <p class="flex items-center gap-2 rounded-2xl border border-emerald-200 dark:border-emerald-900 bg-emerald-50 dark:bg-emerald-950/40 px-4 py-3 text-sm text-emerald-900 dark:text-emerald-200"><x-icon name="shield" class="w-5 h-5 shrink-0" /> All clear — no new inquiries, advisories or maintenance flags.</p>
        @endif

        {{-- 3. KPIs: last 7 days vs the 7 before --}}
        <section aria-label="Key numbers" class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <x-stat-tile label="Page views" :value="$kpis['views']['value']" :previous="$kpis['views']['previous']" :spark="$kpis['views']['spark']" :href="route('admin.analytics')" />
            <x-stat-tile label="Visitors" :value="$kpis['visitors']['value']" :previous="$kpis['visitors']['previous']" :spark="$kpis['visitors']['spark']" :href="route('admin.analytics')" hint="unique per day, summed" />
            <x-stat-tile label="Link clicks" :value="$kpis['clicks']['value']" :previous="$kpis['clicks']['previous']" :spark="$kpis['clicks']['spark']" :href="route('admin.analytics')" />
            <x-stat-tile label="New inquiries" :value="$newInquiries" :href="route('admin.contact-inquiries.index', ['status' => 'new'])" hint="unread in your inbox" />
        </section>

        {{-- 4. Traffic chart + inbox --}}
        <div class="grid gap-6 lg:grid-cols-3">
            <section class="{{ $card }} p-5 lg:col-span-2" x-data="trafficChart(@js($series))" aria-labelledby="traffic-title">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h2 id="traffic-title" class="text-base font-semibold text-gray-900 dark:text-gray-50">Traffic</h2>
                        <p class="mt-0.5 text-sm text-gray-600 dark:text-gray-400">
                            <span class="font-semibold text-gray-900 dark:text-gray-50 tabular-nums" x-text="total.toLocaleString()"></span>
                            <span x-text="metrics[metric].toLowerCase()"></span> in the last <span x-text="range"></span> days
                            <span class="ml-1 inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium"
                                  :class="{'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300': delta.dir==='up','bg-red-100 text-red-800 dark:bg-red-950 dark:text-red-300': delta.dir==='down','bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300': delta.dir==='flat'}"
                                  x-text="delta.text"></span>
                        </p>
                    </div>
                    <div class="flex flex-wrap gap-2 text-sm">
                        <div class="inline-flex rounded-lg border border-gray-300 dark:border-gray-700 p-0.5" role="group" aria-label="Metric">
                            <template x-for="(name, key) in metrics" :key="key">
                                <button type="button" @click="metric = key" :aria-pressed="metric === key" x-text="name"
                                        class="rounded-md px-2.5 py-1 font-medium transition"
                                        :class="metric === key ? 'bg-gray-900 text-white dark:bg-gray-100 dark:text-gray-900' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800'"></button>
                            </template>
                        </div>
                        <div class="inline-flex rounded-lg border border-gray-300 dark:border-gray-700 p-0.5" role="group" aria-label="Range">
                            <template x-for="r in [7, 14, 30]" :key="r">
                                <button type="button" @click="range = r; hover = null" :aria-pressed="range === r" x-text="r + 'd'"
                                        class="rounded-md px-2.5 py-1 font-medium transition"
                                        :class="range === r ? 'bg-gray-900 text-white dark:bg-gray-100 dark:text-gray-900' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800'"></button>
                            </template>
                        </div>
                    </div>
                </div>

                <div class="relative mt-4">
                    <svg viewBox="0 0 640 220" class="w-full h-auto touch-pan-y select-none" role="img" tabindex="0"
                         :aria-label="`${metrics[metric]}, last ${range} days: ${total} total`"
                         @mousemove="move($event)" @touchstart.passive="move($event.touches[0] && {currentTarget: $event.currentTarget, clientX: $event.touches[0].clientX})" @touchmove.passive="move({currentTarget: $event.currentTarget, clientX: $event.touches[0].clientX})"
                         @mouseleave="hover = null" @keydown="key($event)" @blur="hover = null">
                        {{-- Static elements bound by index: Alpine <template> does not work inside <svg>. --}}
                        @foreach ([0, 1, 2] as $k)
                            <line :x1="pad.l" :x2="w - pad.r" :y1="y(ticks[{{ $k }}])" :y2="y(ticks[{{ $k }}])" class="stroke-gray-200 dark:stroke-gray-800" stroke-width="1" />
                            <text :x="pad.l - 8" :y="y(ticks[{{ $k }}]) + 4" text-anchor="end" class="fill-gray-600 dark:fill-gray-400" font-size="11" x-text="ticks[{{ $k }}].toLocaleString()"></text>
                        @endforeach
                        <path :d="prevPath" fill="none" stroke-width="1.5" stroke-dasharray="4 4" class="stroke-gray-400 dark:stroke-gray-500" />
                        <path :d="areaPath" class="fill-emerald-500/10 dark:fill-emerald-400/10" />
                        <path :d="linePath" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="stroke-emerald-600 dark:stroke-emerald-400" />
                        @foreach (range(0, 7) as $k)
                            <text x-show="xLabels[{{ $k }}]" :text-anchor="xLabels[{{ $k }}] && xLabels[{{ $k }}].i === values.length - 1 ? 'end' : 'middle'" :x="xLabels[{{ $k }}] ? x(xLabels[{{ $k }}].i) : 0" :y="h - 6" class="fill-gray-600 dark:fill-gray-400" font-size="11" x-text="xLabels[{{ $k }}] ? xLabels[{{ $k }}].text : ''"></text>
                        @endforeach
                        <g x-show="hover !== null" x-cloak>
                            <line :x1="hover === null ? 0 : x(hover)" :x2="hover === null ? 0 : x(hover)" :y1="pad.t" :y2="h - pad.b" class="stroke-gray-400 dark:stroke-gray-500" stroke-width="1" />
                            <circle :cx="hover === null ? 0 : x(hover)" :cy="hover === null ? 0 : y(values[hover])" r="5" stroke-width="2" class="fill-emerald-600 dark:fill-emerald-400 stroke-white dark:stroke-gray-900" />
                        </g>
                    </svg>
                    <div x-show="tip" x-cloak class="pointer-events-none absolute top-0 z-10 -translate-x-1/2 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-950 px-3 py-2 text-xs shadow-lg"
                         :style="`left: ${Math.min(88, Math.max(12, tip ? tip.left : 0))}%`">
                        <p class="font-medium text-gray-900 dark:text-gray-50" x-text="tip && tip.label"></p>
                        <p class="mt-0.5 text-gray-800 dark:text-gray-200"><span class="font-semibold tabular-nums" x-text="tip && tip.value.toLocaleString()"></span> <span x-text="metrics[metric].toLowerCase()"></span></p>
                        <p class="text-gray-600 dark:text-gray-400" x-show="tip && tip.prev !== null">Previous period: <span class="tabular-nums" x-text="tip && tip.prev !== null ? tip.prev.toLocaleString() : ''"></span></p>
                    </div>
                </div>

                <div class="mt-3 flex flex-wrap items-center justify-between gap-2 text-xs text-gray-600 dark:text-gray-400">
                    <p class="flex items-center gap-4">
                        <span class="inline-flex items-center gap-1.5"><span class="h-0.5 w-4 rounded bg-emerald-600 dark:bg-emerald-400"></span> This period</span>
                        <span class="inline-flex items-center gap-1.5"><span class="w-4 border-t-2 border-dashed border-gray-400 dark:border-gray-500"></span> Previous period</span>
                    </p>
                    <p class="basis-full sm:basis-auto">Today is still in progress.</p>
                    <p x-show="peak">Peak: <span class="font-medium text-gray-800 dark:text-gray-200 tabular-nums" x-text="peak && peak.value.toLocaleString()"></span> on <span x-text="peak && peak.label"></span></p>
                </div>

                <details class="mt-3 text-sm">
                    <summary class="cursor-pointer text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">View as table</summary>
                    <div class="mt-2 max-h-56 overflow-auto rounded-lg border border-gray-200 dark:border-gray-800">
                        <table class="w-full text-left text-xs">
                            <thead class="sticky top-0 bg-gray-50 dark:bg-gray-800 text-gray-600 dark:text-gray-300"><tr><th class="p-2">Day</th><th class="p-2">Views</th><th class="p-2">Visitors</th><th class="p-2">Clicks</th></tr></thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-800 text-gray-800 dark:text-gray-200">
                                @foreach ($series->slice(-14)->reverse() as $row)
                                    <tr><td class="p-2">{{ $row['label'] }}</td><td class="p-2 tabular-nums">{{ $row['views'] }}</td><td class="p-2 tabular-nums">{{ $row['visitors'] }}</td><td class="p-2 tabular-nums">{{ $row['clicks'] }}</td></tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </details>
            </section>

            <section class="{{ $card }} flex flex-col" aria-labelledby="inbox-title">
                <div class="flex items-center justify-between p-5 pb-3">
                    <h2 id="inbox-title" class="text-base font-semibold text-gray-900 dark:text-gray-50">Inbox</h2>
                    <a href="{{ route('admin.contact-inquiries.index') }}" class="text-sm font-medium text-emerald-700 dark:text-emerald-400 hover:underline">View all</a>
                </div>
                <ul class="flex-1 divide-y divide-gray-200 dark:divide-gray-800">
                    @forelse ($inquiries as $inquiry)
                        <li>
                            <a href="{{ route('admin.contact-inquiries.show', $inquiry) }}" class="flex gap-3 px-5 py-3 hover:bg-gray-50 dark:hover:bg-gray-800/60">
                                <span @class(['mt-1.5 h-2 w-2 shrink-0 rounded-full', 'bg-emerald-500' => $inquiry->status === 'new', 'bg-gray-300 dark:bg-gray-700' => $inquiry->status !== 'new']) title="{{ $inquiry->status }}"></span>
                                <span class="min-w-0 flex-1">
                                    <span class="flex items-baseline justify-between gap-2">
                                        <span @class(['truncate text-sm', 'font-semibold text-gray-900 dark:text-gray-50' => $inquiry->status === 'new', 'text-gray-800 dark:text-gray-200' => $inquiry->status !== 'new'])>{{ $inquiry->name }}</span>
                                        <time class="shrink-0 text-xs text-gray-600 dark:text-gray-400" datetime="{{ $inquiry->created_at->toIso8601String() }}">{{ $inquiry->created_at->diffForHumans(short: true) }}</time>
                                    </span>
                                    <span class="block truncate text-sm text-gray-600 dark:text-gray-400">{{ $inquiry->subject ?: \Illuminate\Support\Str::limit($inquiry->message, 60) }}</span>
                                </span>
                            </a>
                        </li>
                    @empty
                        <li class="px-5 py-10 text-center">
                            <x-icon name="mail" class="mx-auto w-8 h-8 text-gray-400 dark:text-gray-500" />
                            <p class="mt-2 text-sm font-medium text-gray-800 dark:text-gray-200">No messages yet</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Inquiries from your contact form will appear here.</p>
                        </li>
                    @endforelse
                </ul>
            </section>
        </div>

        {{-- 5. Where visitors go and come from (30 days) --}}
        <div class="grid gap-6 md:grid-cols-3">
            <section class="{{ $card }} p-5" aria-labelledby="pages-title">
                <h2 id="pages-title" class="text-base font-semibold text-gray-900 dark:text-gray-50">Top pages</h2>
                <p class="text-xs text-gray-600 dark:text-gray-400">Last 30 days</p>
                <div class="mt-3">
                    @forelse ($topPages as $path => $count)
                        <x-analytics-bar :label="$path" :count="$count" :max="$topPages->max()" />
                    @empty
                        <p class="py-6 text-sm text-gray-600 dark:text-gray-400">No visits recorded yet.</p>
                    @endforelse
                </div>
            </section>
            <section class="{{ $card }} p-5" aria-labelledby="ref-title">
                <h2 id="ref-title" class="text-base font-semibold text-gray-900 dark:text-gray-50">Top referrers</h2>
                <p class="text-xs text-gray-600 dark:text-gray-400">Last 30 days, external only</p>
                <div class="mt-3">
                    @forelse ($topReferrers as $host => $count)
                        <x-analytics-bar :label="$host" :count="$count" :max="$topReferrers->max()" />
                    @empty
                        <p class="py-6 text-sm text-gray-600 dark:text-gray-400">No external referrers yet — most visits arrive directly.</p>
                    @endforelse
                </div>
            </section>
            <section class="{{ $card }} p-5" aria-labelledby="dev-title">
                <h2 id="dev-title" class="text-base font-semibold text-gray-900 dark:text-gray-50">Devices</h2>
                <p class="text-xs text-gray-600 dark:text-gray-400">Share of visits, last 30 days</p>
                <div class="mt-3">
                    @forelse ($devices as $device => $count)
                        <x-analytics-bar :label="ucfirst($device)" :count="$count" :display="round($count / $deviceTotal * 100).'%'" :max="$devices->max()" />
                    @empty
                        <p class="py-6 text-sm text-gray-600 dark:text-gray-400">No device data yet.</p>
                    @endforelse
                </div>
            </section>
        </div>

        {{-- 6. Calendar: public holidays (national + state) and Hijri occasions --}}
        @include('admin.dashboard-calendar')

        {{-- 7. Shortcuts + prayer times --}}
        <div class="grid gap-6 lg:grid-cols-3">
            <section class="{{ $card }} p-5 lg:col-span-2" aria-labelledby="actions-title">
                <h2 id="actions-title" class="text-base font-semibold text-gray-900 dark:text-gray-50">Manage your site</h2>
                <div class="mt-4 grid grid-cols-2 sm:grid-cols-3 gap-3">
                    @foreach ([
                        ['Links', $linkCount.' published', 'admin.links.index', 'link'],
                        ['Social links', $socialLinkCount.' profiles', 'admin.social-links.index', 'user'],
                        ['Analytics', 'Full report', 'admin.analytics', 'chart'],
                        ['Digital card', 'Profile & QR', 'admin.digital-card.edit', 'user'],
                        ['Sections & theme', 'Layout and colours', 'admin.sections.index', 'grid'],
                        ['Maintenance', $maintenance ? 'Currently ON' : 'Off', 'admin.maintenance.edit', 'shield'],
                        ['Share pages', 'Custom landing URLs', 'admin.share-pages.index', 'link'],
                        ['Checklists', $openChecklistItems.' open '.\Illuminate\Support\Str::plural('item', $openChecklistItems), 'admin.checklists.index', 'grid'],
                        ['Site settings', 'Profile, SEO, contact', 'admin.site-settings.edit', 'grid'],
                    ] as [$name, $meta, $route, $icon])
                        <a href="{{ route($route) }}" class="group flex items-center gap-3 rounded-xl border border-gray-200 dark:border-gray-800 p-3 hover:border-emerald-500/50 hover:bg-emerald-50/40 dark:hover:bg-emerald-950/20 focus-visible:outline focus-visible:outline-2 focus-visible:outline-emerald-500">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 group-hover:bg-emerald-100 dark:group-hover:bg-emerald-950 group-hover:text-emerald-700 dark:group-hover:text-emerald-400"><x-icon :name="$icon" /></span>
                            <span class="min-w-0"><span class="block truncate text-sm font-medium text-gray-900 dark:text-gray-50">{{ $name }}</span><span class="block truncate text-xs text-gray-600 dark:text-gray-400">{{ $meta }}</span></span>
                        </a>
                    @endforeach
                </div>
            </section>

            @include('admin.dashboard-prayer')
        </div>

        {{-- 8. System health & security: summary first, detail on demand --}}
        @include('admin.dashboard-status')
    </div>
</x-app-layout>
