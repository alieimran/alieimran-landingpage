<?php

namespace App\Console\Commands;

use App\Models\PageView;
use Illuminate\Console\Command;

class PruneVisitorDetailsCommand extends Command
{
    protected $signature = 'analytics:prune-visitor-details';

    protected $description = 'Remove public IPs and locations after 30 days while retaining aggregate analytics';

    public function handle(): int
    {
        $count = PageView::where('created_at', '<', now()->subDays(30))
            ->where(fn ($query) => $query->whereNotNull('ip_address')->orWhereNotNull('location'))
            ->update(['ip_address' => null, 'location' => null, 'geo_checked_at' => null]);
        $this->info("Cleared visitor details on {$count} records.");

        return self::SUCCESS;
    }
}
