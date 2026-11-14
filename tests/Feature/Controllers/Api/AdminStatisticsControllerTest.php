<?php

namespace Tests\Feature\Controllers\Api;

use App\Enums\Scopes\AdminReadScope;
use App\Enums\Scopes\Scope;
use App\Models\ActivityPubFollower;
use App\Models\ActivityPubServer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\TestCase;

class AdminStatisticsControllerTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create()->forceFill(['is_admin' => true]);
    }

    public function test_guests_are_unauthorized(): void
    {
        $this->getJson(route('admin.statistics'))->assertUnauthorized();
    }

    public function test_non_admins_are_forbidden(): void
    {
        Passport::actingAs(User::factory()->create(), ['*']);

        $this->getJson(route('admin.statistics'))->assertForbidden();
    }

    public function test_admins_need_the_statistics_scope(): void
    {
        $admin = $this->admin();
        $admin->save();

        Passport::actingAs($admin, [Scope::Read->value, AdminReadScope::Settings->value]);
        $this->getJson(route('admin.statistics'))->assertForbidden();

        Passport::actingAs($admin, [Scope::AdminRead->value]);
        $this->getJson(route('admin.statistics'))->assertOk();
    }

    public function test_returns_counts_for_admins(): void
    {
        $admin = $this->admin();
        $admin->save();
        User::factory()->count(2)->create();
        User::factory()->create()->forceFill(['updated_at' => now()->subMonths(2)])->saveQuietly();

        Passport::actingAs($admin, [AdminReadScope::Statistics->value]);
        $this->getJson(route('admin.statistics'))
            ->assertOk()
            ->assertExactJson([
                'users' => 4,
                'activeUsersMonth' => 3,
                'posts' => 0,
                'postsMonth' => 0,
                'invites' => 0,
                'federatedServers' => 0,
                'federatedServersMonth' => 0,
                'remoteFollowers' => 0,
                'remotePosts' => 0,
                'remotePostsMonth' => 0,
            ]);
    }

    public function test_returns_activity_pub_counts(): void
    {
        $admin = $this->admin();
        $admin->save();
        ActivityPubServer::create(['host' => 'a.example', 'first_seen_at' => now(), 'last_seen_at' => now()]);
        ActivityPubServer::create(['host' => 'b.example', 'first_seen_at' => now()->subMonths(3), 'last_seen_at' => now()->subMonths(2)]);
        ActivityPubFollower::factory()->count(2)->create();

        Passport::actingAs($admin, [AdminReadScope::Statistics->value]);
        $this->getJson(route('admin.statistics'))
            ->assertOk()
            ->assertJson([
                'federatedServers' => 2,
                'federatedServersMonth' => 1,
                'remoteFollowers' => 2,
            ]);
    }

    public function test_admin_page_redirects_guests_and_forbids_non_admins(): void
    {
        $this->get('/admin/imprint')->assertRedirect('/login');

        $this->actingAs(User::factory()->create())->get('/admin')->assertForbidden();
    }

    public function test_admin_page_renders_for_admins(): void
    {
        $admin = $this->admin();
        $admin->save();

        $this->actingAs($admin)->get('/admin/imprint')->assertOk()->assertViewIs('admin');
    }
}
