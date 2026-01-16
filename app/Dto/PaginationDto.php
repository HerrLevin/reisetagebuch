<?php

namespace App\Dto;

use App\Traits\JsonResponseObject;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'PaginationDto',
    description: 'A generic pagination DTO',
    required: ['perPage', 'items', 'nextCursor', 'previousCursor'],
    properties: [
        new OA\Property(
            property: 'perPage',
            description: 'Number of items per page',
            type: 'integer'
        ),
        new OA\Property(
            property: 'nextCursor',
            description: 'Cursor for the next page',
            type: 'string',
            nullable: true
        ),
        new OA\Property(
            property: 'previousCursor',
            description: 'Cursor for the previous page',
            type: 'string',
            nullable: true
        ),
    ]
)]
abstract class PaginationDto implements Arrayable
{
    use JsonResponseObject;

    /**
     * @template T
     *
     * @param  T[]|Collection  $items
     */
    public function __construct(
        public int $perPage,
        public ?string $nextCursor,
        public ?string $previousCursor,
        public array|Collection $items
    ) {}
}
