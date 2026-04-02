<?php

namespace App\Hydrators\Notifications;

use App\Dto\Notifications\RemotePostBoostedData;
use Illuminate\Notifications\DatabaseNotification;

class RemotePostBoostedHydrator
{
    public function hydrate(DatabaseNotification $notification): RemotePostBoostedData
    {
        $data = $notification->data;

        return new RemotePostBoostedData(
            actorUsername: $data['actor_username'],
            actorDisplayName: $data['actor_display_name'] ?? null,
            actorAvatar: $data['actor_avatar'] ?? null,
            actorInstance: $data['actor_instance'],
            postId: $data['post_id'],
            postBody: $data['post_body'] ?? null,
        );
    }
}
