@props(['label', 'count', 'max' => 1, 'badge' => null])

<div class="py-1.5">
    <div class="flex items-center justify-between text-sm mb-1">
        <span class="truncate text-gray-700 dark:text-gray-300 flex items-center gap-1.5">
            {{ $label }}
            @if ($badge)
                <span class="font-mono text-[9px] uppercase px-1.5 py-0.5 rounded bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400">{{ $badge }}</span>
            @endif
        </span>
        <span class="font-mono text-xs text-gray-500 dark:text-gray-400 shrink-0 ml-2">{{ $count }}</span>
    </div>
    <div class="h-1.5 rounded-full bg-gray-100 dark:bg-gray-700 overflow-hidden">
        <div class="h-full rounded-full bg-emerald-500" style="width: {{ max(3, round(($count / max(1, $max)) * 100)) }}%"></div>
    </div>
</div>
