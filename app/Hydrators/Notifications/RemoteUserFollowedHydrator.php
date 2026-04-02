<?php

namespace App\Hydrators\Notifications;

use App\Dto\Notifications\RemoteUserFollowedData;
use Illuminate\Notifications\DatabaseNotification;

class RemoteUserFollowedHydrator
{
    public function hydrate(DatabaseNotification $notification): RemoteUserFollowedData
    {
        $data = $notification->data;

        return new RemoteUserFollowedData(
            actorUsername: $data['actor_username'],
            actorDisplayName: $data['actor_display_name'] ?? null,
            actorAvatar: $data['actor_avatar'] ?? null,
            actorInstance: $data['actor_instance'],
        );
    }
}
