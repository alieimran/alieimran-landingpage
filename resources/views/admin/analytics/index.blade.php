<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Analytics') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Summary tiles --}}
            <div class="grid grid-cols-2 sm:grid-cols-5 gap-4">
                @foreach ([
                    ['label' => 'Total Views', 'value' => $summary['total']],
                    ['label' => 'Today', 'value' => $summary['today']],
                    ['label' => 'Unique Today', 'value' => $summary['unique_today']],
                    ['label' => 'Last 7 Days', 'value' => $summary['last7']],
                    ['label' => 'Last 30 Days', 'value' => $summary['last30']],
                ] as $tile)
                    <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow-sm">
                        <p class="font-mono text-[10px] uppercase tracking-widest text-gray-500 dark:text-gray-400">{{ $tile['label'] }}</p>
                        <p class="mt-1 text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $tile['value'] }}</p>
                    </div>
                @endforeach
            </div>

            {{-- Daily views chart --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                <h3 class="font-mono text-xs uppercase tracking-widest text-gray-500 dark:text-gray-400 mb-4">
                    <span class="text-emerald-500">#</span> views · last 14 days
                </h3>
                @php $max = max(1, $dailySeries->max('count')); @endphp
                <div class="flex items-end gap-1.5 sm:gap-2.5 h-36">
                    @foreach ($dailySeries as $day)
                        <div class="flex-1 flex flex-col items-center justify-end h-full group relative">
                            <div class="absolute -top-6 hidden group-hover:block font-mono text-[10px] text-gray-600 dark:text-gray-300 whitespace-nowrap">{{ $day['count'] }}</div>
                            <div
                                class="w-full rounded-t bg-emerald-500/70 hover:bg-emerald-500 transition"
                                style="height: {{ max(2, round(($day['count'] / $max) * 100)) }}%"
                            ></div>
                            <span class="mt-2 font-mono text-[9px] uppercase text-gray-400 dark:text-gray-600">{{ $day['label'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Top pages --}}
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                    <h3 class="font-mono text-xs uppercase tracking-widest text-gray-500 dark:text-gray-400 mb-4">
                        <span class="text-emerald-500">#</span> top pages · 30d
                    </h3>
                    @forelse ($topPages as $path => $count)
                        <x-analytics-bar :label="$path" :count="$count" :max="$topPages->max() ?: 1" />
                    @empty
                        <p class="text-sm text-gray-500 dark:text-gray-500">No data yet.</p>
                    @endforelse
                </div>

                {{-- Top referrers --}}
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                    <h3 class="font-mono text-xs uppercase tracking-widest text-gray-500 dark:text-gray-400 mb-4">
                        <span class="text-emerald-500">#</span> top referrers · 30d
                    </h3>
                    @forelse ($topReferrers as $host => $count)
                        <x-analytics-bar :label="$host" :count="$count" :max="$topReferrers->max() ?: 1" />
                    @empty
                        <p class="text-sm text-gray-500 dark:text-gray-500">No referrer traffic yet — visits are direct or from unknown sources.</p>
                    @endforelse
                </div>

                {{-- Browsers --}}
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                    <h3 class="font-mono text-xs uppercase tracking-widest text-gray-500 dark:text-gray-400 mb-4">
                        <span class="text-emerald-500">#</span> browsers · 30d
                    </h3>
                    @forelse ($browsers as $browser => $count)
                        <x-analytics-bar :label="$browser" :count="$count" :max="$browsers->max() ?: 1" />
                    @empty
                        <p class="text-sm text-gray-500 dark:text-gray-500">No data yet.</p>
                    @endforelse
                </div>

                {{-- Devices --}}
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                    <h3 class="font-mono text-xs uppercase tracking-widest text-gray-500 dark:text-gray-400 mb-4">
                        <span class="text-emerald-500">#</span> devices · 30d
                    </h3>
                    @forelse ($devices as $device => $count)
                        <x-analytics-bar :label="ucfirst($device)" :count="$count" :max="$devices->max() ?: 1" />
                    @empty
                        <p class="text-sm text-gray-500 dark:text-gray-500">No data yet.</p>
                    @endforelse
                </div>
            </div>

            {{-- Interactions (link/social clicks) --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                <h3 class="font-mono text-xs uppercase tracking-widest text-gray-500 dark:text-gray-400 mb-4">
                    <span class="text-emerald-500">#</span> interactions · outbound clicks · 30d
                </h3>
                @if ($topInteractions->isEmpty())
                    <p class="text-sm text-gray-500 dark:text-gray-500">No link or social clicks recorded yet.</p>
                @else
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-8">
                        @foreach ($topInteractions as $interaction)
                            <x-analytics-bar
                                :label="$interaction->label"
                                :count="$interaction->total"
                                :max="$topInteractions->max('total') ?: 1"
                                :badge="$interaction->event_type === 'social_click' ? 'social' : 'link'"
                            />
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Recent activity --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="font-mono text-xs uppercase tracking-widest text-gray-500 dark:text-gray-400">
                        <span class="text-emerald-500">#</span> recent visits
                    </h3>
                </div>
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('Page') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('Device') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('Browser') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('When') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse ($recentViews as $view)
                            <tr>
                                <td class="px-6 py-3 text-sm text-gray-900 dark:text-gray-100 font-mono">{{ $view->path }}</td>
                                <td class="px-6 py-3 text-sm text-gray-600 dark:text-gray-400">{{ ucfirst($view->device_type ?? '—') }} / {{ $view->os ?? '—' }}</td>
                                <td class="px-6 py-3 text-sm text-gray-600 dark:text-gray-400">{{ $view->browser ?? '—' }}</td>
                                <td class="px-6 py-3 text-xs text-gray-500 dark:text-gray-500">{{ $view->created_at->diffForHumans() }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-8 text-center text-sm text-gray-500 dark:text-gray-400">{{ __('No visits recorded yet.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <p class="font-mono text-[11px] text-gray-400 dark:text-gray-600">
                Privacy: no IP addresses are stored. Visitor counts use a one-way hash that rotates daily, so no individual can be tracked across days.
                @if ($retentionNote)
                    Data collected since {{ \Illuminate\Support\Carbon::parse($retentionNote)->format('Y-m-d') }}.
                @endif
            </p>
        </div>
    </div>
</x-app-layout>
