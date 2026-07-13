<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Hydrators\ActivityPub\FollowHydrator;
use App\Repositories\ActivityPubRemoteFollowRepository;
use App\Repositories\UserRepository;
use App\Services\ActivityPubService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SendFollowToRemoteActor implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public array $backoff = [30, 120, 600];

    public function __construct(
        private readonly string $userId,
        private readonly string $remoteActorId,
    ) {}

    public function handle(
        ActivityPubService $activityPub,
        UserRepository $userRepository,
        ActivityPubRemoteFollowRepository $remoteFollowRepository,
        FollowHydrator $hydrator,
    ): void {
        $remoteFollow = $remoteFollowRepository->findByUserAndActor($this->userId, $this->remoteActorId);

        if (! $remoteFollow) {
            Log::info('SendFollowToRemoteActor: remote follow record not found, skipping', [
                'userId' => $this->userId,
                'remoteActorId' => $this->remoteActorId,
            ]);

            return;
        }

        $userDto = $userRepository->getUserById($this->userId);
        $actorUrl = route('ap.actor', ['username' => $userDto->username]);

        $followActivity = $hydrator->hydrate(
            followActivityId: $remoteFollow->follow_activity_id,
            actorUrl: $actorUrl,
            remoteActorId: $this->remoteActorId,
        )->toArray();

        $inbox = $remoteFollow->remote_actor_shared_inbox_url ?? $remoteFollow->remote_actor_inbox_url;

        $activityPub->deliverActivity($userDto, $this->remoteActorId, $inbox, $followActivity);
    }
}
