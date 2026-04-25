<?php

use App\Console\Commands\FetchAirports;
use App\Http\Middleware\ApiMiddleware;
use App\Http\Middleware\EnsureUserIsAdmin;
use App\Http\Middleware\RequestLogger;
use App\Jobs\DeleteOldNearbyRequests;
use App\Jobs\DispatchRefreshJobForActiveTrips;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
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
            CreateFreshApiToken::class,
            RequestLogger::class,
        ]);
        $middleware->api(append: [
            ApiMiddleware::class,
            RequestLogger::class,
        ]);
        $middleware->encryptCookies(except: [
            'rtb_allow_history',
        ]);
        $middleware->alias([
            'admin' => EnsureUserIsAdmin::class,
        ]);
        $middleware->redirectGuestsTo('/login');
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->withSchedule(function (Schedule $schedule) {
        $schedule->command(FetchAirports::class)->daily()->runInBackground();
        $schedule->job(DispatchRefreshJobForActiveTrips::class)->everyMinute();
        $schedule->job(DeleteOldNearbyRequests::class)->daily();
    })
    ->create();
