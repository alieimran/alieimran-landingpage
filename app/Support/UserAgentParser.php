<?php

namespace App\Support;

/**
 * Deliberately not a package (jenssegers/agent et al.) — a personal
 * analytics dashboard doesn't need pixel-perfect UA parsing, and this
 * covers the common browsers/OSes/device types in a few lines with no
 * new dependency.
 */
class UserAgentParser
{
    public static function browser(string $userAgent): string
    {
        return match (true) {
            str_contains($userAgent, 'Edg/') => 'Edge',
            str_contains($userAgent, 'OPR/') || str_contains($userAgent, 'Opera') => 'Opera',
            str_contains($userAgent, 'Chrome/') && ! str_contains($userAgent, 'Chromium') => 'Chrome',
            str_contains($userAgent, 'Firefox/') => 'Firefox',
            str_contains($userAgent, 'Safari/') && ! str_contains($userAgent, 'Chrome') => 'Safari',
            default => 'Other',
        };
    }

    public static function os(string $userAgent): string
    {
        return match (true) {
            str_contains($userAgent, 'Windows') => 'Windows',
            str_contains($userAgent, 'iPhone') || str_contains($userAgent, 'iPad') => 'iOS',
            str_contains($userAgent, 'Mac OS X') => 'macOS',
            str_contains($userAgent, 'Android') => 'Android',
            str_contains($userAgent, 'Linux') => 'Linux',
            default => 'Other',
        };
    }

    public static function deviceType(string $userAgent): string
    {
        return match (true) {
            str_contains($userAgent, 'iPad') || str_contains($userAgent, 'Tablet') => 'tablet',
            str_contains($userAgent, 'Mobi') || str_contains($userAgent, 'iPhone') || str_contains($userAgent, 'Android') => 'mobile',
            default => 'desktop',
        };
    }

    public static function isBot(string $userAgent): bool
    {
        return (bool) preg_match('/bot|crawl|slurp|spider|facebookexternalhit|bingpreview|headless/i', $userAgent);
    }
}
