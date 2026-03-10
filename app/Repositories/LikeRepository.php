<?php

namespace App\Repositories;

use App\Http\Resources\UserDto;
use App\Hydrators\UserHydrator;
use App\Models\Like;

class LikeRepository
{
    private UserHydrator $userHydrator;

    public function __construct(UserHydrator $userHydrator)
    {
        $this->userHydrator = $userHydrator;
    }

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

    /**
     * @return UserDto[]
     */
    public function getLikesByPostId(string $postId): array
    {
        $likedBy = Like::where('post_id', $postId)->with('user')->get()->pluck('user');
        $likedByDto = $likedBy->map(fn ($user) => $this->userHydrator->modelToDto($user));

        return $likedByDto->toArray();
    }

    public function destroy(string $likeId): bool
    {
        return Like::where('id', $likeId)->delete() > 0;
    }
}
