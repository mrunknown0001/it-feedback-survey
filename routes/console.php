<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use Illuminate\Support\Facades\Schedule;

Schedule::command('backup:run --only-db')->at('18:00')->days([1, 2, 3, 4, 5, 6]);
Schedule::command('backup:clean')->dailyAt('02:30');
