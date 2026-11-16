<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use App\Support\PrayerZones;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PrayerZoneController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'prayer_zone' => ['required', 'string', Rule::in(PrayerZones::codes())],
        ]);

        SiteSetting::current()->update(['prayer_zone' => $data['prayer_zone']]);
        $zone = PrayerZones::describe($data['prayer_zone']);

        return redirect()->route('admin.dashboard')->with('status', 'Prayer zone set to '.$data['prayer_zone'].' · '.$zone['state'].'.');
    }
}
