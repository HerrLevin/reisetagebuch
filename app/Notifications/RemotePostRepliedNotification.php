<?php

namespace App\Notifications;

use App\Enums\DatabaseNotificationType;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class RemotePostRepliedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly string $actorUsername,
        public readonly ?string $actorDisplayName,
        public readonly ?string $actorAvatar,
        public readonly string $actorInstance,
        public readonly string $postId,
        public readonly ?string $postBody,
        public readonly ?string $replyContent,
    ) {}

    public function databaseType(object $notifiable): DatabaseNotificationType
    {
        return DatabaseNotificationType::RemotePostReplied;
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
            'reply_content' => $this->replyContent,
        ];
    }
}
