<?php

namespace App\Jobs;

use App\Hydrators\UserHydrator;
use App\Models\ActivityPubFollower;
use App\Models\User;
use App\Services\ActivityPubService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class PushDeleteToMastodon implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public array $backoff = [30, 120, 600];

    public function __construct(
        private readonly string $postId,
        private readonly string $userId,
        private readonly string $username
    ) {}

    public function handle(ActivityPubService $activityPub): void
    {
        $user = User::find($this->userId);
        if (! $user) {
            Log::warning('PushDeleteToMastodon: User not found', ['userId' => $this->userId]);

            return;
        }

        $userDto = app(UserHydrator::class)->modelToDto($user);
        $followers = ActivityPubFollower::whereFollowedUserId($this->userId)->get();

        if ($followers->isEmpty()) {
            return;
        }

        $postObjectUrl = route('ap.post-object', ['id' => $this->postId]);

        $deleteActivity = [
            '@context' => 'https://www.w3.org/ns/activitystreams',
            'id' => route('ap.post', ['id' => $this->postId]).'/delete',
            'type' => 'Delete',
            'actor' => route('ap.actor', ['username' => $this->username]),
            'to' => ['https://www.w3.org/ns/activitystreams#Public'],
            'object' => [
                'id' => $postObjectUrl,
                'type' => 'Tombstone',
            ],
        ];

        $usedInboxes = [];

        foreach ($followers as $follow) {
            $inbox = $follow->follower_shared_inbox_url ?? $follow->follower_inbox_url;
            if ($inbox !== null) {
                if (in_array($inbox, $usedInboxes)) {
                    continue;
                }
                $usedInboxes[] = $inbox;
            }
            $activityPub->deliverActivity($userDto, $follow->follower_actor_id, $inbox, $deleteActivity);
        }
    }
}
