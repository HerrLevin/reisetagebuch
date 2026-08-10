<?php

namespace App\Hydrators\Notifications;

use App\Dto\Notifications\ActivityPubUserRequestedFollowData;
use App\Models\ActivityPubActor;
use Illuminate\Notifications\DatabaseNotification;

class ActivityPubUserRequestedFollowHydrator
{
    public function hydrate(DatabaseNotification $notification): ActivityPubUserRequestedFollowData
    {
        $data = $notification->data;

        $iconUrl = $data['follower_icon_url'] ?? null;
        $actor = ActivityPubActor::where('actor_uri', $data['follower_actor_id'])->first();
        if ($actor?->local_icon_url) {
            $iconUrl = $actor->local_icon_url;
        }

        return new ActivityPubUserRequestedFollowData(
            followRequestId: $data['follow_request_id'],
            followerActorId: $data['follower_actor_id'],
            followerPreferredUsername: $data['follower_preferred_username'],
            followerDisplayName: $data['follower_display_name'] ?? null,
            followerIconUrl: $iconUrl,
            followerProfileUrl: $data['follower_profile_url'] ?? null,
        );
    }
}
