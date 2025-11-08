<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule job imports every 6 hours (includes all sources: Remotive, TheMuse, Himalayas, AiJobsNet)
Schedule::command('jobs:import')->everySixHours();

// Schedule Jobicy imports every 6 hours
Schedule::command('jobs:import-jobicy', ['--count' => 100])->everySixHours();
