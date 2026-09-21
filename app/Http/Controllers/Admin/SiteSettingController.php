<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateSiteSettingRequest;
use App\Models\SiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SiteSettingController extends Controller
{
    public function edit(): View
    {
        return view('admin.site-settings.edit', ['setting' => SiteSetting::current()]);
    }

    public function update(UpdateSiteSettingRequest $request): RedirectResponse
    {
        $setting = SiteSetting::current();
        $data = $request->validated();

        if ($request->hasFile('profile_photo')) {
            if ($setting->profile_photo) {
                Storage::disk('public')->delete($setting->profile_photo);
            }

            $file = $request->file('profile_photo');
            $filename = Str::uuid().'.'.$file->getClientOriginalExtension();
            $data['profile_photo'] = $file->storeAs('profile', $filename, 'public');
        }

        $setting->update($data);

        return redirect()->route('admin.site-settings.edit')->with('status', 'Profile updated.');
    }
}
