<?php

namespace Tests\Feature\Controllers\Api;

use App\Enums\Visibility;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\TestCase;

class PostControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_filter_returns_user_posts(): void
    {
        $user = User::factory()->create();
        Post::factory()->count(3)->create([
            'user_id' => $user->id,
            'visibility' => Visibility::PRIVATE,
        ]);
        Post::factory()->count(2)->create([
            'visibility' => Visibility::PRIVATE,
        ]);

        Passport::actingAs($user);
        $response = $this->getJson(route('api.posts.filter'));

        $response->assertOk();
        $response->assertJsonStructure([
            'items',
            'nextCursor',
            'previousCursor',
            'availableTags',
        ]);
        $response->assertJsonCount(3, 'items');
    }

    public function test_filter_with_date_range(): void
    {
        $user = User::factory()->create();
        Post::factory()->create([
            'user_id' => $user->id,
            'visibility' => Visibility::PRIVATE,
            'published_at' => now()->subDays(10),
        ]);
        Post::factory()->create([
            'user_id' => $user->id,
            'visibility' => Visibility::PRIVATE,
            'published_at' => now()->subDays(5),
        ]);
        Post::factory()->create([
            'user_id' => $user->id,
            'visibility' => Visibility::PRIVATE,
            'published_at' => now(),
        ]);

        Passport::actingAs($user);
        $response = $this->getJson(route('api.posts.filter', [
            'dateFrom' => now()->subDays(7)->toDateString(),
            'dateTo' => now()->toDateString(),
        ]));

        $response->assertOk();
        $response->assertJsonCount(2, 'items');
    }

    public function test_filter_with_visibility(): void
    {
        $user = User::factory()->create();
        Post::factory()->count(2)->create([
            'user_id' => $user->id,
            'visibility' => Visibility::PUBLIC,
        ]);
        Post::factory()->create([
            'user_id' => $user->id,
            'visibility' => Visibility::PRIVATE,
        ]);

        Passport::actingAs($user);
        $response = $this->getJson(route('api.posts.filter', [
            'visibility' => [Visibility::PUBLIC->value],
        ]));

        $response->assertOk();
        $response->assertJsonCount(2, 'items');
    }

    public function test_filter_returns_available_tags(): void
    {
        $user = User::factory()->create();

        Passport::actingAs($user);
        $response = $this->getJson(route('api.posts.filter'));

        $response->assertOk();
        $response->assertJsonStructure(['availableTags']);
    }

    public function test_unauthenticated_user_cannot_filter(): void
    {
        $response = $this->getJson(route('api.posts.filter'));

        $response->assertUnauthorized();
    }

    public function test_mass_edit_updates_posts(): void
    {
        $user = User::factory()->create();
        $posts = Post::factory()->count(3)->create([
            'user_id' => $user->id,
            'visibility' => Visibility::PRIVATE,
        ]);

        Passport::actingAs($user);
        $response = $this->postJson(route('api.posts.mass-edit'), [
            'postIds' => $posts->pluck('id')->toArray(),
            'visibility' => Visibility::PUBLIC->value,
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);

        foreach ($posts as $post) {
            $this->assertEquals(Visibility::PUBLIC, $post->fresh()->visibility);
        }
    }
}
