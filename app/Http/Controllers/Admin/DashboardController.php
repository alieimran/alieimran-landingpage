<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AnalyticsEvent;
use App\Models\ChecklistItem;
use App\Models\ContactInquiry;
use App\Models\Link;
use App\Models\PageView;
use App\Models\SiteSetting;
use App\Models\SocialLink;
use App\Services\DependencyAdvisories;
use App\Services\HijriCalendar;
use App\Services\Holidays;
use App\Services\PrayerTimes;
use App\Services\SystemStatus;
use App\Support\PrayerZones;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    private const MYT = 'Asia/Kuala_Lumpur';

    public function __invoke(Request $request, PrayerTimes $prayers, SystemStatus $system, DependencyAdvisories $advisories, Holidays $holidays): View
    {
        $now = now(self::MYT);
        $start = $now->copy()->subDays(59)->startOfDay();

        $views = PageView::where('created_at', '>=', $start)
            ->selectRaw('DATE(created_at) as d, COUNT(*) as views, COUNT(DISTINCT visitor_hash) as uniques')
            ->groupBy('d')->get()->keyBy('d');
        $events = AnalyticsEvent::where('created_at', '>=', $start)
            ->selectRaw('DATE(created_at) as d, COUNT(*) as total')
            ->groupBy('d')->pluck('total', 'd');

        // 60 daily points (oldest -> newest): the chart shows the last 7/14/30 and compares with the period before.
        $series = collect(range(59, 0))->map(function (int $ago) use ($now, $views, $events) {
            $date = $now->copy()->subDays($ago);
            $key = $date->format('Y-m-d');

            return [
                'date' => $key,
                'label' => $date->format('D j M'),
                'views' => (int) ($views[$key]->views ?? 0),
                'visitors' => (int) ($views[$key]->uniques ?? 0),
                'clicks' => (int) ($events[$key] ?? 0),
            ];
        })->values();

        $sum = fn (string $metric, int $from, int $to) => $series->slice($from, $to - $from)->sum($metric);
        $kpis = collect(['views' => 'Page views', 'visitors' => 'Visitors', 'clicks' => 'Link clicks'])->map(fn ($label, $metric) => [
            'label' => $label,
            'value' => $sum($metric, 53, 60),
            'previous' => $sum($metric, 46, 53),
            'spark' => $series->slice(53, 7)->pluck($metric)->values()->all(),
        ]);

        $since30 = $now->copy()->subDays(29)->startOfDay();
        $topPages = PageView::where('created_at', '>=', $since30)->selectRaw('path, COUNT(*) as total')
            ->groupBy('path')->orderByDesc('total')->take(5)->pluck('total', 'path');
        $topReferrers = PageView::whereNotNull('referrer')->where('referrer', 'not like', '%'.$request->getHost().'%')
            ->where('created_at', '>=', $since30)->pluck('referrer')
            ->map(fn ($r) => parse_url($r, PHP_URL_HOST) ?: $r)->countBy()->sortDesc()->take(5);
        $devices = PageView::whereNotNull('device_type')->where('created_at', '>=', $since30)
            ->selectRaw('device_type, COUNT(*) as total')->groupBy('device_type')->orderByDesc('total')->pluck('total', 'device_type');

        $components = $system->components();
        $report = $advisories->report();
        $needsAttention = collect($components)->filter(fn ($c) => in_array($c['tone'], ['amber', 'red'], true))->count();
        $maintenance = SiteSetting::current()->maintenance_enabled;
        $newInquiries = ContactInquiry::where('status', 'new')->count();
        $times = $prayers->today();
        $hijri = new HijriCalendar($times['hijri'] ?? null);

        return view('admin.dashboard', [
            'greeting' => match (true) {
                $now->hour < 5 => 'Working late',
                $now->hour < 12 => 'Good morning',
                $now->hour < 18 => 'Good afternoon',
                default => 'Good evening',
            },
            'today' => $now->format('l, j F Y'),
            'series' => $series,
            'kpis' => $kpis,
            'topPages' => $topPages,
            'topReferrers' => $topReferrers,
            'devices' => $devices,
            'inquiries' => ContactInquiry::latest()->take(5)->get(),
            'newInquiries' => $newInquiries,
            'openChecklistItems' => ChecklistItem::where('is_done', false)->count(),
            'linkCount' => Link::count(),
            'socialLinkCount' => SocialLink::count(),
            'maintenance' => $maintenance,
            'nationalHolidays' => $holidays->national(3),
            'stateHolidays' => $holidays->state(4),
            'stateNames' => $holidays->stateNames(),
            'extraStateCode' => $holidays->extraStateCode(),
            'primaryStateCode' => $holidays->stateCode(),
            'stateOptions' => config('holidays.state_names'),
            'hijriToday' => $hijri->today(),
            'hijriOccasions' => $hijri->upcoming(4),
            'prayers' => $times,
            'zoneInfo' => PrayerZones::describe($times['zone']),
            'zoneGroups' => PrayerZones::grouped(),
            'nextPrayer' => $this->nextPrayer($times, $now),
            'components' => $components,
            'advisories' => $report,
            'needsAttention' => $needsAttention,
            'attention' => array_values(array_filter([
                $maintenance ? ['tone' => 'amber', 'text' => 'Maintenance mode is on — visitors see the maintenance page.', 'href' => route('admin.maintenance.edit'), 'cta' => 'Manage'] : null,
                $newInquiries ? ['tone' => 'emerald', 'text' => $newInquiries.' new '.str('inquiry')->plural($newInquiries).' waiting for a reply.', 'href' => route('admin.contact-inquiries.index', ['status' => 'new']), 'cta' => 'Open inbox'] : null,
                count($report['findings']) ? ['tone' => 'red', 'text' => count($report['findings']).' known dependency '.str('advisory')->plural(count($report['findings'])).'.', 'href' => '#system', 'cta' => 'Review'] : null,
                $needsAttention ? ['tone' => 'amber', 'text' => $needsAttention.' system '.str('component')->plural($needsAttention).' need an update or review.', 'href' => '#system', 'cta' => 'Review'] : null,
            ])),
        ]);
    }

    /** @return array{label:string,time:string,minutes:int}|null */
    private function nextPrayer(array $prayers, Carbon $now): ?array
    {
        foreach (['Subuh', 'Syuruk', 'Zohor', 'Asar', 'Maghrib', 'Isyak'] as $name) {
            $raw = $prayers['times'][$name] ?? null;
            if (! $raw || ! preg_match('/^\d{1,2}:\d{2}/', $raw)) {
                continue;
            }
            $at = Carbon::createFromFormat('H:i', substr($raw, 0, 5), self::MYT)->setDateFrom($now);
            if ($at->isFuture()) {
                return ['label' => $name, 'time' => substr($raw, 0, 5), 'minutes' => (int) $now->diffInMinutes($at, true)];
            }
        }

        return null;
    }
}
