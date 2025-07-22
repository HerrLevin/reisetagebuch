<?php

namespace App\Dto;

use App\Http\Resources\PostTypes\BasePost;
use App\Models\LocationPost;
use App\Models\TransportPost;
use Illuminate\Support\Collection;

class PostPaginationDto extends PaginationDto
{
    /**
     * @param  int  $perPage
     * @param  ?string  $nextCursor
     * @param  ?string  $previousCursor
     * @param  LocationPost[]|BasePost[]|TransportPost[]|Collection  $items
     */
}
