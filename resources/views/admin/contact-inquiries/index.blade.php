<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Contact Inquiries') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="bg-green-50 dark:bg-green-900 text-green-800 dark:text-green-200 text-sm rounded-md p-4">
                    {{ session('status') }}
                </div>
            @endif

            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.contact-inquiries.index') }}" class="px-3 py-1.5 rounded-md text-xs font-mono uppercase tracking-wide {{ !$statusFilter ? 'bg-emerald-600 text-white' : 'border border-gray-300 dark:border-gray-700 text-gray-600 dark:text-gray-400' }}">All</a>
                @foreach (['new' => 'New', 'read' => 'Read', 'replied' => 'Replied', 'archived' => 'Archived'] as $value => $label)
                    <a href="{{ route('admin.contact-inquiries.index', ['status' => $value]) }}" class="px-3 py-1.5 rounded-md text-xs font-mono uppercase tracking-wide {{ $statusFilter === $value ? 'bg-emerald-600 text-white' : 'border border-gray-300 dark:border-gray-700 text-gray-600 dark:text-gray-400' }}">{{ $label }}</a>
                @endforeach
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('From') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('Subject') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('Category') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('Status') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('Received') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse ($inquiries as $inquiry)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/50 cursor-pointer" onclick="window.location='{{ route('admin.contact-inquiries.show', $inquiry) }}'">
                                <td class="px-6 py-4 text-sm">
                                    <div class="font-medium text-gray-900 dark:text-gray-100">{{ $inquiry->name }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-500">{{ $inquiry->email }}</div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">{{ $inquiry->subject }}</td>
                                <td class="px-6 py-4 text-xs font-mono uppercase text-gray-500 dark:text-gray-500">{{ str($inquiry->category)->replace('_', ' ') }}</td>
                                <td class="px-6 py-4 text-sm">
                                    <span @class([
                                        'px-2 py-0.5 rounded-full text-xs font-mono uppercase',
                                        'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400' => $inquiry->status === 'new',
                                        'bg-gray-500/10 text-gray-600 dark:text-gray-400' => $inquiry->status === 'read',
                                        'bg-cyan-500/10 text-cyan-600 dark:text-cyan-400' => $inquiry->status === 'replied',
                                        'bg-gray-500/10 text-gray-400 dark:text-gray-600' => $inquiry->status === 'archived',
                                    ])>{{ $inquiry->status }}</span>
                                </td>
                                <td class="px-6 py-4 text-xs text-gray-500 dark:text-gray-500">{{ $inquiry->created_at->diffForHumans() }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-sm text-gray-500 dark:text-gray-400">{{ __('No inquiries yet.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $inquiries->links() }}
        </div>
    </div>
</x-app-layout>
