<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Providers\TelescopeServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class AdminGateTest extends TestCase
{
    use RefreshDatabase;

    public function test_view_horizon_gate_allows_only_admins(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create(['is_admin' => false]);

        $this->assertTrue(Gate::forUser($admin)->allows('viewHorizon'));
        $this->assertFalse(Gate::forUser($user)->allows('viewHorizon'));
        $this->assertFalse(Gate::forUser(null)->allows('viewHorizon'));
    }

    public function test_view_telescope_gate_allows_only_admins(): void
    {
        // Telescope's own service provider is only registered when enabled (see
        // AppServiceProvider), which the test env deliberately keeps off — register
        // it here so its gate definition runs for this test.
        $this->app->register(TelescopeServiceProvider::class);

        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create(['is_admin' => false]);

        $this->assertTrue(Gate::forUser($admin)->allows('viewTelescope'));
        $this->assertFalse(Gate::forUser($user)->allows('viewTelescope'));
        $this->assertFalse(Gate::forUser(null)->allows('viewTelescope'));
    }
}
