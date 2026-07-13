<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Hydrators\ActivityPub\LikeHydrator;
use App\Models\ActivityPubPost;
use App\Repositories\UserRepository;
use App\Services\ActivityPubService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SendApPostLikeActivity implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public array $backoff = [30, 120, 600];

    public function __construct(
        private readonly string $userId,
        private readonly string $apPostId,
        private readonly string $likeActivityId,
    ) {}

    public function handle(
        ActivityPubService $activityPub,
        UserRepository $userRepository,
        LikeHydrator $hydrator,
    ): void {
        $post = ActivityPubPost::with('actor')->find($this->apPostId);
        if (! $post) {
            Log::info('SendApPostLikeActivity: AP post not found', ['apPostId' => $this->apPostId]);

            return;
        }

        $actor = $post->actor;
        if (! $actor) {
            return;
        }

        $userDto = $userRepository->getUserById($this->userId);
        $actorUrl = route('ap.actor', ['username' => $userDto->username]);

        $likeActivity = $hydrator->hydrate(
            likeActivityId: $this->likeActivityId,
            actorUrl: $actorUrl,
            objectId: $post->activity_id,
        )->toArray();

        $inbox = $actor->shared_inbox_url ?? $actor->inbox_url;
        $activityPub->deliverActivity($userDto, $actor->actor_uri, $inbox, $likeActivity);
    }
}
