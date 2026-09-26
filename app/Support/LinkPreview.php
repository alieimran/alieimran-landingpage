<?php

namespace App\Support;

use Illuminate\Support\Facades\Http;
use Throwable;

class LinkPreview
{
    /**
     * Best-effort lookup of a page's og:image, e.g. the cover photo of
     * a public Google Photos album. Returns null on any failure — a
     * missing preview must never block saving a share page.
     */
    public static function image(string $url): ?string
    {
        try {
            // Google Photos only serves Open Graph tags to crawlers, so
            // identify as one (the same UA link unfurlers use).
            $response = Http::timeout(8)
                ->withUserAgent('facebookexternalhit/1.1')
                ->get($url);
        } catch (Throwable) {
            return null;
        }

        if (! $response->successful()) {
            return null;
        }

        if (! preg_match('/<meta[^>]+property=["\']og:image["\'][^>]+content=["\']([^"\']+)["\']/i', $response->body(), $match)) {
            return null;
        }

        $image = html_entity_decode($match[1], ENT_QUOTES | ENT_HTML5);

        if (! str_starts_with($image, 'https://')) {
            return null;
        }

        // Google-hosted images take their size from a URL suffix like
        // "=w600-h315-p-k"; ask for a larger, uncropped version instead.
        if (str_contains(parse_url($image, PHP_URL_HOST) ?? '', 'googleusercontent.com')) {
            $image = preg_replace('/=[a-z0-9-]+$/i', '', $image).'=w1600';
        }

        return $image;
    }
}
