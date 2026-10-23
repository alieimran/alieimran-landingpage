<?php

namespace App\Http\Middleware;

use App\Models\SiteSetting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CmsMaintenance
{
    public function handle(Request $request, Closure $next): Response
    {
        // Admin management and account recovery remain governed by their existing auth middleware.
        if ($request->is('admin', 'admin/*', 'login', 'logout', 'forgot-password', 'reset-password', 'reset-password/*', 'verify-email', 'verify-email/*', 'email/verification-notification', 'confirm-password')
            || $request->user()?->is_admin) {
            return $next($request);
        }

        $setting = SiteSetting::current();
        if (! $setting->maintenance_enabled) {
            return $next($request);
        }

        $retry = $setting->maintenance_until?->isFuture()
            ? max(60, (int) now()->diffInSeconds($setting->maintenance_until)) : 300;

        return response()->view('maintenance', ['setting' => $setting], 503)
            ->header('Retry-After', (string) $retry)
            ->header('Cache-Control', 'no-store, private')
            ->header('X-Robots-Tag', 'noindex, nofollow');
    }
}
