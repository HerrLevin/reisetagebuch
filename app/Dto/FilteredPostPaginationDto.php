<?php

namespace App\Dto;

use App\Http\Resources\PostTypes\BasePost;
use App\Http\Resources\PostTypes\LocationPost;
use App\Http\Resources\PostTypes\TransportPost;
use Illuminate\Support\Collection;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'FilteredPostPaginationDto',
    description: 'Pagination DTO specifically for posts',
    required: ['perPage', 'items', 'nextCursor', 'previousCursor', 'availableTags'],
    properties: [
        new OA\Property(property: 'availableTags', type: 'array', items: new OA\Items(type: 'string')),
        new OA\Property(
            property: 'items',
            description: 'Array of post items on the current page',
            type: 'array',
            items: new OA\Items(
                anyOf: [
                    new OA\Schema(ref: LocationPost::class),
                    new OA\Schema(ref: BasePost::class),
                    new OA\Schema(ref: TransportPost::class),
                ]
            )
        ),
    ]
)]
class FilteredPostPaginationDto extends PostPaginationDto
{
    /**
     * @param  string[]  $availableTags
     * @param  LocationPost[]|BasePost[]|TransportPost[]|Collection  $items
     */
    public function __construct(
        public int $perPage,
        public ?string $nextCursor,
        public ?string $previousCursor,
        public array|Collection $items,
        public array $availableTags,
    ) {}
}
