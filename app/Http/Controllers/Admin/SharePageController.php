<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SharePageRequest;
use App\Models\SharePage;
use App\Support\ImageCompressor;
use App\Support\LinkPreview;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SharePageController extends Controller
{
    public function index(): View
    {
        return view('admin.share-pages.index', ['pages' => SharePage::latest()->paginate(20)]);
    }

    public function create(): View
    {
        return view('admin.share-pages.create');
    }

    public function store(SharePageRequest $request): RedirectResponse
    {
        $page = new SharePage;
        $this->save($request, $page);

        return redirect()->route('admin.share-pages.edit', $page)->with('status', 'Share page created.');
    }

    public function edit(SharePage $sharePage): View
    {
        return view('admin.share-pages.edit', ['page' => $sharePage]);
    }

    public function update(SharePageRequest $request, SharePage $sharePage): RedirectResponse
    {
        $this->save($request, $sharePage);

        return redirect()->route('admin.share-pages.edit', $sharePage)->with('status', 'Share page updated.');
    }

    public function destroy(SharePage $sharePage): RedirectResponse
    {
        Storage::disk('public')->delete($sharePage->images ?? []);
        $sharePage->delete();

        return redirect()->route('admin.share-pages.index')->with('status', 'Share page deleted.');
    }

    private function save(SharePageRequest $request, SharePage $page): void
    {
        $data = $request->safe()->except(['images', 'remove_images', 'refresh_preview']);

        // Only paths that actually belong to this page can be removed,
        // so a tampered remove_images[] can't delete arbitrary files.
        $remove = array_intersect($page->images ?? [], $request->input('remove_images', []));
        Storage::disk('public')->delete($remove);

        $images = array_values(array_diff($page->images ?? [], $remove));

        foreach ($request->file('images', []) as $file) {
            $images[] = ImageCompressor::store($file, 'share-pages');
        }

        $data['images'] = $images;

        if (! $page->exists || $page->target_url !== $data['target_url'] || $request->boolean('refresh_preview')) {
            $data['preview_image_url'] = LinkPreview::image($data['target_url']);
        }

        $page->fill($data)->save();
    }
}
