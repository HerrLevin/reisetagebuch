<?php

namespace Tests\Feature;

use App\Enums\PostMetaInfo\TravelReason;
use App\Enums\Visibility;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\TestCase;

class MassEditPostsTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_mass_edit_visibility(): void
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

    public function test_user_can_mass_edit_travel_reason(): void
    {
        $user = User::factory()->create();
        $posts = Post::factory()->count(2)->create([
            'user_id' => $user->id,
        ]);

        Passport::actingAs($user);
        $response = $this->postJson(route('api.posts.mass-edit'), [
            'postIds' => $posts->pluck('id')->toArray(),
            'travelReason' => TravelReason::BUSINESS->value,
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_user_can_mass_edit_tags(): void
    {
        $user = User::factory()->create();
        $posts = Post::factory()->count(2)->create([
            'user_id' => $user->id,
        ]);

        Passport::actingAs($user);
        $response = $this->postJson(route('api.posts.mass-edit'), [
            'postIds' => $posts->pluck('id')->toArray(),
            'tags' => ['vacation', 'summer'],
            'addTags' => false,
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);

        foreach ($posts as $post) {
            $tags = $post->fresh()->hashTags->pluck('value')->toArray();
            $this->assertContains('vacation', $tags);
            $this->assertContains('summer', $tags);
        }
    }

    public function test_user_cannot_mass_edit_other_users_posts(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $posts = Post::factory()->count(2)->create([
            'user_id' => $otherUser->id,
            'visibility' => Visibility::PRIVATE,
        ]);

        Passport::actingAs($user);
        $response = $this->postJson(route('api.posts.mass-edit'), [
            'postIds' => $posts->pluck('id')->toArray(),
            'visibility' => Visibility::PUBLIC->value,
        ]);

        $response->assertOk();

        foreach ($posts as $post) {
            $this->assertEquals(Visibility::PRIVATE, $post->fresh()->visibility);
        }
    }

    public function test_mass_edit_requires_post_ids(): void
    {
        $user = User::factory()->create();

        Passport::actingAs($user);
        $response = $this->postJson(route('api.posts.mass-edit'), [
            'visibility' => Visibility::PUBLIC->value,
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('postIds');
    }

    public function test_mass_edit_validates_post_ids_exist(): void
    {
        $user = User::factory()->create();

        Passport::actingAs($user);
        $response = $this->postJson(route('api.posts.mass-edit'), [
            'postIds' => ['00000000-0000-0000-0000-000000000000'],
            'visibility' => Visibility::PUBLIC->value,
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('postIds.0');
    }
}
