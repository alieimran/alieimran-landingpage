<span @class([
    'px-2 py-0.5 rounded-full text-xs font-mono uppercase',
    'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400' => $inquiry->status === 'new',
    'bg-gray-500/10 text-gray-600 dark:text-gray-400' => $inquiry->status === 'read',
    'bg-cyan-500/10 text-cyan-600 dark:text-cyan-400' => $inquiry->status === 'replied',
    'bg-gray-500/10 text-gray-400 dark:text-gray-600' => $inquiry->status === 'archived',
])>{{ $inquiry->status }}</span>
