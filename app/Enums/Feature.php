<?php

namespace App\Enums;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Feature',
    type: 'string',
    enum: [Feature::REGISTRATION, Feature::INVITE, Feature::HORIZON, Feature::TELESCOPE],
)]
enum Feature: string
{
    case REGISTRATION = 'registration';
    case INVITE = 'invite';
    case HORIZON = 'horizon';
    case TELESCOPE = 'telescope';
}
