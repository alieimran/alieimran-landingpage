<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// SRS §38: keep analytics retention bounded. Requires the server's
// cron to call `php artisan schedule:run` every minute in production
// (standard Laravel deployment practice) for this to actually fire.
Schedule::command('analytics:prune')->monthly();

Schedule::command('analytics:locate')->hourly()->withoutOverlapping();
Schedule::command('analytics:prune-visitor-details')->daily()->withoutOverlapping();
