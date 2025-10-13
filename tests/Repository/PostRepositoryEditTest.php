<?php

namespace Tests\Repository;

use App\Enums\Visibility;
use App\Hydrators\PostHydrator;
use App\Models\Post;
use App\Models\User;
use App\Repositories\PostRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostRepositoryEditTest extends TestCase
{
    use RefreshDatabase;

    public function test_post_edit_functionality()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create([
            'user_id' => $user->id,
            'body' => 'Original body',
            'visibility' => Visibility::PRIVATE->value,
        ]);

        $repo = new PostRepository;
        $updatedPost = $repo->updateBasePost(new PostHydrator()->modelToDto($post), Visibility::PUBLIC, 'Updated body');

        $this->assertEquals('Updated body', $updatedPost->body);
        $this->assertEquals(Visibility::PUBLIC, $updatedPost->visibility);

        $post->refresh();
        $this->assertEquals('Updated body', $post->body);
        $this->assertEquals(Visibility::PUBLIC, $post->visibility);
    }

    public function test_post_edit_only_body()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create([
            'user_id' => $user->id,
            'body' => 'Original body',
            'visibility' => Visibility::PRIVATE->value,
        ]);

        $repo = new PostRepository;
        $updatedPost = $repo->updateBasePost(new PostHydrator()->modelToDto($post), null, 'Updated body');

        $this->assertEquals('Updated body', $updatedPost->body);
        $this->assertEquals(Visibility::PRIVATE, $updatedPost->visibility);

        $post->refresh();
        $this->assertEquals('Updated body', $post->body);
        $this->assertEquals(Visibility::PRIVATE, $post->visibility);
    }

    public function test_post_edit_only_visibility()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create([
            'user_id' => $user->id,
            'body' => 'Original body',
            'visibility' => Visibility::PRIVATE->value,
        ]);

        $repo = new PostRepository;
        $updatedPost = $repo->updateBasePost(new PostHydrator()->modelToDto($post), Visibility::PUBLIC, null);

        $this->assertEquals('Original body', $updatedPost->body);
        $this->assertEquals(Visibility::PUBLIC, $updatedPost->visibility);

        $post->refresh();
        $this->assertEquals('Original body', $post->body);
        $this->assertEquals(Visibility::PUBLIC, $post->visibility);
    }
}
