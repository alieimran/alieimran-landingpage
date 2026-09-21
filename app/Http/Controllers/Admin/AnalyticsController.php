<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AnalyticsEvent;
use App\Models\PageView;
use Illuminate\View\View;

class AnalyticsController extends Controller
{
    public function __invoke(): View
    {
        $now = now();
        $today = $now->copy()->startOfDay();
        $sevenDaysAgo = $now->copy()->subDays(6)->startOfDay();
        $thirtyDaysAgo = $now->copy()->subDays(29)->startOfDay();

        $summary = [
            'total' => PageView::count(),
            'today' => PageView::where('created_at', '>=', $today)->count(),
            'last7' => PageView::where('created_at', '>=', $sevenDaysAgo)->count(),
            'last30' => PageView::where('created_at', '>=', $thirtyDaysAgo)->count(),
            'unique_today' => PageView::where('created_at', '>=', $today)->distinct('visitor_hash')->count('visitor_hash'),
        ];

        $dailyCounts = PageView::selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->where('created_at', '>=', $now->copy()->subDays(13)->startOfDay())
            ->groupBy('day')
            ->pluck('total', 'day');

        $dailySeries = collect(range(13, 0))->map(function (int $daysAgo) use ($now, $dailyCounts) {
            $date = $now->copy()->subDays($daysAgo);

            return [
                'label' => $date->format('D'),
                'date' => $date->format('Y-m-d'),
                'count' => (int) ($dailyCounts[$date->format('Y-m-d')] ?? 0),
            ];
        });

        $topReferrers = PageView::whereNotNull('referrer')
            ->where('referrer', 'not like', '%'.request()->getHost().'%')
            ->where('created_at', '>=', $thirtyDaysAgo)
            ->get()
            ->map(fn (PageView $view) => parse_url($view->referrer, PHP_URL_HOST) ?? $view->referrer)
            ->countBy()
            ->sortDesc()
            ->take(6);

        $browsers = PageView::whereNotNull('browser')
            ->where('created_at', '>=', $thirtyDaysAgo)
            ->selectRaw('browser, COUNT(*) as total')
            ->groupBy('browser')
            ->orderByDesc('total')
            ->pluck('total', 'browser');

        $devices = PageView::whereNotNull('device_type')
            ->where('created_at', '>=', $thirtyDaysAgo)
            ->selectRaw('device_type, COUNT(*) as total')
            ->groupBy('device_type')
            ->orderByDesc('total')
            ->pluck('total', 'device_type');

        $topPages = PageView::where('created_at', '>=', $thirtyDaysAgo)
            ->selectRaw('path, COUNT(*) as total')
            ->groupBy('path')
            ->orderByDesc('total')
            ->take(6)
            ->pluck('total', 'path');

        $topInteractions = AnalyticsEvent::where('created_at', '>=', $thirtyDaysAgo)
            ->selectRaw('label, event_type, COUNT(*) as total')
            ->groupBy('label', 'event_type')
            ->orderByDesc('total')
            ->take(8)
            ->get();

        $recentViews = PageView::latest()->take(8)->get();

        return view('admin.analytics.index', [
            'summary' => $summary,
            'dailySeries' => $dailySeries,
            'topReferrers' => $topReferrers,
            'browsers' => $browsers,
            'devices' => $devices,
            'topPages' => $topPages,
            'topInteractions' => $topInteractions,
            'recentViews' => $recentViews,
            'retentionNote' => PageView::query()->oldest('created_at')->value('created_at'),
        ]);
    }
}
