<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Hydrators\ActivityPub\UndoFollowHydrator;
use App\Repositories\UserRepository;
use App\Repositories\UserStatisticsRepository;
use App\Services\ActivityPubService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Str;

class SendUndoFollowToRemoteActor implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public array $backoff = [30, 120, 600];

    public function __construct(
        private readonly string $userId,
        private readonly string $remoteActorId,
        private readonly string $followActivityId,
        private readonly string $inboxUrl,
    ) {}

    public function handle(
        ActivityPubService $activityPub,
        UserRepository $userRepository,
        UndoFollowHydrator $hydrator,
        UserStatisticsRepository $userStatisticsRepository,
    ): void {
        $userDto = $userRepository->getUserById($this->userId);
        $actorUrl = route('ap.actor', ['username' => $userDto->username]);

        $undoActivity = $hydrator->hydrate(
            undoActivityId: $actorUrl.'#undos/'.Str::uuid(),
            actorUrl: $actorUrl,
            followActivityId: $this->followActivityId,
            remoteActorId: $this->remoteActorId,
        )->toArray();

        $activityPub->deliverActivity($userDto, $this->remoteActorId, $this->inboxUrl, $undoActivity);
        $userStatisticsRepository->decrementFollowingCount($this->userId);
    }
}
