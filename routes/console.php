<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// F7: pay due allowances automatically.
// Every minute is cheap and safe because paying is idempotent.
Schedule::command('allowances:pay')->everyMinute()->withoutOverlapping();