<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\ActivityPubRemoteFollow;
use Illuminate\Support\Collection;

class ActivityPubRemoteFollowRepository
{
    public function findByUserAndActor(string $userId, string $remoteActorId): ?ActivityPubRemoteFollow
    {
        return ActivityPubRemoteFollow::where('local_user_id', $userId)
            ->where('remote_actor_id', $remoteActorId)
            ->first();
    }

    public function listForUser(string $userId): Collection
    {
        return ActivityPubRemoteFollow::where('local_user_id', $userId)
            ->with('actor')
            ->orderByDesc('created_at')
            ->get();
    }

    public function create(
        string $userId,
        string $remoteActorId,
        string $inboxUrl,
        ?string $sharedInboxUrl,
        string $followActivityId,
    ): ActivityPubRemoteFollow {
        return ActivityPubRemoteFollow::create([
            'local_user_id' => $userId,
            'remote_actor_id' => $remoteActorId,
            'remote_actor_inbox_url' => $inboxUrl,
            'remote_actor_shared_inbox_url' => $sharedInboxUrl,
            'follow_activity_id' => $followActivityId,
            'state' => 'pending',
        ]);
    }

    public function updateState(string $userId, string $remoteActorId, string $state): void
    {
        ActivityPubRemoteFollow::where('local_user_id', $userId)
            ->where('remote_actor_id', $remoteActorId)
            ->update(['state' => $state]);
    }

    public function updateStateByFollowActivityId(string $userId, string $followActivityId, string $state): void
    {
        ActivityPubRemoteFollow::where('local_user_id', $userId)
            ->where('follow_activity_id', $followActivityId)
            ->update(['state' => $state]);
    }

    public function delete(string $userId, string $remoteActorId): ?ActivityPubRemoteFollow
    {
        $record = $this->findByUserAndActor($userId, $remoteActorId);
        $record?->delete();

        return $record;
    }
}
