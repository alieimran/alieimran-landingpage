{{-- Layered on top of the fixed emerald design system rather than replacing
     it — only emits anything when the admin has actually changed the
     primary accent color away from the built-in default, and only
     targets the highest-visibility accent usages (buttons, links,
     badges). Background/text/secondary colors, button style, border
     radius, and font family are not wired to theme_settings yet — see
     DESIGN_SYSTEM.md and admin/theme/edit.blade.php for why. --}}
@php
    // Wrapped defensively: see the same note in partials/favicon.blade.php —
    // this renders on every page including error pages, and must degrade
    // to "no override" rather than fail if the DB itself is the problem.
    try {
        $themePrimaryColor = \App\Models\ThemeSetting::current()->primary_color;
    } catch (\Throwable $e) {
        $themePrimaryColor = null;
    }
@endphp
@if ($themePrimaryColor && strtolower($themePrimaryColor) !== '#10b981')
    <style>
        :root { --theme-accent: {{ $themePrimaryColor }}; }
        .bg-emerald-600, .bg-emerald-500 { background-color: var(--theme-accent) !important; }
        .text-emerald-600, .text-emerald-500 { color: var(--theme-accent) !important; }
        .border-emerald-500, .hover\:border-emerald-500:hover { border-color: var(--theme-accent) !important; }
        .dark\:text-emerald-400 { color: var(--theme-accent) !important; }
        .dark\:bg-emerald-600 { background-color: var(--theme-accent) !important; }
        .dark\:hover\:text-emerald-400:hover { color: var(--theme-accent) !important; }
    </style>
@endif
