<?php

namespace App\Hydrators\Notifications;

use App\Dto\Notifications\ActivityPubMentionData;
use App\Models\ActivityPubActor;
use Illuminate\Notifications\DatabaseNotification;

class ActivityPubMentionHydrator
{
    public function hydrate(DatabaseNotification $notification): ActivityPubMentionData
    {
        $data = $notification->data;

        $iconUrl = $data['icon_url'] ?? null;
        $actor = ActivityPubActor::where('actor_uri', $data['actor_id'])->first();
        if ($actor?->local_icon_url) {
            $iconUrl = $actor->local_icon_url;
        }

        return new ActivityPubMentionData(
            actorId: $data['actor_id'],
            preferredUsername: $data['preferred_username'],
            displayName: $data['display_name'] ?? null,
            iconUrl: $iconUrl,
            profileUrl: $data['profile_url'] ?? null,
            postId: $data['post_id'],
            postBody: $data['post_body'] ?? null,
        );
    }
}
