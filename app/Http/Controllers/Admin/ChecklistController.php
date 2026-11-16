<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ChecklistItemRequest;
use App\Http\Requests\Admin\ChecklistSharingRequest;
use App\Http\Requests\Admin\StoreChecklistRequest;
use App\Http\Requests\Admin\UpdateChecklistRequest;
use App\Models\Checklist;
use App\Models\ChecklistItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ChecklistController extends Controller
{
    public function index(): View
    {
        $checklists = Checklist::with('items')->orderBy('sort_order')->orderBy('id')->get();

        return view('admin.checklists.index', [
            'checklists' => $checklists,
            'templates' => config('checklists.templates'),
            'openItems' => $checklists->sum(fn (Checklist $c) => $c->items->where('is_done', false)->count()),
        ]);
    }

    public function store(StoreChecklistRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $template = isset($data['template']) ? config('checklists.templates.'.$data['template']) : null;

        $checklist = DB::transaction(function () use ($data, $template) {
            $checklist = Checklist::create([
                'title' => $data['title'] ?? $template['title'],
                'slug' => $data['slug'] ?? null,
                'description' => $data['description'] ?? ($template['description'] ?? null),
                'sort_order' => (int) Checklist::max('sort_order') + 1,
            ]);

            foreach (($template['items'] ?? []) as $i => $item) {
                [$title, $url, $note] = is_array($item) ? [$item[0], $item[1] ?? null, $item[2] ?? null] : [$item, null, null];
                $checklist->items()->create(['title' => $title, 'url' => $url, 'note' => $note, 'sort_order' => $i + 1]);
            }

            return $checklist;
        });

        return redirect()->to(route('admin.checklists.index').'#list-'.$checklist->id)->with('status', 'Checklist "'.$checklist->title.'" created.');
    }

    public function update(UpdateChecklistRequest $request, Checklist $checklist): RedirectResponse
    {
        $checklist->update($request->validated());

        return back()->with('status', 'Checklist updated.');
    }

    /** Set the personalised link and switch read-only public sharing on or off. */
    public function updateSharing(ChecklistSharingRequest $request, Checklist $checklist): RedirectResponse
    {
        $checklist->update($request->validated());

        return back()->with('status', match (true) {
            $checklist->visitorsCanTick() => 'Shared. Anyone with the link can view and tick items: '.$checklist->publicUrl(),
            $checklist->is_public => 'Shared as read-only: '.$checklist->publicUrl(),
            default => 'Link saved. This checklist is private.',
        });
    }

    public function destroy(Checklist $checklist): RedirectResponse
    {
        $checklist->delete(); // items are removed by the foreign key cascade

        return redirect()->route('admin.checklists.index')->with('status', 'Checklist deleted.');
    }

    /** Uncheck everything so a recurring checklist can be used again. */
    public function reset(Checklist $checklist): RedirectResponse
    {
        $checklist->items()->update(['is_done' => false, 'done_at' => null]);

        return back()->with('status', 'All items unchecked.');
    }

    public function storeItem(ChecklistItemRequest $request, Checklist $checklist): RedirectResponse
    {
        $checklist->items()->create([...$request->validated(), 'sort_order' => (int) $checklist->items()->max('sort_order') + 1]);

        return back()->with('status', 'Item added.');
    }

    public function updateItem(ChecklistItemRequest $request, ChecklistItem $item): RedirectResponse
    {
        $item->update($request->validated());

        return back()->with('status', 'Item updated.');
    }

    /** Redirects for plain form posts; returns JSON for the in-page toggle so the page need not reload. */
    public function toggleItem(Request $request, ChecklistItem $item): RedirectResponse|JsonResponse
    {
        $done = ! $item->is_done;
        $item->update(['is_done' => $done, 'done_at' => $done ? now() : null]);

        if ($request->expectsJson()) {
            return response()->json([
                'is_done' => $done,
                'progress' => $item->checklist->load('items')->progress(),
                'open_items' => ChecklistItem::where('is_done', false)->count(),
            ]);
        }

        return back();
    }

    public function destroyItem(ChecklistItem $item): RedirectResponse
    {
        $item->delete();

        return back()->with('status', 'Item removed.');
    }
}
