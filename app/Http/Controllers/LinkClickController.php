<?php

namespace App\Http\Controllers;

use App\Models\AnalyticsEvent;
use App\Models\Link;
use App\Models\SocialLink;
use Illuminate\Http\RedirectResponse;

class LinkClickController extends Controller
{
    /**
     * Both destinations are always read from our own database record,
     * never from user-supplied input, so this can't become an open
     * redirect regardless of what {link}/{socialLink} resolves to.
     */
    public function link(Link $link): RedirectResponse
    {
        AnalyticsEvent::create([
            'event_type' => 'link_click',
            'label' => $link->title,
            'url' => $link->url,
        ]);

        return redirect()->away($link->url);
    }

    public function social(SocialLink $socialLink): RedirectResponse
    {
        AnalyticsEvent::create([
            'event_type' => 'social_click',
            'label' => $socialLink->display_name ?? $socialLink->platform,
            'url' => $socialLink->url,
        ]);

        return redirect()->away($socialLink->url);
    }
}
