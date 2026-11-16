<section class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-5" aria-labelledby="prayer-title">
    <div class="flex items-start justify-between gap-2">
        <div class="min-w-0">
            <h2 id="prayer-title" class="text-base font-semibold text-gray-900 dark:text-gray-50">Waktu solat</h2>
            <p class="text-xs text-gray-600 dark:text-gray-400">
                {{ $prayers['zone'] }}@if ($zoneInfo) · {{ $zoneInfo['state'] }}@endif · {{ $prayers['date'] }} · JAKIM
            </p>
            @if ($zoneInfo)<p class="mt-0.5 truncate text-xs text-gray-600 dark:text-gray-400" title="{{ $zoneInfo['areas'] }}">{{ $zoneInfo['areas'] }}</p>@endif
        </div>
        <a href="https://www.e-solat.gov.my/" target="_blank" rel="noopener noreferrer" class="shrink-0 text-xs font-medium text-emerald-700 dark:text-emerald-400 hover:underline">e-Solat &nearr;</a>
    </div>

    <details class="group mt-3">
        <summary class="inline-flex cursor-pointer items-center gap-1 rounded-md text-xs font-medium text-emerald-700 dark:text-emerald-400 hover:underline focus-visible:outline focus-visible:outline-2 focus-visible:outline-emerald-500">Change zone</summary>
        <form method="POST" action="{{ route('admin.prayer-zone.update') }}" class="mt-2 space-y-2">
            @csrf @method('PATCH')
            <label for="prayer_zone" class="sr-only">Prayer zone</label>
            <select id="prayer_zone" name="prayer_zone" class="block w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-950 dark:text-gray-200 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                @foreach ($zoneGroups as $state => $zones)
                    <optgroup label="{{ $state }}">
                        @foreach ($zones as $code => $areas)
                            <option value="{{ $code }}" @selected($prayers['zone'] === $code)>{{ $code }} — {{ \Illuminate\Support\Str::limit($areas, 48) }}</option>
                        @endforeach
                    </optgroup>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('prayer_zone')" />
            <button type="submit" class="rounded-lg bg-emerald-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-emerald-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-500">Save zone</button>
            <p class="text-xs text-gray-600 dark:text-gray-400">Also sets the state used for state holidays.</p>
        </form>
    </details>

    @if ($prayers['times'])
        @if ($nextPrayer)
            <div class="mt-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 px-4 py-3">
                <p class="font-mono text-[11px] uppercase tracking-widest text-emerald-800 dark:text-emerald-300">Next · {{ $nextPrayer['label'] }}</p>
                <p class="mt-0.5 text-3xl font-semibold tabular-nums text-emerald-900 dark:text-emerald-100">{{ $nextPrayer['time'] }}</p>
                <p class="text-xs text-emerald-900/80 dark:text-emerald-200/80">in {{ intdiv($nextPrayer['minutes'], 60) ? intdiv($nextPrayer['minutes'], 60).' h ' : '' }}{{ $nextPrayer['minutes'] % 60 }} min</p>
            </div>
        @endif
        <dl class="mt-3 grid grid-cols-2 gap-x-4 gap-y-1.5 text-sm">
            @foreach ($prayers['times'] as $label => $time)
                <div @class(['flex justify-between', 'font-semibold text-emerald-700 dark:text-emerald-400' => $nextPrayer && $nextPrayer['label'] === $label, 'text-gray-800 dark:text-gray-200' => ! ($nextPrayer && $nextPrayer['label'] === $label)])>
                    <dt class="text-gray-600 dark:text-gray-400">{{ $label }}</dt><dd class="font-mono tabular-nums">{{ $time }}</dd>
                </div>
            @endforeach
        </dl>
        <p class="mt-3 text-xs text-gray-600 dark:text-gray-400">Retrieved {{ $prayers['checked_at'] }} MYT. Refreshed every 15 minutes.</p>
    @else
        <p class="mt-4 text-sm text-amber-700 dark:text-amber-400">Today's JAKIM times are unavailable. Check the official timetable and configured zone.</p>
    @endif
</section>
