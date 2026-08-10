<?php

namespace App\Notifications;

use App\Enums\DatabaseNotificationType;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ActivityPubUserRequestedFollowNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly string $followRequestId,
        public readonly string $followerActorId,
        public readonly string $followerPreferredUsername,
        public readonly ?string $followerDisplayName = null,
        public readonly ?string $followerIconUrl = null,
        public readonly ?string $followerProfileUrl = null,
    ) {}

    public function databaseType(object $notifiable): DatabaseNotificationType
    {
        return DatabaseNotificationType::ActivityPubUserRequestedFollow;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'follow_request_id' => $this->followRequestId,
            'reference_id' => $this->followRequestId,
            'follower_actor_id' => $this->followerActorId,
            'follower_preferred_username' => $this->followerPreferredUsername,
            'follower_display_name' => $this->followerDisplayName,
            'follower_icon_url' => $this->followerIconUrl,
            'follower_profile_url' => $this->followerProfileUrl,
        ];
    }
}
