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
     *
     * Both also 404 for a disabled/expired link rather than silently
     * redirecting anyway — otherwise a bookmarked or previously-indexed
     * /go/ URL would keep working (and keep logging clicks) for a link
     * an admin believed they'd hidden, which is the same disabled-but-
     * still-reachable inconsistency the digital card page's abort
     * already guards against.
     */
    public function link(Link $link): RedirectResponse
    {
        abort_unless(Link::visible()->whereKey($link->id)->exists(), 404);

        AnalyticsEvent::create([
            'event_type' => 'link_click',
            'label' => $link->title,
            'url' => $link->url,
        ]);

        return redirect()->away($link->url);
    }

    public function social(SocialLink $socialLink): RedirectResponse
    {
        abort_unless($socialLink->enabled, 404);

        AnalyticsEvent::create([
            'event_type' => 'social_click',
            'label' => $socialLink->display_name ?? $socialLink->platform,
            'url' => $socialLink->url,
        ]);

        return redirect()->away($socialLink->url);
    }
}
