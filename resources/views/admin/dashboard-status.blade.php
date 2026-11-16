@php
    $findings = count($advisories['findings']);
    $advisoryState = $findings ? 'red' : ($advisories['complete'] ? 'green' : 'amber');
    $pill = fn (string $tone) => match ($tone) {
        'green' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300',
        'amber' => 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300',
        'red' => 'bg-red-100 text-red-800 dark:bg-red-950 dark:text-red-300',
        default => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
    };
@endphp
<section id="system" class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 scroll-mt-6" aria-labelledby="system-title">
    <div class="flex flex-wrap items-center justify-between gap-3 p-5">
        <div>
            <h2 id="system-title" class="text-base font-semibold text-gray-900 dark:text-gray-50">System &amp; security</h2>
            <p class="text-xs text-gray-600 dark:text-gray-400">Versions, support lifecycle and known dependency advisories</p>
        </div>
        <div class="flex flex-wrap gap-2 text-xs font-medium">
            <span class="rounded-full px-2.5 py-1 {{ $pill($needsAttention ? 'amber' : 'green') }}">{{ $needsAttention ? $needsAttention.' to review' : 'Components up to date' }}</span>
            <span class="rounded-full px-2.5 py-1 {{ $pill($advisoryState) }}">
                @if ($findings){{ $findings }} advisory {{ str('match')->plural($findings) }}
                @elseif ($advisories['complete'])No known advisories
                @else Advisory check unavailable @endif
            </span>
        </div>
    </div>

    <details class="border-t border-gray-200 dark:border-gray-800" @if ($needsAttention || $findings) open @endif>
        <summary class="cursor-pointer px-5 py-3 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800/60 rounded-b-2xl">System versions &amp; support</summary>
        <div class="px-5 pb-5">
            <p class="text-xs text-gray-600 dark:text-gray-400">Green means the latest patch in a maintained release branch, not a guarantee against CVEs. Lifecycle checks use endoflife.date and are cached for 6 hours. Unknown means the version or reference could not be verified.</p>
            <div class="mt-4 overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="text-xs uppercase text-gray-600 dark:text-gray-400"><tr><th class="p-3">Component</th><th class="p-3">Installed</th><th class="p-3">Latest in branch</th><th class="p-3">Status</th><th class="p-3">Support / checked (MYT)</th></tr></thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800 text-gray-800 dark:text-gray-200">
                        @foreach ($components as $component)
                            <tr>
                                <td class="p-3"><a href="{{ $component['advisories'] }}" target="_blank" rel="noopener noreferrer" class="font-semibold underline">{{ $component['name'] }} &nearr;</a><p class="mt-1 text-xs text-gray-600 dark:text-gray-400">{{ $component['scope'] }}</p></td>
                                <td class="p-3 font-mono">{{ $component['version'] ?? 'Not detected' }}</td>
                                <td class="p-3 font-mono">{{ $component['latest'] ?? '—' }}</td>
                                <td class="p-3"><span class="inline-flex whitespace-nowrap rounded-full px-3 py-1 text-xs font-medium {{ $pill($component['tone']) }}">{{ $component['status'] }}</span></td>
                                <td class="p-3 text-xs">Support ends: {{ $component['eol'] ?? 'Unknown' }}<br>Checked: {{ $component['checked_at'] ?? 'Unavailable' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <p class="mt-4 text-xs text-gray-600 dark:text-gray-400">Node.js, npm and Vite describe the machine that built these assets. This PHP deployment does not require Node.js on the web server. Host-managed patches and system libraries require confirmation with the hosting provider. <a href="https://endoflife.date/" target="_blank" rel="noopener noreferrer" class="underline">Lifecycle source &nearr;</a></p>
        </div>
    </details>

    <details class="border-t border-gray-200 dark:border-gray-800" @if ($findings) open @endif>
        <summary class="cursor-pointer px-5 py-3 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800/60 rounded-b-2xl">Dependency security advisories</summary>
        <div class="px-5 pb-5">
            <p class="text-xs text-gray-600 dark:text-gray-400">OSV checks Composer production dependencies and all locked npm build dependencies. PHP, Node.js, database and host OS CVEs require the vendor advisory links above. Package names and versions are sent to OSV; no credentials are sent.</p>
            <p class="mt-4 font-medium {{ $findings ? 'text-red-700 dark:text-red-400' : ($advisories['complete'] ? 'text-emerald-700 dark:text-emerald-400' : 'text-amber-700 dark:text-amber-400') }}">
                @if ($findings)
                    {{ $findings }} known advisory matches
                @elseif ($advisories['complete'])
                    No known dependency advisories found
                @else
                    Advisory check unavailable or incomplete
                @endif
            </p>
            <p class="mt-2 text-xs text-gray-600 dark:text-gray-400">{{ $advisories['count'] }} locked packages · Checked {{ $advisories['checked_at'] }} MYT · Cached for 6 hours. Results are not a full server vulnerability scan.</p>
            @if (! $advisories['complete'] && $findings)<p class="mt-2 text-sm text-amber-700 dark:text-amber-400">Partial results; additional findings may exist.</p>@endif
            <ul class="mt-4 space-y-2 text-sm text-gray-800 dark:text-gray-200">
                @foreach ($advisories['findings'] as $finding)
                    <li><span class="font-mono">{{ $finding['package'] }} {{ $finding['version'] }}</span> — <a class="underline text-red-700 dark:text-red-400" href="https://osv.dev/vulnerability/{{ rawurlencode($finding['id']) }}" target="_blank" rel="noopener noreferrer">{{ $finding['id'] }} &nearr;</a></li>
                @endforeach
            </ul>
        </div>
    </details>
</section>
