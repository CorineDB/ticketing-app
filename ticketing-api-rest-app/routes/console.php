<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Schedule event status updates every hour
Schedule::command('events:update-statuses')
    ->hourly()
    ->withoutOverlapping()
    ->runInBackground();

// Schedule gate status updates every hour
Schedule::command('gates:update-statuses')
    ->hourly()
    ->withoutOverlapping()
    ->runInBackground();
