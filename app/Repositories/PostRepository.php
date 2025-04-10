<?php

namespace App\Repositories;

use App\Models\Location;
use App\Models\Post;
use App\Models\User;
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

    public function dashboard(User $user): Collection
    {
        return Post::with(['user', 'locationPost.location', 'locationPost.location.tags'])
            ->where('user_id', $user->id)
            ->latest()
            ->limit(50)
            ->get();
    }
}
