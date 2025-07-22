<?php

namespace App\Dto;

use Illuminate\Support\Collection;

class PaginationDto
{
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
