<?php

namespace App\Enums;

use App\Hydrators\Notifications\ActivityPubUserFollowedHydrator;
use App\Hydrators\Notifications\PostLikedDataHydrator;
use App\Hydrators\Notifications\TraewellingCrosspostFailedHydrator;
use App\Hydrators\Notifications\UserFollowedHydrator;
use App\Hydrators\Notifications\UserRequestedFollowHydrator;
use App\Notifications\ActivityPubUserFollowedNotification;
use App\Notifications\PostLiked;
use App\Notifications\TraewellingCrosspostFailedNotification;
use App\Notifications\UserFollowedNotification;
use App\Notifications\UserRequestedFollowNotification;
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
    case TraewellingCrosspostFailed = 'TraewellingCrosspostFailedNotification';
    case UserRequestedFollow = 'UserRequestedFollowNotification';
    case ActivityPubUserFollowed = 'ActivityPubUserFollowedNotification';

    public function getClassName(): string
    {
        return match ($this) {
            self::PostLiked => PostLiked::class,
            self::UserFollowed => UserFollowedNotification::class,
            self::TraewellingCrosspostFailed => TraewellingCrosspostFailedNotification::class,
            self::UserRequestedFollow => UserRequestedFollowNotification::class,
            self::ActivityPubUserFollowed => ActivityPubUserFollowedNotification::class,
        };
    }

    public function getHydratorClassName(): string
    {
        return match ($this) {
            self::PostLiked => PostLikedDataHydrator::class,
            self::UserFollowed => UserFollowedHydrator::class,
            self::TraewellingCrosspostFailed => TraewellingCrosspostFailedHydrator::class,
            self::UserRequestedFollow => UserRequestedFollowHydrator::class,
            self::ActivityPubUserFollowed => ActivityPubUserFollowedHydrator::class,
        };
    }
}
