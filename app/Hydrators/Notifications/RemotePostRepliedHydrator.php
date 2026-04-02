<?php

namespace App\Hydrators\Notifications;

use App\Dto\Notifications\RemotePostRepliedData;
use Illuminate\Notifications\DatabaseNotification;

class RemotePostRepliedHydrator
{
    public function hydrate(DatabaseNotification $notification): RemotePostRepliedData
    {
        $data = $notification->data;

        return new RemotePostRepliedData(
            actorUsername: $data['actor_username'],
            actorDisplayName: $data['actor_display_name'] ?? null,
            actorAvatar: $data['actor_avatar'] ?? null,
            actorInstance: $data['actor_instance'],
            postId: $data['post_id'],
            postBody: $data['post_body'] ?? null,
            replyContent: $data['reply_content'] ?? null,
        );
    }
}
