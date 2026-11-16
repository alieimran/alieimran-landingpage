@props(['label', 'value', 'previous' => null, 'spark' => [], 'href' => null, 'hint' => 'last 7 days'])
@php
    $pct = $previous === null ? null : ($previous > 0 ? round((($value - $previous) / $previous) * 100) : ($value > 0 ? null : 0));
    $dir = $previous === null ? null : ($previous == 0 && $value > 0 ? 'up' : ($pct > 0 ? 'up' : ($pct < 0 ? 'down' : 'flat')));
    $max = max(1, ...($spark ?: [0]));
    $n = max(1, count($spark) - 1);
    $pts = collect($spark)->map(fn ($v, $i) => round(($i / $n) * 100, 1).','.round(26 - ($v / $max) * 22, 1))->implode(' ');
    $tag = $href ? 'a' : 'div';
@endphp
<{{ $tag }} @if($href) href="{{ $href }}" @endif {{ $attributes->class(['group relative block rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-5 transition', 'hover:border-emerald-500/50 hover:shadow-lg hover:shadow-emerald-500/5 focus-visible:outline focus-visible:outline-2 focus-visible:outline-emerald-500' => $href]) }}>
    <div class="flex items-start justify-between gap-2">
        <p class="font-mono text-[11px] uppercase tracking-widest text-gray-600 dark:text-gray-400">{{ $label }}</p>
        @if ($dir)
            <span @class(['inline-flex items-center gap-0.5 rounded-full px-2 py-0.5 text-xs font-medium', 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' => $dir === 'up', 'bg-red-100 text-red-800 dark:bg-red-950 dark:text-red-300' => $dir === 'down', 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300' => $dir === 'flat'])
                  title="Compared with the previous 7 days ({{ number_format($previous) }})">
                @if ($dir === 'up')<x-icon name="up" class="w-3 h-3" />@elseif ($dir === 'down')<x-icon name="down" class="w-3 h-3" />@endif
                {{ $pct === null ? 'new' : ($pct > 0 ? '+' : '').$pct.'%' }}
            </span>
        @endif
    </div>
    <p class="mt-3 text-4xl font-semibold tracking-tight text-gray-900 dark:text-gray-50 tabular-nums">{{ number_format($value) }}</p>
    <p class="mt-1 text-xs text-gray-600 dark:text-gray-400">{{ $hint }}</p>
    @if (count($spark) > 1)
        <svg viewBox="0 0 100 30" preserveAspectRatio="none" class="mt-3 h-8 w-full overflow-visible" role="img" aria-label="{{ $label }} over the last 7 days: {{ implode(', ', $spark) }}">
            <polyline points="{{ $pts }}" fill="none" stroke-width="2" vector-effect="non-scaling-stroke" stroke-linecap="round" stroke-linejoin="round" class="stroke-emerald-600 dark:stroke-emerald-400" />
        </svg>
    @endif
</{{ $tag }}>
