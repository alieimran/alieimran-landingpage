<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Inquiry from :name', ['name' => $inquiry->name]) }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="bg-green-50 dark:bg-green-900 text-green-800 dark:text-green-200 text-sm rounded-md p-4">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 space-y-4">
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">{{ __('Name') }}</dt>
                        <dd class="text-gray-900 dark:text-gray-100">{{ $inquiry->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">{{ __('Email') }}</dt>
                        <dd class="text-gray-900 dark:text-gray-100"><a href="mailto:{{ $inquiry->email }}" class="text-emerald-600 dark:text-emerald-400 hover:underline">{{ $inquiry->email }}</a></dd>
                    </div>
                    @if ($inquiry->phone)
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">{{ __('Phone') }}</dt>
                            <dd class="text-gray-900 dark:text-gray-100">{{ $inquiry->phone }}</dd>
                        </div>
                    @endif
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">{{ __('Category') }}</dt>
                        <dd class="text-gray-900 dark:text-gray-100">{{ str($inquiry->category)->replace('_', ' ')->title() }}</dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-gray-500 dark:text-gray-400">{{ __('Subject') }}</dt>
                        <dd class="text-gray-900 dark:text-gray-100">{{ $inquiry->subject }}</dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-gray-500 dark:text-gray-400">{{ __('Received') }}</dt>
                        <dd class="text-gray-900 dark:text-gray-100">{{ $inquiry->created_at->format('Y-m-d H:i') }}</dd>
                    </div>
                </dl>

                <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                    <dt class="text-gray-500 dark:text-gray-400 text-sm mb-1">{{ __('Message') }}</dt>
                    <dd class="text-gray-900 dark:text-gray-100 text-sm whitespace-pre-wrap leading-relaxed">{{ $inquiry->message }}</dd>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('admin.contact-inquiries.update', $inquiry) }}" class="flex flex-wrap items-end gap-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <x-input-label for="status" value="Status" />
                        <select id="status" name="status" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm">
                            @foreach (['new', 'read', 'replied', 'archived'] as $status)
                                <option value="{{ $status }}" @selected($inquiry->status === $status)>{{ ucfirst($status) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <x-primary-button>{{ __('Update Status') }}</x-primary-button>
                </form>

                <form method="POST" action="{{ route('admin.contact-inquiries.destroy', $inquiry) }}" class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700" onsubmit="return confirm('Delete this inquiry permanently?');">
                    @csrf
                    @method('DELETE')
                    <x-danger-button>{{ __('Delete Inquiry') }}</x-danger-button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
