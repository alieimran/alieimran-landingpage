<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Admin Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                <a href="{{ route('admin.links.index') }}" class="block bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 hover:ring-1 hover:ring-emerald-500/40 transition">
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Links') }}</p>
                    <p class="text-3xl font-semibold text-gray-900 dark:text-gray-100">{{ $linkCount }}</p>
                </a>
                <a href="{{ route('admin.social-links.index') }}" class="block bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 hover:ring-1 hover:ring-emerald-500/40 transition">
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Social Links') }}</p>
                    <p class="text-3xl font-semibold text-gray-900 dark:text-gray-100">{{ $socialLinkCount }}</p>
                </a>
                <a href="{{ route('admin.contact-inquiries.index', ['status' => 'new']) }}" class="block bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 hover:ring-1 hover:ring-emerald-500/40 transition">
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('New Inquiries') }}</p>
                    <p class="text-3xl font-semibold text-gray-900 dark:text-gray-100">{{ $newInquiryCount }}</p>
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
