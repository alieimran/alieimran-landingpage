<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSocialLinkRequest;
use App\Http\Requests\Admin\UpdateSocialLinkRequest;
use App\Models\SocialLink;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SocialLinkController extends Controller
{
    public function index(): View
    {
        return view('admin.social-links.index', [
            'socialLinks' => SocialLink::orderBy('sort_order')->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('admin.social-links.create');
    }

    public function store(StoreSocialLinkRequest $request): RedirectResponse
    {
        SocialLink::create($request->validated());

        return redirect()->route('admin.social-links.index')->with('status', 'Social link created.');
    }

    public function edit(SocialLink $socialLink): View
    {
        return view('admin.social-links.edit', ['socialLink' => $socialLink]);
    }

    public function update(UpdateSocialLinkRequest $request, SocialLink $socialLink): RedirectResponse
    {
        $socialLink->update($request->validated());

        return redirect()->route('admin.social-links.index')->with('status', 'Social link updated.');
    }

    public function destroy(SocialLink $socialLink): RedirectResponse
    {
        $socialLink->delete();

        return redirect()->route('admin.social-links.index')->with('status', 'Social link deleted.');
    }
}
