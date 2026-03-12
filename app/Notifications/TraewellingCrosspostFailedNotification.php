<?php

namespace App\Notifications;

use App\Enums\DatabaseNotificationType;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TraewellingCrosspostFailedNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly string $postId,
        private readonly string $errorMessage,
    ) {}

    public function databaseType(object $notifiable): DatabaseNotificationType
    {
        return DatabaseNotificationType::TraewellingCrosspostFailed;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'post_id' => $this->postId,
            'error_message' => $this->errorMessage,
        ];
    }
}
