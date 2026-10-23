            <section class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 text-gray-900 dark:text-gray-100">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div><h3 class="text-lg font-semibold">Waktu solat</h3><p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $prayers['zone'] }} · {{ $prayers['date'] }} · Asia/Kuala_Lumpur</p></div>
                    <a href="https://www.e-solat.gov.my/" target="_blank" rel="noopener noreferrer" class="text-sm text-emerald-600 dark:text-emerald-400 underline">JAKIM e-Solat &nearr;</a>
                </div>
                @if ($prayers['times'])
                    <dl class="mt-5 grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-3">
                        @foreach ($prayers['times'] as $label => $time)
                            <div class="rounded-lg bg-gray-50 dark:bg-gray-900 p-3"><dt class="text-xs text-gray-500 dark:text-gray-400">{{ $label }}</dt><dd class="mt-1 font-mono text-lg">{{ $time }}</dd></div>
                        @endforeach
                    </dl>
                    <p class="mt-4 text-xs text-gray-500 dark:text-gray-400">Retrieved {{ $prayers['checked_at'] }} MYT. Refreshed every 15 minutes. Zone configured through JAKIM_PRAYER_ZONE.</p>
                @else
                    <p class="mt-4 text-amber-700 dark:text-amber-400">Today's JAKIM times are unavailable. Check the official timetable and configured zone.</p>
                @endif
            </section>

            <section class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 text-gray-900 dark:text-gray-100">
                <h3 class="text-lg font-semibold">System versions &amp; support</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Green means the latest patch in a maintained release branch, not a guarantee against CVEs. Lifecycle checks use endoflife.date and are cached for 6 hours. Unknown means the version or reference could not be verified.</p>
                <div class="mt-5 overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="text-xs uppercase text-gray-500 dark:text-gray-400"><tr><th class="p-3">Component</th><th class="p-3">Installed</th><th class="p-3">Latest in branch</th><th class="p-3">Status</th><th class="p-3">Support / checked (MYT)</th></tr></thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach ($components as $component)
                                <tr>
                                    <td class="p-3"><a href="{{ $component['advisories'] }}" target="_blank" rel="noopener noreferrer" class="font-semibold underline">{{ $component['name'] }} &nearr;</a><p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $component['scope'] }}</p></td>
                                    <td class="p-3 font-mono">{{ $component['version'] ?? 'Not detected' }}</td>
                                    <td class="p-3 font-mono">{{ $component['latest'] ?? '—' }}</td>
                                    <td class="p-3"><span @class(['inline-flex whitespace-nowrap rounded-full px-3 py-1 text-xs font-medium', 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900 dark:text-emerald-200' => $component['tone'] === 'green', 'bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200' => $component['tone'] === 'amber', 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' => $component['tone'] === 'red', 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200' => $component['tone'] === 'gray'])>{{ $component['status'] }}</span></td>
                                    <td class="p-3 text-xs">Support ends: {{ $component['eol'] ?? 'Unknown' }}<br>Checked: {{ $component['checked_at'] ?? 'Unavailable' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <p class="mt-4 text-xs text-gray-500 dark:text-gray-400">Node.js, npm and Vite describe the machine that built these assets. This PHP deployment does not require Node.js on the web server. Host-managed patches and system libraries require confirmation with the hosting provider. <a href="https://endoflife.date/" target="_blank" rel="noopener noreferrer" class="underline">Lifecycle source &nearr;</a></p>
            </section>

            <section class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 text-gray-900 dark:text-gray-100">
                <h3 class="text-lg font-semibold">Dependency security advisories</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">OSV checks Composer production dependencies and all locked npm build dependencies. PHP, Node.js, database and host OS CVEs require the vendor advisory links above. Package names and versions are sent to OSV; no credentials are sent.</p>
                <p class="mt-4 font-medium {{ count($advisories['findings']) ? 'text-red-700 dark:text-red-400' : ($advisories['complete'] ? 'text-emerald-700 dark:text-emerald-400' : 'text-amber-700 dark:text-amber-400') }}">
                    @if (count($advisories['findings']))
                        {{ count($advisories['findings']) }} known advisory matches
                    @elseif ($advisories['complete'])
                        No known dependency advisories found
                    @else
                        Advisory check unavailable or incomplete
                    @endif
                </p>
                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">{{ $advisories['count'] }} locked packages · Checked {{ $advisories['checked_at'] }} MYT · Cached for 6 hours. Results are not a full server vulnerability scan.</p>
                @if (! $advisories['complete'] && count($advisories['findings']))<p class="mt-2 text-sm text-amber-700 dark:text-amber-400">Partial results; additional findings may exist.</p>@endif
                <ul class="mt-4 space-y-2 text-sm">
                    @foreach ($advisories['findings'] as $finding)
                        <li><span class="font-mono">{{ $finding['package'] }} {{ $finding['version'] }}</span> — <a class="underline text-red-700 dark:text-red-400" href="https://osv.dev/vulnerability/{{ rawurlencode($finding['id']) }}" target="_blank" rel="noopener noreferrer">{{ $finding['id'] }} &nearr;</a></li>
                    @endforeach
                </ul>
            </section>
