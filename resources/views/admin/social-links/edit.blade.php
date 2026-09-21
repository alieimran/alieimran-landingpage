<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Edit Social Link') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('admin.social-links.update', $socialLink) }}">
                    @csrf
                    @method('PUT')
                    @include('admin.social-links._form')

                    <div class="mt-6 flex items-center gap-4">
                        <x-primary-button>{{ __('Save Changes') }}</x-primary-button>
                        <a href="{{ route('admin.social-links.index') }}" class="text-sm text-gray-600 dark:text-gray-400">{{ __('Cancel') }}</a>
                    </div>
                </form>

                <form method="POST" action="{{ route('admin.social-links.destroy', $socialLink) }}" class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700" onsubmit="return confirm('Delete this social link?');">
                    @csrf
                    @method('DELETE')
                    <x-danger-button>{{ __('Delete') }}</x-danger-button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
