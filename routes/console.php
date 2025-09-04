<?php

use Illuminate\Foundation\Inspiring;
use App\Jobs\ProcessScheduledPayments;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
// Exécuter les paiements programmés arrivés à échéance toutes les 5 minutes
Schedule::job(new ProcessScheduledPayments)->everyFiveMinutes();