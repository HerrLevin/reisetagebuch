<?php

namespace Tests\Feature\Controllers\Api;

use App\Models\PrivacyPolicy;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\TestCase;

class PrivacyPolicyControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_show_returns_not_found_when_no_policy_exists(): void
    {
        $response = $this->getJson(route('privacy-policy.show'));

        $response->assertNotFound();
    }

    public function test_show_returns_the_latest_version_that_is_already_in_effect(): void
    {
        PrivacyPolicy::query()->create(['content' => 'Older version', 'valid_from' => now()->subDays(10)]);
        $latest = PrivacyPolicy::query()->create(['content' => 'Latest version', 'valid_from' => now()->subDay()]);
        PrivacyPolicy::query()->create(['content' => 'Future version', 'valid_from' => now()->addDay()]);

        $response = $this->getJson(route('privacy-policy.show'));

        $response->assertOk();
        $response->assertJson(['id' => $latest->id, 'content' => 'Latest version']);
    }

    public function test_upcoming_returns_not_found_when_none_is_scheduled(): void
    {
        PrivacyPolicy::query()->create(['content' => 'Current version', 'valid_from' => now()->subDay()]);

        $response = $this->getJson(route('privacy-policy.upcoming'));

        $response->assertNotFound();
    }

    public function test_upcoming_returns_the_nearest_future_version(): void
    {
        $nearest = PrivacyPolicy::query()->create(['content' => 'Nearest upcoming', 'valid_from' => now()->addDay()]);
        PrivacyPolicy::query()->create(['content' => 'Further upcoming', 'valid_from' => now()->addWeek()]);

        $response = $this->getJson(route('privacy-policy.upcoming'));

        $response->assertOk();
        $response->assertJson(['id' => $nearest->id, 'content' => 'Nearest upcoming']);
    }

    public function test_store_forbidden_for_non_admin(): void
    {
        $user = User::factory()->create();

        Passport::actingAs($user);
        $response = $this->postJson(route('privacy-policy.store'), [
            'content' => 'New policy',
            'validFrom' => now()->addDay()->toIso8601String(),
        ]);

        $response->assertForbidden();
        $this->assertDatabaseCount('privacy_policies', 0);
    }

    public function test_store_unauthenticated_returns_unauthorized(): void
    {
        $response = $this->postJson(route('privacy-policy.store'), [
            'content' => 'New policy',
            'validFrom' => now()->addDay()->toIso8601String(),
        ]);

        $response->assertUnauthorized();
    }

    public function test_store_rejects_a_valid_from_date_in_the_past(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['is_admin' => true])->save();

        Passport::actingAs($admin);
        $response = $this->postJson(route('privacy-policy.store'), [
            'content' => 'Backdated policy',
            'validFrom' => now()->subDay()->toIso8601String(),
        ]);

        $response->assertUnprocessable();
        $this->assertDatabaseCount('privacy_policies', 0);
    }

    public function test_store_persists_a_new_version_for_admin_without_touching_existing_versions(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['is_admin' => true])->save();
        $existing = PrivacyPolicy::query()->create(['content' => 'Existing version', 'valid_from' => now()->subDay()]);

        Passport::actingAs($admin);
        $response = $this->postJson(route('privacy-policy.store'), [
            'content' => 'Brand new version',
            'validFrom' => now()->addDay()->toIso8601String(),
        ]);

        $response->assertCreated();
        $response->assertJson(['content' => 'Brand new version']);

        $this->assertDatabaseCount('privacy_policies', 2);
        $this->assertDatabaseHas('privacy_policies', ['id' => $existing->id, 'content' => 'Existing version']);
    }

    public function test_accept_requires_authentication(): void
    {
        $policy = PrivacyPolicy::query()->create(['content' => 'Current version', 'valid_from' => now()->subDay()]);

        $response = $this->postJson(route('privacy-policy.accept', ['privacyPolicy' => $policy->id]));

        $response->assertUnauthorized();
    }

    public function test_accept_persists_acceptance_for_the_current_version(): void
    {
        $user = User::factory()->create();
        $policy = PrivacyPolicy::query()->create(['content' => 'Current version', 'valid_from' => now()->subDay()]);

        Passport::actingAs($user);
        $response = $this->postJson(route('privacy-policy.accept', ['privacyPolicy' => $policy->id]));

        $response->assertOk();
        $response->assertJsonPath('acceptedAt', fn ($value) => $value !== null);

        $this->assertDatabaseHas('privacy_policy_acceptances', [
            'user_id' => $user->id,
            'privacy_policy_id' => $policy->id,
        ]);
    }

    public function test_accept_is_idempotent(): void
    {
        $user = User::factory()->create();
        $policy = PrivacyPolicy::query()->create(['content' => 'Current version', 'valid_from' => now()->subDay()]);

        Passport::actingAs($user);
        $this->postJson(route('privacy-policy.accept', ['privacyPolicy' => $policy->id]))->assertOk();
        $this->postJson(route('privacy-policy.accept', ['privacyPolicy' => $policy->id]))->assertOk();

        $this->assertDatabaseCount('privacy_policy_acceptances', 1);
    }

    public function test_accept_allows_the_upcoming_version(): void
    {
        $user = User::factory()->create();
        PrivacyPolicy::query()->create(['content' => 'Current version', 'valid_from' => now()->subDay()]);
        $upcoming = PrivacyPolicy::query()->create(['content' => 'Upcoming version', 'valid_from' => now()->addDay()]);

        Passport::actingAs($user);
        $response = $this->postJson(route('privacy-policy.accept', ['privacyPolicy' => $upcoming->id]));

        $response->assertOk();
        $this->assertDatabaseHas('privacy_policy_acceptances', [
            'user_id' => $user->id,
            'privacy_policy_id' => $upcoming->id,
        ]);
    }

    public function test_accept_rejects_a_superseded_version(): void
    {
        $user = User::factory()->create();
        $old = PrivacyPolicy::query()->create(['content' => 'Old version', 'valid_from' => now()->subWeek()]);
        PrivacyPolicy::query()->create(['content' => 'Current version', 'valid_from' => now()->subDay()]);

        Passport::actingAs($user);
        $response = $this->postJson(route('privacy-policy.accept', ['privacyPolicy' => $old->id]));

        $response->assertUnprocessable();
        $this->assertDatabaseCount('privacy_policy_acceptances', 0);
    }
}
