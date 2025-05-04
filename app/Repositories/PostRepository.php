<?php

namespace App\Repositories;

use App\Http\Resources\PostTypes\BasePost;
use App\Http\Resources\PostTypes\LocationPost;
use App\Http\Resources\PostTypes\TransportPost;
use App\Hydrators\PostHydrator;
use App\Models\Location;
use App\Models\Post;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Throwable;

class PostRepository
{
    private PostHydrator $postHydrator;

    public function __construct(?PostHydrator $postHydrator = null)
    {
        $this->postHydrator = $postHydrator ?? new PostHydrator();
    }

    public function store(User $user, Location $location, ?string $body = null): BasePost|LocationPost|TransportPost
    {
        try {
            DB::beginTransaction();
            /** @var Post $post */
            $post = Post::create([
                'user_id' => $user->id,
                'body' => $body,
            ]);
            // create location post
            $post->locationPost()->create([
                'location_id' => $location->id,
            ]);
            DB::commit();

            $post->load('locationPost');
        } catch (Throwable $e) {
            DB::rollBack();
            report($e);
        }

        return $this->postHydrator->modelToDto($post);
    }

    public function storeTransport(
        User $user,
        Location $start,
        Carbon $startTime,
        Location $stop,
        Carbon $stopTime,
        string $mode,
        string $line,
        ?string $body = null
    ): BasePost|LocationPost|TransportPost
    {
        try {
            DB::beginTransaction();
            /** @var Post $post */
            $post = Post::create([
                'user_id' => $user->id,
                'body' => $body,
            ]);

            // create transport post
            $post->transportPost()->create([
                'origin_id' => $start->id,
                'departure' => $startTime,
                'destination_id' => $stop->id,
                'arrival' => $stopTime,
                'mode' => $mode,
                'line' => $line,
            ]);
            DB::commit();

            $post->load('transportPost');
        } catch (Throwable $e) {
            DB::rollBack();
            report($e);
        }

        return $this->postHydrator->modelToDto($post);
    }

    public function getPostsForUser(User|string $user): Collection
    {
        if ($user instanceof User) {
            $user = $user->id;
        }

        $posts = Post::with(['user', 'locationPost.location', 'locationPost.location.tags', 'transportPost', 'transportPost.origin', 'transportPost.destination'])
            ->where('user_id', $user)
            ->latest()
            ->limit(50)
            ->get();

        return $posts->map(function (Post $post) {
            return $this->postHydrator->modelToDto($post);
        });
    }

    public function getById(string $postId): BasePost|LocationPost|TransportPost
    {
        $post = Post::with(['user', 'locationPost.location', 'locationPost.location.tags', 'transportPost', 'transportPost.origin', 'transportPost.destination'])
            ->where('id', $postId)
            ->firstOrFail();

        return $this->postHydrator->modelToDto($post);
    }

    public function delete(Post $post): void
    {
        DB::beginTransaction();
        $post->delete();
        $post->locationPost()->delete();
        $post->transportPost()->delete();
        DB::commit();
    }
}
