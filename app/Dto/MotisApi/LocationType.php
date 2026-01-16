<?php

namespace App\Dto\MotisApi;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'MotisLocationType',
    type: 'string',
    enum: ['ADDRESS', 'PLACE', 'STOP'],
)]
enum LocationType: string
{
    case ADDRESS = 'ADDRESS';
    case PLACE = 'PLACE';
    case STOP = 'STOP';
}
