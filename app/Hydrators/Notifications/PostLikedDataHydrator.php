<?php

namespace App\Hydrators\Notifications;

use App\Dto\Notifications\PostLikedData;
use Illuminate\Notifications\DatabaseNotification;

class PostLikedDataHydrator
{
    public function hydrate(DatabaseNotification $notification): PostLikedData
    {
        $data = $notification->data;

        return new PostLikedData(
            postId: $data['post_id'],
            postBody: $data['post_body'] ? substr($data['post_body'], 0, 50) : null,
            likedByUserId: $data['liker']['id'],
            likedByUserName: $data['liker']['username'],
            likedByUserDisplayName: $data['liker']['name'],
            likedByUserAvatarUrl: $data['liker']['avatar'],
            postSummary: null // todo: implement post summary logic if needed
        );
    }
}
