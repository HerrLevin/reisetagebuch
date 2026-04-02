<?php

namespace App\Enums;

use App\Hydrators\Notifications\PostLikedDataHydrator;
use App\Hydrators\Notifications\RemotePostBoostedHydrator;
use App\Hydrators\Notifications\RemotePostLikedHydrator;
use App\Hydrators\Notifications\RemotePostRepliedHydrator;
use App\Hydrators\Notifications\RemoteUserFollowedHydrator;
use App\Hydrators\Notifications\TraewellingCrosspostFailedHydrator;
use App\Hydrators\Notifications\UserFollowedHydrator;
use App\Hydrators\Notifications\UserRequestedFollowHydrator;
use App\Notifications\PostLiked;
use App\Notifications\RemotePostBoostedNotification;
use App\Notifications\RemotePostLikedNotification;
use App\Notifications\RemotePostRepliedNotification;
use App\Notifications\RemoteUserFollowedNotification;
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
    case RemotePostLiked = 'RemotePostLikedNotification';
    case RemotePostBoosted = 'RemotePostBoostedNotification';
    case RemotePostReplied = 'RemotePostRepliedNotification';
    case RemoteUserFollowed = 'RemoteUserFollowedNotification';

    public function getClassName(): string
    {
        return match ($this) {
            self::PostLiked => PostLiked::class,
            self::UserFollowed => UserFollowedNotification::class,
            self::TraewellingCrosspostFailed => TraewellingCrosspostFailedNotification::class,
            self::UserRequestedFollow => UserRequestedFollowNotification::class,
            self::RemotePostLiked => RemotePostLikedNotification::class,
            self::RemotePostBoosted => RemotePostBoostedNotification::class,
            self::RemotePostReplied => RemotePostRepliedNotification::class,
            self::RemoteUserFollowed => RemoteUserFollowedNotification::class,
        };
    }

    public function getHydratorClassName(): string
    {
        return match ($this) {
            self::PostLiked => PostLikedDataHydrator::class,
            self::UserFollowed => UserFollowedHydrator::class,
            self::TraewellingCrosspostFailed => TraewellingCrosspostFailedHydrator::class,
            self::UserRequestedFollow => UserRequestedFollowHydrator::class,
            self::RemotePostLiked => RemotePostLikedHydrator::class,
            self::RemotePostBoosted => RemotePostBoostedHydrator::class,
            self::RemotePostReplied => RemotePostRepliedHydrator::class,
            self::RemoteUserFollowed => RemoteUserFollowedHydrator::class,
        };
    }
}
