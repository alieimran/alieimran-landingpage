<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Contact Inquiries') }}
        </h2>
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
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

            {{-- Mobile: each inquiry is a tappable card --}}
            <div class="md:hidden bg-white dark:bg-gray-800 shadow-sm rounded-lg divide-y divide-gray-200 dark:divide-gray-700">
                @forelse ($inquiries as $inquiry)
                    <div class="flex items-start gap-3 p-4">
                        <a href="{{ route('admin.contact-inquiries.show', $inquiry) }}" class="min-w-0 flex-1 space-y-1">
                            <div class="flex items-center justify-between gap-2">
                                <span class="font-medium text-sm text-gray-900 dark:text-gray-100 truncate">{{ $inquiry->name }}</span>
                                <span class="shrink-0 text-xs text-gray-500 dark:text-gray-500">{{ $inquiry->created_at->diffForHumans() }}</span>
                            </div>
                            <div class="text-xs text-gray-500 dark:text-gray-500 truncate">{{ $inquiry->email }}</div>
                            <div class="text-sm text-gray-700 dark:text-gray-300 truncate">{{ $inquiry->subject }}</div>
                            <div class="flex flex-wrap items-center gap-2 pt-1">
                                @include('admin.contact-inquiries._status-badge')
                                <span class="text-xs font-mono uppercase text-gray-500 dark:text-gray-500">{{ str($inquiry->category)->replace('_', ' ') }}</span>
                            </div>
                        </a>
                        <a href="{{ route('admin.contact-inquiries.show', $inquiry) }}" class="shrink-0 px-3 py-1.5 rounded-md border border-gray-300 dark:border-gray-700 text-xs font-semibold uppercase tracking-wide text-emerald-600 dark:text-emerald-400 hover:bg-gray-50 dark:hover:bg-gray-900">{{ __('Edit') }}</a>
                    </div>
                @empty
                    <div class="p-8 text-center text-sm text-gray-500 dark:text-gray-400">{{ __('No inquiries yet.') }}</div>
                @endforelse
            </div>

            {{-- Desktop: table --}}
            <div class="hidden md:block bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('From') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('Subject') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('Category') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('Status') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('Received') }}</th>
                            <th class="px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse ($inquiries as $inquiry)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/50 cursor-pointer" onclick="window.location='{{ route('admin.contact-inquiries.show', $inquiry) }}'">
                                <td class="px-6 py-4 text-sm">
                                    <a href="{{ route('admin.contact-inquiries.show', $inquiry) }}" class="font-medium text-gray-900 dark:text-gray-100">{{ $inquiry->name }}</a>
                                    <div class="text-xs text-gray-500 dark:text-gray-500">{{ $inquiry->email }}</div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">{{ $inquiry->subject }}</td>
                                <td class="px-6 py-4 text-xs font-mono uppercase text-gray-500 dark:text-gray-500 whitespace-nowrap">{{ str($inquiry->category)->replace('_', ' ') }}</td>
                                <td class="px-6 py-4 text-sm">
                                    @include('admin.contact-inquiries._status-badge')
                                </td>
                                <td class="px-6 py-4 text-xs text-gray-500 dark:text-gray-500 whitespace-nowrap">{{ $inquiry->created_at->diffForHumans() }}</td>
                                <td class="px-6 py-4 text-right text-sm">
                                    <a href="{{ route('admin.contact-inquiries.show', $inquiry) }}" class="text-emerald-600 dark:text-emerald-400 hover:underline">{{ __('Edit') }}</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-sm text-gray-500 dark:text-gray-400">{{ __('No inquiries yet.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $inquiries->links() }}
        </div>
    </div>
</x-app-layout>
