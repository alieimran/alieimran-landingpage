<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Share Pages') }}
            </h2>
            <a href="{{ route('admin.share-pages.create') }}" class="inline-flex items-center px-4 py-2 bg-emerald-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-emerald-500">
                {{ __('New Page') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="bg-green-50 dark:bg-green-900 text-green-800 dark:text-green-200 text-sm rounded-md p-4">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse ($pages as $page)
                        @php($thumb = $page->displayImages()[0] ?? null)
                        <li class="flex items-center gap-4 px-4 sm:px-6 py-4">
                            <div class="h-12 w-12 shrink-0 rounded bg-gray-100 dark:bg-gray-900 overflow-hidden">
                                @if ($thumb)
                                    <img src="{{ $thumb }}" alt="" class="h-full w-full object-cover" referrerpolicy="no-referrer">
                                @endif
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">{{ $page->title }}</p>
                                <a href="{{ route('share-page.show', $page) }}" target="_blank" rel="noopener noreferrer" class="font-mono text-xs text-emerald-600 dark:text-emerald-400 hover:underline truncate block">
                                    /{{ $page->slug }} &#8599;
                                </a>
                            </div>
                            <div class="text-sm shrink-0">
                                @if ($page->enabled)
                                    <span class="text-green-700 dark:text-green-400">{{ __('Enabled') }}</span>
                                @else
                                    <span class="text-gray-400">{{ __('Disabled') }}</span>
                                @endif
                            </div>
                            <a href="{{ route('admin.share-pages.edit', $page) }}" class="text-sm shrink-0 text-emerald-600 dark:text-emerald-400 hover:underline">{{ __('Edit') }}</a>
                        </li>
                    @empty
                        <li class="px-6 py-8 text-center text-sm text-gray-500 dark:text-gray-400">{{ __('No share pages yet.') }}</li>
                    @endforelse
                </ul>
            </div>

            {{ $pages->links() }}
        </div>
    </div>
</x-app-layout>
