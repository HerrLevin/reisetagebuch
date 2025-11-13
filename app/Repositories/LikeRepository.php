<?php

namespace App\Repositories;

use App\Models\Like;

class LikeRepository
{
    public function store(string $userId, string $postId): Like
    {
        return Like::firstOrCreate([
            'user_id' => $userId,
            'post_id' => $postId,
        ]);
    }

    public function destroy(string $userId, string $postId): bool
    {
        return Like::where('user_id', $userId)
            ->where('post_id', $postId)
            ->delete();
    }
}
