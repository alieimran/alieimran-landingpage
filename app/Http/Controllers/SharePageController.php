<?php

namespace App\Http\Controllers;

use App\Models\AnalyticsEvent;
use App\Models\SharePage;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SharePageController extends Controller
{
    public function show(SharePage $sharePage): View
    {
        abort_unless($sharePage->enabled, 404);

        return view('share-page', ['page' => $sharePage]);
    }

    /**
     * Destination always comes from our own record, never from input,
     * so this can't become an open redirect (same as LinkClickController).
     */
    public function go(SharePage $sharePage): RedirectResponse
    {
        abort_unless($sharePage->enabled, 404);

        AnalyticsEvent::create([
            'event_type' => 'share_page_click',
            'label' => $sharePage->title,
            'url' => $sharePage->target_url,
        ]);

        return redirect()->away($sharePage->target_url);
    }
}
