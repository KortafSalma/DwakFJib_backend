<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('reservations:expire')->everyMinute();
Schedule::command('stock:check-low')->everySixHours();
Schedule::command('medications:check-expired')->dailyAt('08:00');
Schedule::command('certificates:cleanup --days=30')->dailyAt('02:00');
