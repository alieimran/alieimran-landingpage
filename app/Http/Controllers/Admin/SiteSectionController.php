<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SiteSectionController extends Controller
{
    /**
     * No create/delete: section keys correspond 1:1 to hard-coded
     * conditional blocks in home.blade.php, so admins can toggle,
     * reorder, and relabel the five that exist but not invent new
     * ones the template wouldn't know how to render.
     */
    public function index(): View
    {
        return view('admin.sections.index', [
            'sections' => SiteSection::orderBy('sort_order')->get(),
        ]);
    }

    public function update(Request $request, SiteSection $section): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
            'enabled' => ['boolean'],
            'nav_visible' => ['boolean'],
            'homepage_visible' => ['boolean'],
            'sort_order' => ['integer', 'min:0'],
        ]);

        $section->update($data);

        return redirect()->route('admin.sections.index')->with('status', "Section '{$section->title}' updated.");
    }
}
