<?php

namespace App\Http\Controllers;

use App\Models\Link;
use App\Models\SeoMetadata;
use App\Models\SiteSection;
use App\Models\SiteSetting;
use App\Models\SocialLink;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $sections = SiteSection::onHomepage()->get()->keyBy('key');

        return view('home', [
            'sections' => $sections,
            'profile' => SiteSetting::current(),
            'seo' => SeoMetadata::forPage('home'),
            'featuredLinks' => Link::visible()->featured()->ordered()->get(),
            'links' => Link::visible()->where('featured', false)->with('category')->ordered()->get(),
            'socialLinks' => SocialLink::visible()->get(),
        ]);
    }
}
