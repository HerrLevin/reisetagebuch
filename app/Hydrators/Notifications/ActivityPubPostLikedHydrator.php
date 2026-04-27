<?php

namespace App\Hydrators\Notifications;

use App\Dto\Notifications\ActivityPubPostLikedData;
use Illuminate\Notifications\DatabaseNotification;

class ActivityPubPostLikedHydrator
{
    public function hydrate(DatabaseNotification $notification): ActivityPubPostLikedData
    {
        $data = $notification->data;

        return new ActivityPubPostLikedData(
            actorId: $data['actor_id'],
            preferredUsername: $data['preferred_username'],
            displayName: $data['display_name'] ?? null,
            iconUrl: $data['icon_url'] ?? null,
            profileUrl: $data['profile_url'] ?? null,
            postId: $data['post_id'] ?? null,
            postBody: $data['post_body'] ?? null,
            postSummary: $data['post_summary'] ?? null,
        );
    }
}
