<?php

namespace App\Hydrators\Notifications;

use App\Dto\Notifications\UserRequestedFollowData;
use Illuminate\Notifications\DatabaseNotification;

class UserRequestedFollowHydrator
{
    public function hydrate(DatabaseNotification $notification): UserRequestedFollowData
    {
        $data = $notification->data;

        return new UserRequestedFollowData(
            followerUserId: $data['follower']['id'],
            followerUserName: $data['follower']['username'],
            followerUserDisplayName: $data['follower']['name'],
            followerUserAvatarUrl: $data['follower']['avatar'],
        );
    }
}
