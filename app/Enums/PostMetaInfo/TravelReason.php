<?php

namespace App\Enums\PostMetaInfo;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'TravelReason',
    description: 'Reasons for travel associated with transport posts',
    type: 'string',
    enum: ['commute', 'business', 'leisure', 'crew', 'errand', 'other']
)]
enum TravelReason: string
{
    case COMMUTE = 'commute';
    case BUSINESS = 'business';
    case LEISURE = 'leisure';
    case CREW = 'crew';
    case ERRAND = 'errand';
    case OTHER = 'other';

    public function getTraewellingReasonIdentifier(): int
    {
        return match ($this) {
            self::COMMUTE => 2,
            self::BUSINESS, self::CREW => 1,
            default => 0,
        };
    }
}
