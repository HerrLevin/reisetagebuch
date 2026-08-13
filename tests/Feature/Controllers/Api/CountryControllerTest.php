<?php

namespace Tests\Feature\Controllers\Api;

use App\Models\Location;
use App\Models\LocationPost;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\TestCase;

class CountryControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_visited_and_transited_only_countries_for_the_authenticated_user(): void
    {
        $user = User::factory()->create();
        Passport::actingAs($user);

        $location = Location::factory()->create(['country_code' => 'DE']);
        $post = Post::factory()->create(['user_id' => $user->id]);
        LocationPost::create(['post_id' => $post->id, 'location_id' => $location->id]);

        $response = $this->getJson('/api/statistics/countries');

        $response->assertOk();
        $response->assertJson(['visited' => ['DE'], 'transitedOnly' => []]);
    }

    public function test_requires_authentication(): void
    {
        $response = $this->getJson('/api/statistics/countries');

        $response->assertUnauthorized();
    }
}
