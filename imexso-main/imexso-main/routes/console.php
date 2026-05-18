<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

if (config('imexso.schedule_imports', true)) {
    Schedule::command('cars:import-all')
        ->dailyAt(config('imexso.schedule_vendu_at', '02:30'))
        ->withoutOverlapping()
        ->onOneServer()
        ->appendOutputTo(storage_path('logs/cars-import.log'));
}
