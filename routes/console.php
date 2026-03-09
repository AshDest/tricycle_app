<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Scheduled Tasks
|--------------------------------------------------------------------------
| Les tâches planifiées pour l'application
*/

// Envoyer les notifications quotidiennes chaque matin à 7h00
Schedule::command('notifications:quotidiennes')->dailyAt('07:00');

