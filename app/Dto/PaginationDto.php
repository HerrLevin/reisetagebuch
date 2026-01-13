<?php

namespace App\Dto;

use App\Traits\JsonResponseObject;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;

class PaginationDto implements Arrayable
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
