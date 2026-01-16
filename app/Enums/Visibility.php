<?php

namespace App\Enums;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Visibility',
    description: 'Visibility levels for posts',
    type: 'string',
    enum: ['public', 'private', 'unlisted', 'only-authenticated']
)]
enum Visibility: string
{
    case PUBLIC = 'public'; // Visible to everyone, listed publicly on profile and search
    case PRIVATE = 'private'; // Visible only to the owner, not listed publicly
    case UNLISTED = 'unlisted'; // Visible to anyone with the link, not listed publicly
    case ONLY_AUTHENTICATED = 'only-authenticated'; // Visible to authenticated users only, not listed publicly

    public function getTraewellingVisibility(): int
    {
        return match ($this) {
            self::PUBLIC => 0,
            self::UNLISTED => 1,
            self::PRIVATE => 3,
            self::ONLY_AUTHENTICATED => 4,
        };
    }
}
