<?php

namespace App\Notifications;

use App\Enums\DatabaseNotificationType;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ActivityPubPostLikedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly string $actorId,
        public readonly string $preferredUsername,
        public readonly ?string $displayName = null,
        public readonly ?string $iconUrl = null,
        public readonly ?string $profileUrl = null,
        public readonly ?string $postId = null,
        public readonly ?string $postBody = null,
        public readonly ?string $postSummary = null,
    ) {}

    public function databaseType(object $notifiable): DatabaseNotificationType
    {
        return DatabaseNotificationType::ActivityPubPostLiked;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'actor_id' => $this->actorId,
            'preferred_username' => $this->preferredUsername,
            'display_name' => $this->displayName,
            'icon_url' => $this->iconUrl,
            'profile_url' => $this->profileUrl,
            'post_id' => $this->postId,
            'post_body' => $this->postBody,
            'post_summary' => $this->postSummary,
        ];
    }
}
