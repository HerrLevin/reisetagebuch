<?php

namespace App\Dto;

use App\Http\Resources\PostTypes\BasePost;
use App\Http\Resources\PostTypes\LocationPost;
use App\Http\Resources\PostTypes\TransportPost;
use Illuminate\Support\Collection;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'PostPaginationDto',
    description: 'Pagination DTO specifically for posts',
    required: ['items'],
    properties: [
        new OA\Property(
            property: 'items',
            description: 'Array of post items on the current page',
            type: 'array',
            items: new OA\Items(
                oneOf: [
                    new OA\Schema(ref: LocationPost::class),
                    new OA\Schema(ref: BasePost::class),
                    new OA\Schema(ref: TransportPost::class),
                ]
            )
        ),
    ]
)]
class PostPaginationDto extends PaginationDto
{
    /**
     * @param  int  $perPage
     * @param  ?string  $nextCursor
     * @param  ?string  $previousCursor
     * @param  LocationPost[]|BasePost[]|TransportPost[]|Collection  $items
     */
}
