<?php

namespace App\Hydrators\Notifications;

use App\Dto\Notifications\ActivityPubUserFollowedData;
use App\Models\ActivityPubActor;
use Illuminate\Notifications\DatabaseNotification;

class ActivityPubUserFollowedHydrator
{
    public function hydrate(DatabaseNotification $notification): ActivityPubUserFollowedData
    {
        $data = $notification->data;

        $iconUrl = $data['follower_icon_url'] ?? null;
        $actor = ActivityPubActor::where('actor_uri', $data['follower_actor_id'])->first();
        if ($actor?->local_icon_url) {
            $iconUrl = $actor->local_icon_url;
        }

        return new ActivityPubUserFollowedData(
            followerActorId: $data['follower_actor_id'],
            followerPreferredUsername: $data['follower_preferred_username'],
            followerDisplayName: $data['follower_display_name'] ?? null,
            followerIconUrl: $iconUrl,
            followerProfileUrl: $data['follower_profile_url'] ?? null,
        );
    }
}
