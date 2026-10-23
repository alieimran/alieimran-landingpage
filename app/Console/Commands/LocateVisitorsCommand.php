<?php

namespace App\Console\Commands;

use App\Models\PageView;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Throwable;

class LocateVisitorsCommand extends Command
{
    protected $signature = 'analytics:locate';

    protected $description = 'Resolve approximate public-IP locations for recent visits (up to 20 IPs per run)';

    public function handle(): int
    {
        $cutoff = now()->subDays(30);
        $ips = PageView::where('created_at', '>=', $cutoff)->whereNotNull('ip_address')->whereNull('location')
            ->where(fn ($query) => $query->whereNull('geo_checked_at')->orWhere('geo_checked_at', '<', now()->subDay()))
            ->select('ip_address')->distinct()->limit(20)->pluck('ip_address');
        foreach ($ips as $ip) {
            if (! filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_GLOBAL_RANGE)) {
                continue;
            }
            $location = PageView::where('ip_address', $ip)->where('created_at', '>=', $cutoff)->whereNotNull('location')->value('location');
            if (! $location) {
                try {
                    $response = Http::connectTimeout(2)->timeout(4)->get('https://ipwho.is/'.rawurlencode($ip), ['fields' => 'ip,success,city,region,country']);
                    if ($response->status() === 429) {
                        break;
                    }
                    if ($response->successful() && $response->json('success') === true && $response->json('ip') === $ip) {
                        $parts = array_filter([$response->json('city'), $response->json('region'), $response->json('country')], fn ($part) => is_string($part) && $part !== '');
                        $location = mb_substr(implode(', ', array_unique($parts)), 0, 255) ?: null;
                    }
                } catch (Throwable) {
                    // Retry unavailable lookups tomorrow; never slow public page loads.
                }
            }
            PageView::where('ip_address', $ip)->where('created_at', '>=', $cutoff)->whereNull('location')
                ->update(['location' => $location, 'geo_checked_at' => now()]);
        }
        $this->info('Visitor location lookup completed.');

        return self::SUCCESS;
    }
}
