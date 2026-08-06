<?php

namespace App\Jobs\ActivityPub;

use App\Http\Resources\UserDto;
use App\Services\ActivityPubService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class DeliverActivityPubActivity implements ShouldQueue
{
    public int $tries = 4;

    public array $backoff = [30, 120, 600, 1200];

    use Queueable;

    public function __construct(
        private readonly UserDto $userDto,
        private readonly string $followerActorId,
        private readonly ?string $inbox,
        private readonly array $updateActivity
    ) {}

    public function handle(ActivityPubService $activityPub): void
    {
        $activityPub->deliverActivity($this->userDto, $this->followerActorId, $this->inbox, $this->updateActivity);
    }
}
