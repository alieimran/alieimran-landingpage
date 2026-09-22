@props(['class' => 'h-8 w-auto'])

@php
    try {
        $logoPath = \App\Models\ThemeSetting::current()->logo_path;
    } catch (\Throwable $e) {
        $logoPath = null;
    }
@endphp

@if ($logoPath)
    <img src="{{ \Illuminate\Support\Facades\Storage::url($logoPath) }}" alt="{{ config('app.name') }}" class="{{ $class }}">
@else
    <x-application-logo :class="$class.' fill-current text-gray-800 dark:text-emerald-400'" />
@endif
