<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('monitor:systems')->everyFiveMinutes();

// Sincronizar verificações de KYC pendentes a cada 5 minutos
Schedule::command('nodal:sync-verifications')->everyFiveMinutes();
