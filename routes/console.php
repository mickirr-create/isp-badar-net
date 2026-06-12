<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Billing Cycle Scheduler
Schedule::command('billing:check-due-dates')->dailyAt('07:00');
Schedule::command('billing:apply-throttle')->dailyAt('08:00');
Schedule::command('billing:restore-speed')->everyThirtyMinutes();
