<?php

namespace Tests\Repository;

use App\Enums\Visibility;
use App\Models\Post;
use App\Models\User;
use App\Repositories\PostRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class PostRepositoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_owner_sees_all_posts()
    {
        $user = User::factory()->create();
        $repo = new PostRepository;

        $publicPost = Post::factory()->create(['user_id' => $user->id, 'visibility' => Visibility::PUBLIC->value]);
        $privatePost = Post::factory()->create(['user_id' => $user->id, 'visibility' => Visibility::PRIVATE->value]);
        $unlistedPost = Post::factory()->create(['user_id' => $user->id, 'visibility' => Visibility::UNLISTED->value]);
        $onlyAuthPost = Post::factory()->create(['user_id' => $user->id, 'visibility' => Visibility::ONLY_AUTHENTICATED->value]);

        $result = $repo->getPostsForUser($user, $user);
        $ids = collect($result->items)->pluck('id')->all();

        $this->assertContains($publicPost->id, $ids);
        $this->assertContains($privatePost->id, $ids);
        $this->assertContains($unlistedPost->id, $ids);
        $this->assertContains($onlyAuthPost->id, $ids);
    }

    public function test_profile_visiting_user_sees_only_public_posts()
    {
        $owner = User::factory()->create();
        $visitor = User::factory()->create();
        $repo = new PostRepository;

        $publicPost = Post::factory()->create(['user_id' => $owner->id, 'visibility' => Visibility::PUBLIC->value]);
        $privatePost = Post::factory()->create(['user_id' => $owner->id, 'visibility' => Visibility::PRIVATE->value]);
        $unlistedPost = Post::factory()->create(['user_id' => $owner->id, 'visibility' => Visibility::UNLISTED->value]);
        $onlyAuthPost = Post::factory()->create(['user_id' => $owner->id, 'visibility' => Visibility::ONLY_AUTHENTICATED->value]);

        $result = $repo->getPostsForUser($owner, $visitor);
        $ids = collect($result->items)->pluck('id')->all();

        $this->assertContains($publicPost->id, $ids);
        $this->assertNotContains($privatePost->id, $ids);
        $this->assertNotContains($unlistedPost->id, $ids);
        $this->assertContains($onlyAuthPost->id, $ids);
    }

    public function test_profile_unauthenticated_user_sees_only_public_posts()
    {
        $owner = User::factory()->create();
        $repo = new PostRepository;

        $publicPost = Post::factory()->create(['user_id' => $owner->id, 'visibility' => Visibility::PUBLIC->value]);
        $privatePost = Post::factory()->create(['user_id' => $owner->id, 'visibility' => Visibility::PRIVATE->value]);
        $unlistedPost = Post::factory()->create(['user_id' => $owner->id, 'visibility' => Visibility::UNLISTED->value]);
        $onlyAuthPost = Post::factory()->create(['user_id' => $owner->id, 'visibility' => Visibility::ONLY_AUTHENTICATED->value]);

        $result = $repo->getPostsForUser($owner, null);
        $unauthenticatedIds = collect($result->items)->pluck('id')->all();

        $this->assertContains($publicPost->id, $unauthenticatedIds);
        $this->assertNotContains($privatePost->id, $unauthenticatedIds);
        $this->assertNotContains($unlistedPost->id, $unauthenticatedIds);
        $this->assertNotContains($onlyAuthPost->id, $unauthenticatedIds);
    }

    public function test_single_post_access_control()
    {
        $owner = User::factory()->create();
        $visitor = User::factory()->create();
        $repo = new PostRepository;

        $publicPost = Post::factory()->create(['user_id' => $owner->id, 'visibility' => Visibility::PUBLIC->value]);
        $privatePost = Post::factory()->create(['user_id' => $owner->id, 'visibility' => Visibility::PRIVATE->value]);
        $unlistedPost = Post::factory()->create(['user_id' => $owner->id, 'visibility' => Visibility::UNLISTED->value]);
        $onlyAuthPost = Post::factory()->create(['user_id' => $owner->id, 'visibility' => Visibility::ONLY_AUTHENTICATED->value]);

        // Owner can access all posts
        $this->assertEquals($publicPost->id, $repo->getById($publicPost->id, $owner)->id);
        $this->assertEquals($privatePost->id, $repo->getById($privatePost->id, $owner)->id);
        $this->assertEquals($unlistedPost->id, $repo->getById($unlistedPost->id, $owner)->id);
        $this->assertEquals($onlyAuthPost->id, $repo->getById($onlyAuthPost->id, $owner)->id);

        // Visitor can access public, unlisted, and only-authenticated posts
        $this->assertEquals($unlistedPost->id, $repo->getById($unlistedPost->id, $visitor)->id);
        $this->assertEquals($publicPost->id, $repo->getById($publicPost->id, $visitor)->id);
        $this->assertEquals($onlyAuthPost->id, $repo->getById($onlyAuthPost->id, $visitor)->id);
        $this->expectException(HttpException::class);
        $repo->getById($privatePost->id, $visitor);

        // Unauthenticated user can access only public and unlisted posts
        $this->assertEquals($publicPost->id, $repo->getById($publicPost->id, null)->id);
        $this->assertEquals($unlistedPost->id, $repo->getById($unlistedPost->id, null)->id);
        $this->expectException(HttpException::class);
        $repo->getById($privatePost->id, null);
        $this->expectException(HttpException::class);
        $repo->getById($onlyAuthPost->id, null);
    }

    public function test_dashboard_sees_only_public_and_own_posts()
    {
        $owner = User::factory()->create();
        $owner2 = User::factory()->create();
        $repo = new PostRepository;

        $publicPost = Post::factory()->create(['user_id' => $owner->id, 'visibility' => Visibility::PUBLIC->value]);
        $privatePost = Post::factory()->create(['user_id' => $owner->id, 'visibility' => Visibility::PRIVATE->value]);
        $unlistedPost = Post::factory()->create(['user_id' => $owner->id, 'visibility' => Visibility::UNLISTED->value]);
        $onlyAuthPost = Post::factory()->create(['user_id' => $owner->id, 'visibility' => Visibility::ONLY_AUTHENTICATED->value]);
        $ownPrivatePost = Post::factory()->create(['user_id' => $owner2->id, 'visibility' => Visibility::PRIVATE->value]);
        $ownUnlistedPost = Post::factory()->create(['user_id' => $owner2->id, 'visibility' => Visibility::UNLISTED->value]);
        $ownPublicPost = Post::factory()->create(['user_id' => $owner2->id, 'visibility' => Visibility::PUBLIC->value]);

        $result = $repo->getDashboardForUser($owner2);
        $ids = collect($result->items)->pluck('id')->all();

        $this->assertContains($publicPost->id, $ids);
        $this->assertContains($onlyAuthPost->id, $ids);
        $this->assertNotContains($privatePost->id, $ids);
        $this->assertNotContains($unlistedPost->id, $ids);

        $this->assertContains($ownPrivatePost->id, $ids);
        $this->assertContains($ownUnlistedPost->id, $ids);
        $this->assertContains($ownPublicPost->id, $ids);
    }

    public function test_unauthenticated_user_cannot_access_only_authenticated_post()
    {
        $owner = User::factory()->create();
        $repo = new PostRepository;

        $onlyAuthPost = Post::factory()->create(['user_id' => $owner->id, 'visibility' => Visibility::ONLY_AUTHENTICATED->value]);

        $this->expectException(HttpException::class);
        $repo->getById($onlyAuthPost->id, null);
    }

    public function test_authenticated_user_can_access_only_authenticated_post()
    {
        $owner = User::factory()->create();
        $visitor = User::factory()->create();
        $repo = new PostRepository;

        $onlyAuthPost = Post::factory()->create(['user_id' => $owner->id, 'visibility' => Visibility::ONLY_AUTHENTICATED->value]);

        $result = $repo->getById($onlyAuthPost->id, $visitor);
        $this->assertEquals($onlyAuthPost->id, $result->id);
    }
}
