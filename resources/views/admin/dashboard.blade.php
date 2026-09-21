<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Admin Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Links') }}</p>
                    <p class="text-3xl font-semibold text-gray-900 dark:text-gray-100">{{ $linkCount }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Social Links') }}</p>
                    <p class="text-3xl font-semibold text-gray-900 dark:text-gray-100">{{ $socialLinkCount }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('New Inquiries') }}</p>
                    <p class="text-3xl font-semibold text-gray-900 dark:text-gray-100">{{ $newInquiryCount }}</p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
