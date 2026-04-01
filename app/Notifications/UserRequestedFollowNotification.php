<?php

namespace App\Notifications;

use App\Enums\DatabaseNotificationType;
use App\Http\Resources\UserDto;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class UserRequestedFollowNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly UserDto $follower,
        public readonly string $referenceId = '',
    ) {}

    public function databaseType(object $notifiable): DatabaseNotificationType
    {
        return DatabaseNotificationType::UserRequestedFollow;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'follower' => $this->follower,
            'reference_id' => $this->referenceId,
        ];
    }
}
