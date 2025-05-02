<?php

namespace App\Repositories;

use App\Models\Location;
use App\Models\Post;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Throwable;

class PostRepository
{
    public function store(User $user, Location $location, ?string $body = null): Post {
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

        return $post;
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
    ): Post {
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

        return $post;
    }

    public function dashboard(User $user): Collection
    {
        return Post::with(['user', 'locationPost.location', 'locationPost.location.tags', 'transportPost', 'transportPost.origin', 'transportPost.destination'])
            ->where('user_id', $user->id)
            ->latest()
            ->limit(50)
            ->get();
    }

    public function getById(string $postId): Post
    {
        return Post::with(['user', 'locationPost.location', 'locationPost.location.tags', 'transportPost', 'transportPost.origin', 'transportPost.destination'])
            ->where('id', $postId)
            ->firstOrFail();
    }
}
