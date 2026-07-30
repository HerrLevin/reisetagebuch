<?php

namespace Tests\Feature\Middleware;

use App\Models\PrivacyPolicy;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\TestCase;

class EnsurePrivacyPolicyAcceptedTest extends TestCase
{
    use RefreshDatabase;

    public function test_gated_route_is_reachable_when_no_policy_has_been_published(): void
    {
        $user = User::factory()->create();

        Passport::actingAs($user);
        $response = $this->getJson(route('posts.timeline'));

        $response->assertOk();
    }

    public function test_gated_route_is_blocked_until_the_current_policy_is_accepted(): void
    {
        $user = User::factory()->create();
        PrivacyPolicy::query()->create(['content' => 'Current version', 'valid_from' => now()->subDay()]);

        Passport::actingAs($user);
        $response = $this->getJson(route('posts.timeline'));

        $response->assertForbidden();
        $response->assertJson(['code' => 'privacyPolicyRequired']);
    }

    public function test_gated_route_is_reachable_after_accepting_the_current_policy(): void
    {
        $user = User::factory()->create();
        $policy = PrivacyPolicy::query()->create(['content' => 'Current version', 'valid_from' => now()->subDay()]);

        Passport::actingAs($user);
        $this->postJson(route('privacy-policy.accept', ['privacyPolicy' => $policy->id]))->assertOk();

        $response = $this->getJson(route('posts.timeline'));

        $response->assertOk();
    }

    public function test_exempt_routes_remain_reachable_without_accepting_the_current_policy(): void
    {
        $user = User::factory()->create();
        PrivacyPolicy::query()->create(['content' => 'Current version', 'valid_from' => now()->subDay()]);

        Passport::actingAs($user);

        $this->getJson(route('auth.user'))->assertOk();
        $this->getJson(route('account.show'))->assertOk();
    }
}
