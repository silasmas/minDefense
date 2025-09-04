<?php

use App\Jobs\ProcessScheduledPayments;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })->withSchedule(function (Schedule $schedule) {
        $schedule->job(new ProcessScheduledPayments)->everyFiveMinutes();
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
