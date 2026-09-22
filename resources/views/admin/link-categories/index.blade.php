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
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('Sort order') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ __('Links') }}</th>
                            <th class="px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse ($categories as $category)
                            @php($updateFormId = 'cat-update-'.$category->id)
                            @php($deleteFormId = 'cat-delete-'.$category->id)
                            <tr>
                                <td class="px-6 py-4">
                                    <input type="text" name="name" form="{{ $updateFormId }}" value="{{ $category->name }}" required class="block w-40 text-sm border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                                </td>
                                <td class="px-6 py-4">
                                    <input type="number" name="sort_order" form="{{ $updateFormId }}" value="{{ $category->sort_order }}" min="0" class="block w-20 text-sm border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">{{ $category->links_count }}</td>
                                <td class="px-6 py-4 text-right text-sm whitespace-nowrap space-x-3">
                                    <button type="submit" form="{{ $updateFormId }}" class="text-emerald-600 dark:text-emerald-400 hover:underline">{{ __('Save') }}</button>
                                    <button type="submit" form="{{ $deleteFormId }}" onclick="return confirm('Delete this category? Links using it will become uncategorized.');" class="text-red-600 dark:text-red-400 hover:underline">{{ __('Delete') }}</button>

                                    {{-- Forms with no visible fields of their own — every input/button
                                         above points at one of these via the form="" attribute, since
                                         two <form> elements can't otherwise share one table row. --}}
                                    <form id="{{ $updateFormId }}" method="POST" action="{{ route('admin.link-categories.update', $category) }}">
                                        @csrf
                                        @method('PUT')
                                    </form>
                                    <form id="{{ $deleteFormId }}" method="POST" action="{{ route('admin.link-categories.destroy', $category) }}">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-8 text-center text-sm text-gray-500 dark:text-gray-400">{{ __('No categories yet.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
