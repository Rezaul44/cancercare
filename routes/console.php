<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule nightly sitemap generation
Schedule::command('sitemap:generate')->dailyAt('02:00');

// Phase 5.4: Patient Case Expiration and Notification Schedules
Schedule::job(new \App\Jobs\ExpirePatientCases)->dailyAt('01:00');
Schedule::job(new \App\Jobs\NotifyCaseExpiring)->dailyAt('09:00');

// Phase 7.2: Rating Summary Recalculation
Schedule::job(new \App\Jobs\RecalculateRatingSummaries)->dailyAt('03:00');
