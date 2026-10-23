<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class MaintenanceController extends Controller
{
    public function edit(): View
    {
        return view('admin.maintenance.edit', ['setting' => SiteSetting::current()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $request->merge(['maintenance_enabled' => $request->boolean('maintenance_enabled')]);
        $data = $request->validate([
            'maintenance_enabled' => ['required', 'boolean'],
            'maintenance_reason' => ['required_if:maintenance_enabled,true', 'nullable', 'string', 'max:2000'],
            'maintenance_until' => [Rule::requiredIf($request->boolean('maintenance_enabled') && ! $request->filled('maintenance_duration')), 'nullable', 'date_format:Y-m-d\TH:i'],
            'maintenance_duration' => ['nullable', Rule::in(array_keys(config('maintenance.durations')))],
            'maintenance_content' => ['sometimes', 'array:label,heading,button_text,footer,delayed_message,show_logo,show_start,show_return,show_theme_toggle,show_countdown,show_spinner'],
            'maintenance_content.label' => ['sometimes', 'required', 'string', 'max:100'],
            'maintenance_content.heading' => ['sometimes', 'required', 'string', 'max:200'],
            'maintenance_content.button_text' => ['sometimes', 'required', 'string', 'max:60'],
            'maintenance_content.footer' => ['nullable', 'string', 'max:300'],
            'maintenance_content.delayed_message' => ['sometimes', 'required', 'string', 'max:500'],
            'maintenance_content.show_countdown' => ['sometimes', 'boolean'],
            'maintenance_content.show_spinner' => ['sometimes', 'boolean'],
            'maintenance_content.show_logo' => ['sometimes', 'boolean'],
            'maintenance_content.show_start' => ['sometimes', 'boolean'],
            'maintenance_content.show_return' => ['sometimes', 'boolean'],
            'maintenance_content.show_theme_toggle' => ['sometimes', 'boolean'],
        ]);
        $until = filled($data['maintenance_until'] ?? null)
            ? Carbon::createFromFormat('Y-m-d\TH:i', $data['maintenance_until'], 'Asia/Kuala_Lumpur')->setTimezone(config('app.timezone')) : null;
        if (filled($data['maintenance_duration'] ?? null)) {
            $until = now()->addMinutes((int) $data['maintenance_duration']);
        }
        unset($data['maintenance_duration']);
        if ($data['maintenance_enabled'] && (! $until || ! $until->isFuture())) {
            throw ValidationException::withMessages(['maintenance_until' => 'Choose a future expected return time in Malaysia time.']);
        }
        $setting = SiteSetting::current();
        $data['maintenance_until'] = $until;
        $data['maintenance_started_at'] = $data['maintenance_enabled']
            ? ($setting->maintenance_enabled ? $setting->maintenance_started_at : now()) : null;
        $setting->update($data);

        return redirect()->route('admin.maintenance.edit')->with('status', $data['maintenance_enabled'] ? 'Maintenance mode enabled. Visitors now see the maintenance page.' : 'Maintenance mode disabled. Your website is live.');
    }

    public function preview(): View
    {
        return view('maintenance', ['setting' => SiteSetting::current()]);
    }
}
