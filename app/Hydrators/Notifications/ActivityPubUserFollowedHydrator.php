<?php

namespace App\Hydrators\Notifications;

use App\Dto\Notifications\ActivityPubUserFollowedData;
use Illuminate\Notifications\DatabaseNotification;

class ActivityPubUserFollowedHydrator
{
    public function hydrate(DatabaseNotification $notification): ActivityPubUserFollowedData
    {
        $data = $notification->data;

        return new ActivityPubUserFollowedData(
            followerActorId: $data['follower_actor_id'],
            followerPreferredUsername: $data['follower_preferred_username'],
            followerDisplayName: $data['follower_display_name'] ?? null,
            followerIconUrl: $data['follower_icon_url'] ?? null,
            followerProfileUrl: $data['follower_profile_url'] ?? null,
        );
    }
}
