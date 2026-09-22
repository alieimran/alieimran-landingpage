<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateThemeSettingsRequest;
use App\Models\ThemeSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ThemeController extends Controller
{
    public function edit(): View
    {
        return view('admin.theme.edit', ['theme' => ThemeSetting::current()]);
    }

    public function update(UpdateThemeSettingsRequest $request): RedirectResponse
    {
        $theme = ThemeSetting::current();
        $data = ['primary_color' => $request->validated()['primary_color']];

        if ($request->hasFile('logo')) {
            if ($theme->logo_path) {
                Storage::disk('public')->delete($theme->logo_path);
            }

            $file = $request->file('logo');
            $data['logo_path'] = $file->storeAs('theme', Str::uuid().'.'.$file->getClientOriginalExtension(), 'public');
        }

        if ($request->hasFile('favicon')) {
            if ($theme->favicon_path) {
                Storage::disk('public')->delete($theme->favicon_path);
            }

            $file = $request->file('favicon');
            $data['favicon_path'] = $file->storeAs('theme', Str::uuid().'.'.$file->getClientOriginalExtension(), 'public');
        }

        $theme->update($data);

        return redirect()->route('admin.theme.edit')->with('status', 'Theme updated.');
    }
}
