<?php

namespace App\Repositories;

use App\Http\Resources\UserDto;
use App\Hydrators\ActivityPub\ActivityPubActorHydrator;
use App\Hydrators\UserHydrator;
use App\Models\ActivityPubActor;
use App\Models\ActivityPubLike;
use App\Models\Like;

class LikeRepository
{
    public function __construct(
        private readonly UserHydrator $userHydrator,
        private readonly ActivityPubActorHydrator $activityPubActorHydrator,
    ) {}

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

        $apLikes = ActivityPubLike::where('post_id', $postId)->get();
        $actorsByUri = ActivityPubActor::whereIn('actor_uri', $apLikes->pluck('actor_id'))
            ->get()
            ->keyBy('actor_uri');

        $apLikedByDto = $apLikes->map(fn (ActivityPubLike $like) => $this->activityPubActorHydrator->modelToDto(
            $actorsByUri->get($like->actor_id),
            $like->actor_id,
            $like->created_at->toIso8601String(),
        ))->toArray();

        return $likedByDto->concat($apLikedByDto)->toArray();
    }

    public function destroy(string $likeId): bool
    {
        return Like::where('id', $likeId)->delete() > 0;
    }
}
