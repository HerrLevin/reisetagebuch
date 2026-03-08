<?php

namespace App\Hydrators\Notifications;

use App\Dto\Notifications\UserFollowedData;
use Illuminate\Notifications\DatabaseNotification;

class UserFollowedHydrator
{
    public function hydrate(DatabaseNotification $notification): UserFollowedData
    {
        $data = $notification->data;

        return new UserFollowedData(
            followerUserId: $data['follower']['id'],
            followerUserName: $data['follower']['username'],
            followerUserDisplayName: $data['follower']['name'],
            followerUserAvatarUrl: $data['follower']['avatar'],
        );
    }
}
