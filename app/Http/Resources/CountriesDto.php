<?php

namespace App\Http\Resources;

use App\Traits\JsonResponseObject;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'CountriesDto',
    description: 'Countries a user has visited or merely travelled through',
    required: ['visited', 'transitedOnly'],
    type: 'object'
)]
class CountriesDto
{
    use JsonResponseObject;

    public function __construct(
        #[OA\Property('visited', description: 'ISO A2 codes of countries the user has visited (location post, or a transport post origin/destination)', type: 'array', items: new OA\Items(type: 'string'))]
        public array $visited,

        #[OA\Property('transitedOnly', description: 'ISO A2 codes of countries the user has only travelled through (crossed by a non-flight transport post route, without a stop)', type: 'array', items: new OA\Items(type: 'string'))]
        public array $transitedOnly,
    ) {}
}
