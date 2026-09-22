<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Vite;
use Symfony\Component\HttpFoundation\Response;

class SecureHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        // With 'strict-dynamic' in script-src, browsers that understand it
        // ignore host/scheme allowlists (including 'self') entirely and
        // trust only nonce'd or hash-allowed scripts — so Vite's own
        // generated <script>/<link> tags need this same nonce too, via
        // Laravel's built-in hook, or they'd get silently blocked.
        $nonce = Vite::useCspNonce();
        $request->attributes->set('csp_nonce', $nonce);
        view()->share('cspNonce', $nonce);

        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=(), payment=()');

        if ($request->secure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        $response->headers->set('Content-Security-Policy', $this->contentSecurityPolicy($nonce));

        return $response;
    }

    /**
     * style-src allows 'unsafe-inline': Tailwind-generated inline
     * `style=""` attributes (chart bar widths, background-image
     * gradients) are used throughout the design system. That's a
     * deliberate, documented tradeoff — see SECURITY.md — rather than
     * an oversight; nonce-ing every inline style would need a much
     * larger refactor for comparatively little security benefit
     * (CSS injection is a far weaker vector than script injection).
     * script-src is otherwise as strict as this app can currently go:
     * 'self', the request's nonce, and (via strict-dynamic) scripts
     * loaded by an already-trusted script — which is what lets
     * Google's gtag.js loader load its own additional scripts without
     * widening the allowlist.
     *
     * 'unsafe-eval' is the one deliberate exception, and a real
     * tradeoff, not an oversight: Alpine.js (used for the mobile nav,
     * the settings dropdown, the delete-account confirmation modal,
     * and the theme toggle) evaluates directive expressions via
     * `Function()` internally, which CSP's eval restriction blocks by
     * design. The alternatives — migrating to Alpine's separate CSP
     * build (which requires pre-registering every directive's logic
     * in JS instead of writing it inline, a real migration effort
     * given the modal's focus-trap logic) or dropping Alpine for
     * hand-written vanilla JS — are both worth doing, just not as a
     * rushed change buried in an unrelated task. Tracked in
     * PROJECT_STATUS.md.
     */
    private function contentSecurityPolicy(string $nonce): string
    {
        $directives = [
            "default-src 'self'",
            "script-src 'self' 'unsafe-eval' 'nonce-{$nonce}' 'strict-dynamic' https://www.googletagmanager.com",
            "style-src 'self' 'unsafe-inline' https://fonts.bunny.net",
            "font-src 'self' https://fonts.bunny.net",
            "img-src 'self' data: https:",
            "connect-src 'self' https://www.google-analytics.com https://*.google-analytics.com",
            "frame-ancestors 'none'",
            "base-uri 'self'",
            "form-action 'self'",
            "object-src 'none'",
        ];

        return implode('; ', $directives);
    }
}
