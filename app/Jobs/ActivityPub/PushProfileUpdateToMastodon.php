<?php

namespace App\Jobs\ActivityPub;

use App\Hydrators\ActivityPub\PersonHydrator;
use App\Hydrators\ActivityPub\UpdateHydrator;
use App\Models\ActivityPubFollower;
use App\Repositories\UserRepository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class PushProfileUpdateToMastodon implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public array $backoff = [30, 120, 600];

    public function __construct(
        private readonly string $userId
    ) {}

    public function handle(UserRepository $userRepository): void
    {
        try {
            $userDto = $userRepository->getUserById($this->userId);
        } catch (ModelNotFoundException $e) {
            Log::error('PushProfileUpdateToMastodon: Failed to fetch user', ['userId' => $this->userId, 'error' => $e->getMessage()]);

            return;
        }

        $followers = ActivityPubFollower::whereFollowedUserId($this->userId)->with('actor')->get();

        if ($followers->isEmpty()) {
            Log::info('No followers to send profile update to for user: '.$userDto->username);

            return;
        }

        $actorUrl = route('ap.actor', ['username' => $userDto->username]);
        $person = new PersonHydrator()->hydrate($userDto);
        $updateActivity = new UpdateHydrator()->hydrate($actorUrl, $person, true)->toArray();

        $usedInboxes = [];

        foreach ($followers as $follower) {
            $inbox = $follower->follower_shared_inbox_url ?? $follower->follower_inbox_url;
            if ($inbox !== null) {
                if (in_array($inbox, $usedInboxes)) {
                    continue;
                }
                $usedInboxes[] = $inbox;
            }
            DeliverActivityPubActivity::dispatch($userDto, $follower->follower_actor_id, $inbox, $updateActivity);
        }
    }
}
