@props(['class' => ''])

<button
    type="button"
    x-data
    @click="window.__setTheme(!document.documentElement.classList.contains('dark'))"
    aria-label="Toggle dark mode"
    {{ $attributes->merge(['class' => "inline-flex items-center justify-center rounded-md border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 hover:border-emerald-500/40 hover:text-emerald-600 dark:hover:text-emerald-400 transition $class"]) }}
>
    {{-- Sun: shown in dark mode, click to switch to light --}}
    <svg class="hidden dark:block w-4 h-4" viewBox="0 0 20 20" fill="currentColor">
        <path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 9a1 1 0 100 2h1a1 1 0 100-2h-1zM2 9a1 1 0 100 2h1a1 1 0 100-2H2zm2.05-5.536a1 1 0 000 1.415l.707.707A1 1 0 106.17 4.172l-.707-.707a1 1 0 00-1.414 0zm.707 12.02l-.707.708a1 1 0 001.414 1.414l.707-.707a1 1 0 00-1.414-1.414zM10 16a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1z" />
    </svg>
    {{-- Moon: shown in light mode, click to switch to dark --}}
    <svg class="block dark:hidden w-4 h-4" viewBox="0 0 20 20" fill="currentColor">
        <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z" />
    </svg>
</button>
