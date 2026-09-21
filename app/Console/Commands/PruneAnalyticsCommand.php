<?php

namespace App\Console\Commands;

use App\Models\AnalyticsEvent;
use App\Models\PageView;
use Illuminate\Console\Command;

class PruneAnalyticsCommand extends Command
{
    protected $signature = 'analytics:prune {--days=90 : Delete records older than this many days}';

    protected $description = 'Delete page view and interaction records older than the retention window (SRS §38: retention should be limited where practical)';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $cutoff = now()->subDays($days);

        $views = PageView::where('created_at', '<', $cutoff)->delete();
        $events = AnalyticsEvent::where('created_at', '<', $cutoff)->delete();

        $this->info("Deleted {$views} page view(s) and {$events} event(s) older than {$days} days.");

        return self::SUCCESS;
    }
}
