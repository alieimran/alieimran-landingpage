<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class HolidayStateController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'holiday_extra_state' => ['nullable', 'string', Rule::in(array_keys(config('holidays.state_names')))],
        ]);

        SiteSetting::current()->update(['holiday_extra_state' => $data['holiday_extra_state'] ?: null]);

        return redirect()->route('admin.dashboard')->with('status', $data['holiday_extra_state']
            ? 'Now also showing holidays for '.config('holidays.state_names.'.$data['holiday_extra_state']).'.'
            : 'Showing holidays for your prayer-zone state only.');
    }
}
