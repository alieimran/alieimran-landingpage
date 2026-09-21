<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactInquiry;
use App\Models\Link;
use App\Models\SocialLink;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.dashboard', [
            'linkCount' => Link::query()->count(),
            'socialLinkCount' => SocialLink::query()->count(),
            'newInquiryCount' => ContactInquiry::query()->where('status', 'new')->count(),
        ]);
    }
}
