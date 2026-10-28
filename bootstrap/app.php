<?php

use App\Console\Commands\FetchAirports;
use App\Console\Commands\ImportOsmExtracts;
use App\Exceptions\ApiExceptionRenderer;
use App\Http\Middleware\ApiMiddleware;
use App\Http\Middleware\CheckScopes;
use App\Http\Middleware\EnsurePrivacyPolicyAccepted;
use App\Http\Middleware\EnsureUserIsAdmin;
use App\Http\Middleware\RequestLogger;
use App\Jobs\DeleteOldNearbyRequests;
use App\Jobs\DispatchRefreshJobForActiveTrips;
use App\Jobs\HideExpiredPosts;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Laravel\Passport\Http\Middleware\CreateFreshApiToken;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: [
            __DIR__.'/../routes/api.php',
            __DIR__.'/../routes/admin.php',
        ],
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            // Gives the logged-in web SPA its API token as the laravel_token cookie.
            CreateFreshApiToken::class,
            RequestLogger::class,
        ]);
        $middleware->api(append: [
            ApiMiddleware::class,
            RequestLogger::class,
        ]);
        // ApiMiddleware turns the web token cookie into a bearer header, so it must run before auth:api.
        $middleware->prependToPriorityList(Authenticate::class, ApiMiddleware::class);
        $middleware->encryptCookies(except: [
            'rtb_allow_history',
        ]);
        $middleware->alias([
            'admin' => EnsureUserIsAdmin::class,
            'privacy-policy' => EnsurePrivacyPolicyAccepted::class,
            'scopes' => CheckScopes::class,
        ]);
        $middleware->redirectGuestsTo('/login');
        $middleware->redirectUsersTo('/home');
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (Throwable $e, Request $request) {
            return (new ApiExceptionRenderer)->render($e, $request);
        });
    })
    ->withSchedule(function (Schedule $schedule) {
        $schedule->command('horizon:snapshot')->everyFiveMinutes();
        $schedule->command(FetchAirports::class)->daily()->runInBackground();
        $schedule->job(DispatchRefreshJobForActiveTrips::class)->everyMinute();
        $schedule->job(DeleteOldNearbyRequests::class)->daily();
        $schedule->job(HideExpiredPosts::class)->everyFifteenMinutes();
        $schedule->command(ImportOsmExtracts::class)->cron(config('app.osm.geofabrik_import_schedule'));
    })
    ->create();
