<?php

namespace App\Http\Controllers;

use App\Models\SiteSetting;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    /**
     * Only lists public, indexable Root pages. Admin/auth routes are
     * excluded (per SRS §42), and /contact is excluded because it's
     * marked noindex on the page itself.
     */
    public function __invoke(): Response
    {
        $lastmod = SiteSetting::current()->updated_at?->toAtomString() ?? now()->toAtomString();

        $urls = [
            ['loc' => url('/'), 'lastmod' => $lastmod, 'priority' => '1.0'],
        ];

        $xml = view('sitemap', ['urls' => $urls])->render();

        return response($xml, 200)->header('Content-Type', 'application/xml; charset=UTF-8');
    }
}
