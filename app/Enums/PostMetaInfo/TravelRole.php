<?php

namespace App\Enums\PostMetaInfo;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'TravelRole',
    description: 'Enum representing different travel roles',
    type: 'string',
    enum: ['deadhead', 'operator', 'catering']
)]
enum TravelRole: string
{
    case DEADHEAD = 'deadhead';
    case OPERATOR = 'operator';
    case CATERING = 'catering';

    public function getTraewellingIdentifier(): ?string
    {
        return match ($this) {
            self::DEADHEAD => 'Gf',
            self::OPERATOR => 'Tf',
            self::CATERING => 'catering',
        };
    }
}
