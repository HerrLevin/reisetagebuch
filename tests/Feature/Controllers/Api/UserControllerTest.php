<?php

namespace Tests\Feature\Controllers\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

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
}
