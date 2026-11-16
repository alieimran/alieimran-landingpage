<?php

namespace App\Http\Controllers;

use App\Models\Checklist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PublicChecklistController extends Controller
{
    public function __invoke(string $slug): Response
    {
        // Private lists are indistinguishable from lists that do not exist.
        $checklist = Checklist::where('slug', $slug)->where('is_public', true)->with('items')->firstOrFail();

        return response()
            ->view('checklist-public', ['checklist' => $checklist, 'progress' => $checklist->progress()])
            ->header('X-Robots-Tag', 'noindex, nofollow')
            ->header('Cache-Control', 'no-cache, private');
    }

    /**
     * Tick or untick one item from the shared link. The only write allowed from the public side, and only when the
     * owner switched ticking on. The item is looked up through the list, so an id from another list cannot be reached.
     */
    public function toggle(Request $request, string $slug, int $item): JsonResponse|RedirectResponse
    {
        $checklist = Checklist::where('slug', $slug)->where('is_public', true)->where('allow_ticking', true)->firstOrFail();
        $row = $checklist->items()->findOrFail($item);

        $done = ! $row->is_done;
        $row->update(['is_done' => $done, 'done_at' => $done ? now() : null]);

        if ($request->expectsJson()) {
            return response()->json(['is_done' => $done, 'progress' => $checklist->load('items')->progress()]);
        }

        return back();
    }
}
