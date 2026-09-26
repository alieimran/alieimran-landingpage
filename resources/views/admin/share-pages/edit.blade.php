<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Edit Share Page') }}
            </h2>
            <a href="{{ route('share-page.show', $page) }}" target="_blank" rel="noopener noreferrer" class="font-mono text-sm text-emerald-600 dark:text-emerald-400 hover:underline truncate">
                /{{ $page->slug }} &#8599;
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="bg-green-50 dark:bg-green-900 text-green-800 dark:text-green-200 text-sm rounded-md p-4">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('admin.share-pages.update', $page) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    @include('admin.share-pages._form')

                    <div class="mt-6 flex items-center gap-4">
                        <x-primary-button>{{ __('Save Changes') }}</x-primary-button>
                        <a href="{{ route('admin.share-pages.index') }}" class="text-sm text-gray-600 dark:text-gray-400">{{ __('Cancel') }}</a>
                    </div>
                </form>

                <form method="POST" action="{{ route('admin.share-pages.destroy', $page) }}" class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700" onsubmit="return confirm('Delete this page and its images?');">
                    @csrf
                    @method('DELETE')
                    <x-danger-button>{{ __('Delete Page') }}</x-danger-button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
