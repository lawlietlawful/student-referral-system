<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Run the auto-assignment batch job every day at 8:00 AM
Schedule::command('seminars:auto-assign')->dailyAt('08:00');

// Run the SMS reminder batch job every day at 8:30 AM
Schedule::command('seminars:send-reminders')->dailyAt('08:30');

// Run the post-seminar effectiveness tracker daily at 1:00 AM
Schedule::command('seminars:track-effectiveness')->dailyAt('01:00');
