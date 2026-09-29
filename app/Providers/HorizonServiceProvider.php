<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Laravel\Horizon\Horizon;
use Laravel\Horizon\HorizonApplicationServiceProvider;

class HorizonServiceProvider extends HorizonApplicationServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        parent::boot();

        // Horizon::routeSmsNotificationsTo('15556667777');
        // Horizon::routeMailNotificationsTo('example@example.com');
        // Horizon::routeSlackNotificationsTo('slack-webhook-url', '#channel');
    }

    /**
     * Configure the Horizon authorization services.
     *
     * Adds a "dashboard_enabled" kill switch (see config/horizon.php) on top of the
     * default gate/local-environment check, so the dashboard can be turned off
     * entirely regardless of who's asking.
     */
    protected function authorization(): void
    {
        $this->gate();

        Horizon::auth(function ($request) {
            if (! config('horizon.dashboard_enabled')) {
                return false;
            }

            return Gate::check('viewHorizon', [$request->user()]);
        });
    }

    /**
     * Register the Horizon gate.
     *
     * This gate determines who can access Horizon in non-local environments.
     */
    protected function gate(): void
    {
        Gate::define('viewHorizon', function ($user = null) {
            return (bool) $user?->isAdmin();
        });
    }
}
