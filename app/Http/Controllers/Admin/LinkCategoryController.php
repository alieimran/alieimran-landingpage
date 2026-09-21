<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LinkCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class LinkCategoryController extends Controller
{
    public function index(): View
    {
        return view('admin.link-categories.index', [
            'categories' => LinkCategory::withCount('links')->orderBy('sort_order')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sort_order' => ['integer', 'min:0'],
        ]);

        $data['slug'] = $this->uniqueSlug($data['name']);

        LinkCategory::create($data);

        return redirect()->route('admin.link-categories.index')->with('status', 'Category created.');
    }

    public function update(Request $request, LinkCategory $linkCategory): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sort_order' => ['integer', 'min:0'],
        ]);

        if ($data['name'] !== $linkCategory->name) {
            $data['slug'] = $this->uniqueSlug($data['name'], $linkCategory->id);
        }

        $linkCategory->update($data);

        return redirect()->route('admin.link-categories.index')->with('status', 'Category updated.');
    }

    public function destroy(LinkCategory $linkCategory): RedirectResponse
    {
        $linkCategory->delete();

        return redirect()->route('admin.link-categories.index')->with('status', 'Category deleted.');
    }

    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $counter = 1;

        while (
            LinkCategory::where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()
        ) {
            $slug = "{$base}-{$counter}";
            $counter++;
        }

        return $slug;
    }
}
