@php
    $when = fn (int $days) => $days === 0 ? 'Today' : ($days === 1 ? 'Tomorrow' : 'in '.$days.' days');
@endphp
<div class="grid gap-6 md:grid-cols-3">
    {{-- National --}}
    <section class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-5" aria-labelledby="hol-nat-title">
        <h2 id="hol-nat-title" class="text-base font-semibold text-gray-900 dark:text-gray-50">Next public holidays</h2>
        <p class="text-xs text-gray-600 dark:text-gray-400">National · all of Malaysia</p>
        <ul class="mt-3 divide-y divide-gray-200 dark:divide-gray-800">
            @forelse ($nationalHolidays['items'] as $h)
                <li class="flex items-center gap-3 py-2.5">
                    <div class="w-12 shrink-0 rounded-lg bg-gray-100 dark:bg-gray-800 py-1 text-center leading-tight" aria-hidden="true">
                        <div class="font-mono text-[10px] uppercase text-gray-600 dark:text-gray-400">{{ $h['date']->format('M') }}</div>
                        <div class="text-lg font-semibold text-gray-900 dark:text-gray-50 tabular-nums">{{ $h['date']->format('j') }}</div>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-medium text-gray-900 dark:text-gray-50">{{ $h['name'] }}</p>
                        <p class="text-xs text-gray-600 dark:text-gray-400">
                            @if ($h['replacement'])<span class="mr-1 inline-block rounded bg-amber-100 dark:bg-amber-950 px-1.5 py-0.5 font-mono text-[10px] uppercase text-amber-900 dark:text-amber-300">replacement</span>@endif{{ $h['date']->format('l, j M Y') }} · {{ $when($h['days']) }}@if ($h['tentative']) · <span class="text-amber-700 dark:text-amber-400">tentative</span>@endif
                        </p>
                    </div>
                </li>
            @empty
                <li class="py-6 text-sm text-gray-600 dark:text-gray-400">
                    {{ $nationalHolidays['available'] ? 'No upcoming holidays in the calendar feed.' : 'Holiday calendar is unavailable right now. It will retry in a few minutes.' }}
                </li>
            @endforelse
        </ul>
    </section>

    {{-- State --}}
    <section class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-5" aria-labelledby="hol-state-title">
        <h2 id="hol-state-title" class="text-base font-semibold text-gray-900 dark:text-gray-50">State holidays</h2>
        <p class="text-xs text-gray-600 dark:text-gray-400">{{ implode(' + ', $stateNames) }} · first state from prayer zone {{ $prayers['zone'] }}</p>
        <details class="mt-2">
            <summary class="inline-flex cursor-pointer text-xs font-medium text-emerald-700 dark:text-emerald-400 hover:underline focus-visible:outline focus-visible:outline-2 focus-visible:outline-emerald-500 rounded-md">Choose states</summary>
            <form method="POST" action="{{ route('admin.holiday-state.update') }}" class="mt-2 space-y-2">
                @csrf @method('PATCH')
                <label for="holiday_extra_state" class="block text-xs text-gray-600 dark:text-gray-400">Also show holidays for</label>
                <select id="holiday_extra_state" name="holiday_extra_state" class="block w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-950 dark:text-gray-200 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                    <option value="">None</option>
                    @foreach ($stateOptions as $code => $name)
                        @continue($code === $primaryStateCode)
                        <option value="{{ $code }}" @selected($extraStateCode === $code)>{{ $name }}</option>
                    @endforeach
                </select>
                <button type="submit" class="rounded-lg bg-emerald-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-emerald-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-500">Save</button>
                <p class="text-xs text-gray-600 dark:text-gray-400">The first state follows your prayer zone; change it from the Waktu solat card.</p>
            </form>
        </details>
        <ul class="mt-3 divide-y divide-gray-200 dark:divide-gray-800">
            @forelse ($stateHolidays as $h)
                <li class="flex items-center gap-3 py-2.5">
                    <div class="w-12 shrink-0 rounded-lg bg-gray-100 dark:bg-gray-800 py-1 text-center leading-tight" aria-hidden="true">
                        <div class="font-mono text-[10px] uppercase text-gray-600 dark:text-gray-400">{{ $h['date']->format('M') }}</div>
                        <div class="text-lg font-semibold text-gray-900 dark:text-gray-50 tabular-nums">{{ $h['date']->format('j') }}</div>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-medium text-gray-900 dark:text-gray-50">{{ $h['name'] }}</p>
                        <p class="text-xs text-gray-600 dark:text-gray-400">
                            @foreach ($h['states'] as $stateLabel)<span class="mr-1 inline-block rounded bg-gray-100 dark:bg-gray-800 px-1.5 py-0.5 font-mono text-[10px] uppercase text-gray-700 dark:text-gray-300">{{ str_replace(' / Putrajaya', '', $stateLabel) }}</span>@endforeach
                            @if ($h['replacement'])<span class="mr-1 inline-block rounded bg-amber-100 dark:bg-amber-950 px-1.5 py-0.5 font-mono text-[10px] uppercase text-amber-900 dark:text-amber-300">replacement</span>@endif{{ $h['date']->format('D, j M Y') }} · {{ $when($h['days']) }}@if ($h['tentative']) · <span class="text-amber-700 dark:text-amber-400">tentative</span>@endif
                        </p>
                    </div>
                </li>
            @empty
                <li class="py-6 text-sm text-gray-600 dark:text-gray-400">No state-specific holidays on file for {{ implode(' or ', $stateNames) }}. Add them in <span class="font-mono text-xs">config/holidays.php</span>.</li>
            @endforelse
        </ul>
        <p class="mt-2 text-xs text-gray-600 dark:text-gray-400">Curated fixed-date list. Confirm against the official gazette.</p>
    </section>

    {{-- Hijri --}}
    <section class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-5" aria-labelledby="hijri-title">
        <h2 id="hijri-title" class="text-base font-semibold text-gray-900 dark:text-gray-50">Hijri calendar</h2>
        <p class="text-xs text-gray-600 dark:text-gray-400">
            @if ($hijriToday)
                Today · <span class="font-medium text-gray-900 dark:text-gray-50">{{ $hijriToday['day'] }} {{ $hijriToday['month_name'] }} {{ $hijriToday['year'] }} H</span> <span class="text-gray-500 dark:text-gray-400">(JAKIM)</span>
            @else
                Official Hijri date unavailable right now
            @endif
        </p>
        @unless (\App\Services\HijriCalendar::supported())
            <p class="mt-4 text-sm text-amber-700 dark:text-amber-400">Upcoming occasions need the PHP <span class="font-mono">intl</span> extension, which is not enabled on this server.</p>
        @endunless
        <ul class="mt-3 divide-y divide-gray-200 dark:divide-gray-800">
            @foreach ($hijriOccasions as $o)
                <li class="flex items-center gap-3 py-2.5">
                    <div class="w-12 shrink-0 rounded-lg bg-emerald-50 dark:bg-emerald-950/50 py-1 text-center leading-tight" aria-hidden="true">
                        <div class="font-mono text-[10px] uppercase text-emerald-800 dark:text-emerald-300">{{ $o['date']->format('M') }}</div>
                        <div class="text-lg font-semibold text-emerald-900 dark:text-emerald-200 tabular-nums">{{ $o['date']->format('j') }}</div>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-medium text-gray-900 dark:text-gray-50">{{ $o['name'] }}</p>
                        <p class="text-xs text-gray-600 dark:text-gray-400">{{ $o['hijri'] }} · {{ $o['date']->format('D, j M Y') }} · {{ $when($o['days']) }}</p>
                    </div>
                </li>
            @endforeach
        </ul>
        <p class="mt-2 text-xs text-gray-600 dark:text-gray-400">Estimated. Official dates follow moon sighting and may differ by a day.</p>
    </section>
</div>
