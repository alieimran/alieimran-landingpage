<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Link Categories') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="bg-green-50 dark:bg-green-900 text-green-800 dark:text-green-200 text-sm rounded-md p-4">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4">{{ __('New Category') }}</h3>
                <form method="POST" action="{{ route('admin.link-categories.store') }}" class="flex flex-wrap items-end gap-4">
                    @csrf
                    <div class="flex-1 min-w-[200px]">
                        <x-input-label for="name" value="Name" />
                        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" required />
                    </div>
                    <div class="w-32">
                        <x-input-label for="sort_order" value="Sort order" />
                        <x-text-input id="sort_order" name="sort_order" type="number" min="0" class="mt-1 block w-full" value="0" />
                    </div>
                    <x-primary-button>{{ __('Add') }}</x-primary-button>
                </form>
                @if ($errors->any())
                    <x-input-error :messages="$errors->all()" class="mt-2" />
                @endif
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('Name') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('Links') }}</th>
                            <th class="px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse ($categories as $category)
                            <tr>
                                <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">{{ $category->name }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">{{ $category->links_count }}</td>
                                <td class="px-6 py-4 text-right text-sm">
                                    <form method="POST" action="{{ route('admin.link-categories.destroy', $category) }}" onsubmit="return confirm('Delete this category? Links using it will become uncategorized.');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 dark:text-red-400 hover:underline">{{ __('Delete') }}</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-6 py-8 text-center text-sm text-gray-500 dark:text-gray-400">{{ __('No categories yet.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
