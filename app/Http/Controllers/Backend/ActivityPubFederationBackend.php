<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Jobs\SendFollowToRemoteActor;
use App\Jobs\SendUndoFollowToRemoteActor;
use App\Repositories\ActivityPubRemoteFollowRepository;
use App\Repositories\UserRepository;
use App\Services\ActivityPubContentSanitizer;
use App\Services\ActivityPubService;
use Illuminate\Support\Str;

class ActivityPubFederationBackend extends Controller
{
    public function __construct(
        private readonly ActivityPubService $activityPubService,
        private readonly ActivityPubRemoteFollowRepository $remoteFollowRepository,
        private readonly UserRepository $userRepository,
        private readonly ActivityPubContentSanitizer $contentSanitizer,
    ) {}

    public function resolveHandle(string $userId, string $handle): ?array
    {
        $profile = $this->activityPubService->resolveActorByHandle($handle);
        if ($profile === null) {
            return null;
        }

        $actorId = $profile['actorId'];
        $followState = $this->remoteFollowRepository->findByUserAndActor($userId, $actorId)?->state;

        return [
            'actor_id' => $actorId,
            'display_name' => $profile['name'],
            'preferred_username' => $profile['preferredUsername'],
            'summary' => $this->contentSanitizer->sanitize($profile['summary']),
            'icon_url' => $profile['iconUrl'],
            'profile_url' => $profile['url'],
            'follow_state' => $followState,
        ];
    }

    public function listFollowing(string $userId): array
    {
        return $this->remoteFollowRepository->listForUser($userId)
            ->map(fn ($follow) => [
                'actor_id' => $follow->remote_actor_id,
                'state' => $follow->state,
                'created_at' => $follow->created_at,
                'display_name' => $follow->actor?->display_name,
                'preferred_username' => $follow->actor?->preferred_username,
                'icon_url' => $follow->actor?->local_icon_url,
                'profile_url' => $follow->actor?->profile_url,
            ])
            ->values()
            ->all();
    }

    public function follow(string $userId, string $actorId): void
    {
        if ($this->remoteFollowRepository->findByUserAndActor($userId, $actorId)) {
            return;
        }

        $actor = $this->activityPubService->resolveActor($actorId);
        if ($actor === null) {
            abort(422, 'Could not reach remote actor');
        }

        $userDto = $this->userRepository->getUserById($userId);
        $actorUrl = route('ap.actor', ['username' => $userDto->username]);

        $this->remoteFollowRepository->create(
            userId: $userId,
            remoteActorId: $actorId,
            inboxUrl: $actor->inbox_url,
            sharedInboxUrl: $actor->shared_inbox_url,
            followActivityId: $actorUrl.'#follows/'.Str::uuid(),
        );

        SendFollowToRemoteActor::dispatch($userId, $actorId);
    }

    public function unfollow(string $userId, string $actorId): void
    {
        $record = $this->remoteFollowRepository->delete($userId, $actorId);

        if ($record) {
            SendUndoFollowToRemoteActor::dispatch(
                $userId,
                $actorId,
                $record->follow_activity_id,
                $record->remote_actor_shared_inbox_url ?? $record->remote_actor_inbox_url,
            );
        }
    }
}
