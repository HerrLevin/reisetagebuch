<?php

namespace App\Repositories;

use App\Models\Like;

class LikeRepository
{
    public function store(string $userId, string $postId): Like
    {
        $like = Like::firstOrCreate([
            'user_id' => $userId,
            'post_id' => $postId,
        ]);

        return $like;
    }

    public function getLike(string $userId, string $postId): ?Like
    {
        return Like::where('user_id', $userId)
            ->where('post_id', $postId)
            ->first();
    }

    public function destroy(string $likeId): bool
    {
        return Like::where('id', $likeId)->delete() > 0;
    }
}
