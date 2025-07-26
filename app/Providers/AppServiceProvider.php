<?php

namespace App\Providers;

use App\Http\Resources\PostTypes\BasePost;
use App\Models\Invite;
use App\Policies\InvitePolicy;
use App\Policies\PostPolicy;
use App\Services\Socialite\TraewellingProvider;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Laravel\Socialite\Facades\Socialite;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        JsonResource::withoutWrapping();
        Vite::prefetch(concurrency: 3);

        // This will force all links to be with https in production
        // This is useful when you are using a load balancer or reverse proxy
        if (App::environment('production')) {
            URL::forceScheme('https');
        }

        // Gates
        Gate::policy(BasePost::class, PostPolicy::class);
        Gate::policy(Invite::class, InvitePolicy::class);

        if ($this->app->environment('local')) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }

        // Register custom Socialite provider for traewelling
        Socialite::extend('traewelling', function ($app) {
            $config = $app['config']['services.traewelling'];
            $redirectUri = $config['redirect'] ?? config('app.url').'/socialite/traewelling/callback';

            return new TraewellingProvider(
                $app['request'],
                $config['client_id'],
                $config['client_secret'],
                $redirectUri,
            );
        });
    }
}
