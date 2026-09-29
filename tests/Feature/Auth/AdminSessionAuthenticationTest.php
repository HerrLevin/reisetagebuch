<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Providers\TelescopeServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class AdminSessionAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_form_is_reachable(): void
    {
        $response = $this->get(route('admin.login'));

        $response->assertOk();
        $response->assertSee('Admin sign in');
    }

    public function test_admin_can_sign_in_and_is_redirected_to_horizon(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->post(route('admin.login'), [
            'email' => $admin->email,
            'password' => 'password',
        ]);

        $response->assertRedirect('/horizon');
        $this->assertAuthenticatedAs($admin, 'web');
    }

    public function test_non_admin_credentials_are_rejected_without_creating_a_session(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $response = $this->post(route('admin.login'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest('web');
    }

    public function test_wrong_password_is_rejected(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->post(route('admin.login'), [
            'email' => $admin->email,
            'password' => 'not-the-password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest('web');
    }

    public function test_authenticated_admin_can_log_out(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin, 'web');

        $response = $this->post(route('admin.logout'));

        $response->assertRedirect(route('admin.login'));
        $this->assertGuest('web');
    }

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
