<?php

namespace App\Jobs;

use App\Enums\DatabaseNotificationType;
use App\Repositories\NotificationRepository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class DeletePostLikedNotification implements ShouldQueue
{
    use Queueable;

    private string $userId;

    private string $likeId;

    private NotificationRepository $notificationRepository;

    public function __construct(string $userId, string $likeId, ?NotificationRepository $notificationRepository = null)
    {
        $this->userId = $userId;
        $this->likeId = $likeId;
        $this->notificationRepository = $notificationRepository ?? app(NotificationRepository::class);
    }

    public function handle(): void
    {
        $this->notificationRepository->deleteReferencedNotification(
            $this->userId,
            DatabaseNotificationType::POST_LIKED,
            $this->likeId
        );
    }
}
