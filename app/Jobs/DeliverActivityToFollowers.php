<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class DeliverActivityToFollowers implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly string $userId,
        private readonly array $activity,
    ) {}

    public function handle(): void
    {
        $user = User::find($this->userId);
        if (! $user) {
            return;
        }

        $followers = $user->activityPubFollowers()->get();

        // Collect unique inboxes, preferring shared inbox
        $inboxes = [];
        foreach ($followers as $follower) {
            $inbox = $follower->follower_shared_inbox ?? $follower->follower_inbox;
            $inboxes[$inbox] = true;
        }

        foreach (array_keys($inboxes) as $inbox) {
            DeliverActivityToActor::dispatch($inbox, $this->activity, $this->userId);
        }
    }
}
