<?php

namespace App\Notifications;

use App\Enums\DatabaseNotificationType;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class RemotePostLikedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly string $actorUsername,
        public readonly ?string $actorDisplayName,
        public readonly ?string $actorAvatar,
        public readonly string $actorInstance,
        public readonly string $postId,
        public readonly ?string $postBody,
    ) {}

    public function databaseType(object $notifiable): DatabaseNotificationType
    {
        return DatabaseNotificationType::RemotePostLiked;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'actor_username' => $this->actorUsername,
            'actor_display_name' => $this->actorDisplayName,
            'actor_avatar' => $this->actorAvatar,
            'actor_instance' => $this->actorInstance,
            'post_id' => $this->postId,
            'post_body' => $this->postBody,
        ];
    }
}
