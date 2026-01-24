<?php

namespace App\Enums;

use App\Hydrators\Notifications\PostLikedDataHydrator;
use App\Notifications\PostLiked;
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

    public function getClassName(): string
    {
        return match ($this) {
            self::PostLiked => PostLiked::class,
        };
    }

    public function getHydratorClassName(): string
    {
        return match ($this) {
            self::PostLiked => PostLikedDataHydrator::class,
        };
    }
}
