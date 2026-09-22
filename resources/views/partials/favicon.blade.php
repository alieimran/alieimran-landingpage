@php
    // Wrapped defensively: this partial renders on every page, including
    // error pages — if the error being displayed is itself a database
    // failure, this query must not also fail and break the error page.
    try {
        $faviconPath = \App\Models\ThemeSetting::current()->favicon_path;
    } catch (\Throwable $e) {
        $faviconPath = null;
    }
@endphp

@if ($faviconPath)
    <link rel="icon" href="{{ \Illuminate\Support\Facades\Storage::url($faviconPath) }}" sizes="any">
@else
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
@endif
<link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
