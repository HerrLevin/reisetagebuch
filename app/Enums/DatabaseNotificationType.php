<?php

namespace App\Enums;

use App\Hydrators\Notifications\PostLikedDataHydrator;
use App\Hydrators\Notifications\UserFollowedHydrator;
use App\Notifications\PostLiked;
use App\Notifications\UserFollowedNotification;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'NotificationType',
    description: 'Enumeration of notification types',
    type: 'string',
    enum: [DatabaseNotificationType::PostLiked]
)]
enum DatabaseNotificationType: string
{
    case PostLiked = 'PostLikedNotification';
    case UserFollowed = 'UserFollowedNotification';

    public function getClassName(): string
    {
        return match ($this) {
            self::PostLiked => PostLiked::class,
            self::UserFollowed => UserFollowedNotification::class,
        };
    }

    public function getHydratorClassName(): string
    {
        return match ($this) {
            self::PostLiked => PostLikedDataHydrator::class,
            self::UserFollowed => UserFollowedHydrator::class,
        };
    }
}
