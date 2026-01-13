<?php

use App\Console\Commands\FetchAirports;
use App\Http\Middleware\HandleInertiaRequests;
use App\Jobs\DeleteOldNearbyRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Laravel\Passport\Http\Middleware\CreateFreshApiToken;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
            // Should be the last middleware to run in the web group
            CreateFreshApiToken::class,
        ]);
        $middleware->encryptCookies(except: [
            'rtb_allow_history',
        ]);

        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->withSchedule(function (\Illuminate\Console\Scheduling\Schedule $schedule) {
        $schedule->command(FetchAirports::class)->daily()->runInBackground();
        $schedule->job(App\Jobs\DispatchRefreshJobForActiveTrips::class)->everyMinute();
        $schedule->job(DeleteOldNearbyRequests::class)->daily();
    })
    ->create();
