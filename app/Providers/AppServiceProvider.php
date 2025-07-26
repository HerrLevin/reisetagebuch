<?php

namespace App\Providers;

use App\Http\Resources\PostTypes\BasePost;
use App\Models\Invite;
use App\Policies\InvitePolicy;
use App\Policies\PostPolicy;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

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
    }
}
