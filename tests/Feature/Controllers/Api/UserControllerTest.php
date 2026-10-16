<?php

namespace Tests\Feature\Controllers\Api;

use App\Enums\Visibility;
use App\Models\Location;
use App\Models\LocationPost;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    private function makeLocationPost(string $userId, Visibility $visibility): void
    {
        $post = Post::factory()->create(['user_id' => $userId, 'visibility' => $visibility->value]);
        $location = Location::factory()->create();
        LocationPost::create(['post_id' => $post->id, 'location_id' => $location->id]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // GET /api/users/search
    // ──────────────────────────────────────────────────────────────────────────

    public function test_search_is_public_and_requires_no_authentication(): void
    {
        $this->getJson('/api/users/search?q=test')->assertOk();
    }

    public function test_search_matches_username_case_insensitively(): void
    {
        $user = User::factory()->create(['username' => 'traveldiaryfan']);

        $response = $this->getJson('/api/users/search?q=TRAVELDIARY');

        $response->assertOk()->assertJsonCount(1);
        $this->assertSame($user->id, $response->json('0.id'));
    }

    public function test_search_matches_display_name_case_insensitively(): void
    {
        $user = User::factory()->create(['name' => 'Ada Lovelace']);

        $response = $this->getJson('/api/users/search?q=lovelace');

        $response->assertOk()->assertJsonCount(1);
        $this->assertSame($user->id, $response->json('0.id'));
    }

    public function test_search_returns_empty_array_for_blank_query(): void
    {
        User::factory()->create();

        $response = $this->getJson('/api/users/search?q=');

        $response->assertOk()->assertExactJson([]);
    }

    public function test_search_returns_empty_array_for_no_matches(): void
    {
        User::factory()->create(['username' => 'someone']);

        $response = $this->getJson('/api/users/search?q=nonexistentquery');

        $response->assertOk()->assertExactJson([]);
    }

    public function test_search_respects_result_limit(): void
    {
        for ($i = 0; $i < 25; $i++) {
            User::factory()->create(['username' => 'searchlimituser'.$i]);
        }

        $response = $this->getJson('/api/users/search?q=searchlimituser');

        $response->assertOk()->assertJsonCount(20);
    }

    public function test_search_returns_user_dto_shape(): void
    {
        $user = User::factory()->create(['username' => 'shapedto']);

        $response = $this->getJson('/api/users/search?q=shapedto');

        $response->assertOk()->assertJsonStructure([
            ['id', 'username', 'name', 'statistics'],
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // GET /api/users/{userId}/map-data
    // ──────────────────────────────────────────────────────────────────────────

    public function test_map_data_hides_non_public_locations_from_unauthenticated_visitors(): void
    {
        $owner = User::factory()->create();
        $this->makeLocationPost($owner->id, Visibility::PUBLIC);
        $this->makeLocationPost($owner->id, Visibility::PRIVATE);
        $this->makeLocationPost($owner->id, Visibility::UNLISTED);
        $this->makeLocationPost($owner->id, Visibility::ONLY_AUTHENTICATED);

        $response = $this->getJson("/api/users/{$owner->id}/map-data");

        $response->assertOk();
        $this->assertCount(1, $response->json('geometries'));
    }

    public function test_map_data_hides_private_and_unlisted_locations_from_other_authenticated_users(): void
    {
        $owner = User::factory()->create();
        Passport::actingAs(User::factory()->create());

        $this->makeLocationPost($owner->id, Visibility::PUBLIC);
        $this->makeLocationPost($owner->id, Visibility::PRIVATE);
        $this->makeLocationPost($owner->id, Visibility::UNLISTED);
        $this->makeLocationPost($owner->id, Visibility::ONLY_AUTHENTICATED);

        $response = $this->getJson("/api/users/{$owner->id}/map-data");

        $response->assertOk();
        $this->assertCount(2, $response->json('geometries'));
    }

    public function test_map_data_shows_all_locations_to_the_owner(): void
    {
        $owner = User::factory()->create();
        Passport::actingAs($owner);

        $this->makeLocationPost($owner->id, Visibility::PUBLIC);
        $this->makeLocationPost($owner->id, Visibility::PRIVATE);
        $this->makeLocationPost($owner->id, Visibility::UNLISTED);
        $this->makeLocationPost($owner->id, Visibility::ONLY_AUTHENTICATED);

        $response = $this->getJson("/api/users/{$owner->id}/map-data");

        $response->assertOk();
        $this->assertCount(4, $response->json('geometries'));
    }
}
