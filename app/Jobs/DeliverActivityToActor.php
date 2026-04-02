<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\ActivityPubService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class DeliverActivityToActor implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public array $backoff = [60, 300, 900];

    public function __construct(
        private readonly string $inboxUrl,
        private readonly array $activity,
        private readonly string $senderId,
    ) {}

    public function handle(ActivityPubService $activityPubService): void
    {
        $sender = User::find($this->senderId);
        if (! $sender) {
            return;
        }

        $activityPubService->signAndSend($this->inboxUrl, $this->activity, $sender);
    }
}
