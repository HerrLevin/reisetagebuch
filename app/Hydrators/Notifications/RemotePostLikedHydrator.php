<?php

namespace App\Hydrators\Notifications;

use App\Dto\Notifications\RemotePostLikedData;
use Illuminate\Notifications\DatabaseNotification;

class RemotePostLikedHydrator
{
    public function hydrate(DatabaseNotification $notification): RemotePostLikedData
    {
        $data = $notification->data;

        return new RemotePostLikedData(
            actorUsername: $data['actor_username'],
            actorDisplayName: $data['actor_display_name'] ?? null,
            actorAvatar: $data['actor_avatar'] ?? null,
            actorInstance: $data['actor_instance'],
            postId: $data['post_id'],
            postBody: $data['post_body'] ?? null,
        );
    }
}
