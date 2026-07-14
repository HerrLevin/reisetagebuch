<?php

namespace App\Repositories;

use App\Http\Resources\PostTypes\BasePost;
use App\Hydrators\ActivityPub\ActivityPubPostHydrator;
use App\Models\ActivityPubActor;
use App\Models\ActivityPubPost;
use App\Models\ActivityPubPostLike;
use App\Models\ActivityPubRemoteFollow;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ActivityPubPostRepository
{
    public function __construct(
        private readonly ActivityPubPostHydrator $hydrator = new ActivityPubPostHydrator,
    ) {}

    public function findOrCreateByActivityId(
        string $activityPubActorId,
        string $activityId,
        ?string $url,
        ?string $content,
        Carbon $publishedAt,
    ): ActivityPubPost {
        return ActivityPubPost::firstOrCreate(
            ['activity_id' => $activityId],
            [
                'id' => Str::uuid(),
                'activity_pub_actor_id' => $activityPubActorId,
                'url' => $url,
                'content' => $content,
                'published_at' => $publishedAt,
            ]
        );
    }

    public function deleteByActivityId(string $activityId): void
    {
        ActivityPubPost::where('activity_id', $activityId)->delete();
    }

    public function findById(string $id): ?ActivityPubPost
    {
        return ActivityPubPost::with('actor')->find($id);
    }

    public function findByIdForUser(string $id, ?string $userId): ?BasePost
    {
        $query = ActivityPubPost::with('actor')->withCount('likes');

        if ($userId) {
            $query->withExists(['userLikes as liked_by_user' => function ($q) use ($userId) {
                $q->where('user_id', $userId);
            }]);
        }

        $post = $query->find($id);

        return $post ? $this->hydrator->modelToDto($post) : null;
    }

    /**
     * @return Collection<int, BasePost>
     */
    public function getForFollowedActors(string $userId, Carbon $before, int $limit): Collection
    {
        $followedActorUris = ActivityPubRemoteFollow::where('local_user_id', $userId)
            ->pluck('remote_actor_id');

        if ($followedActorUris->isEmpty()) {
            return collect();
        }

        $actorIds = ActivityPubActor::whereIn('actor_uri', $followedActorUris)->pluck('id');

        if ($actorIds->isEmpty()) {
            return collect();
        }

        return ActivityPubPost::with('actor')
            ->whereIn('activity_pub_actor_id', $actorIds)
            ->where('published_at', '<', $before)
            ->withCount('likes')
            ->withExists(['userLikes as liked_by_user' => function ($q) use ($userId) {
                $q->where('user_id', $userId);
            }])
            ->orderByDesc('published_at')
            ->limit($limit)
            ->get()
            ->map(fn (ActivityPubPost $post) => $this->hydrator->modelToDto($post));
    }

    public function createLike(string $userId, string $postId, string $activityId): ActivityPubPostLike
    {
        return ActivityPubPostLike::create([
            'id' => Str::uuid(),
            'user_id' => $userId,
            'activity_pub_post_id' => $postId,
            'activity_id' => $activityId,
        ]);
    }

    public function deleteLike(string $userId, string $postId): void
    {
        ActivityPubPostLike::where('user_id', $userId)
            ->where('activity_pub_post_id', $postId)
            ->delete();
    }

    public function getLikeActivityId(string $userId, string $postId): ?string
    {
        return ActivityPubPostLike::where('user_id', $userId)
            ->where('activity_pub_post_id', $postId)
            ->value('activity_id');
    }

    public function getLikeCount(string $postId): int
    {
        return ActivityPubPostLike::where('activity_pub_post_id', $postId)->count();
    }

    public function isLikedByUser(string $userId, string $postId): bool
    {
        return ActivityPubPostLike::where('user_id', $userId)
            ->where('activity_pub_post_id', $postId)
            ->exists();
    }
}
