<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreLinkRequest;
use App\Http\Requests\Admin\UpdateLinkRequest;
use App\Models\Link;
use App\Models\LinkCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class LinkController extends Controller
{
    public function index(): View
    {
        $links = Link::with('category')->orderBy('sort_order')->paginate(20);

        return view('admin.links.index', ['links' => $links]);
    }

    public function create(): View
    {
        return view('admin.links.create', ['categories' => LinkCategory::orderBy('name')->get()]);
    }

    public function store(StoreLinkRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $data['image'] = $this->storeImage($request->file('image'));
        }

        Link::create($data);

        return redirect()->route('admin.links.index')->with('status', 'Link created.');
    }

    public function edit(Link $link): View
    {
        return view('admin.links.edit', [
            'link' => $link,
            'categories' => LinkCategory::orderBy('name')->get(),
        ]);
    }

    public function update(UpdateLinkRequest $request, Link $link): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            if ($link->image) {
                Storage::disk('public')->delete($link->image);
            }

            $data['image'] = $this->storeImage($request->file('image'));
        }

        $link->update($data);

        return redirect()->route('admin.links.index')->with('status', 'Link updated.');
    }

    public function destroy(Link $link): RedirectResponse
    {
        if ($link->image) {
            Storage::disk('public')->delete($link->image);
        }

        $link->delete();

        return redirect()->route('admin.links.index')->with('status', 'Link deleted.');
    }

    private function storeImage(UploadedFile $file): string
    {
        $filename = Str::uuid().'.'.$file->getClientOriginalExtension();

        return $file->storeAs('links', $filename, 'public');
    }
}
