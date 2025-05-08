<?php

namespace App\Dto;

use Illuminate\Support\Collection;

class PaginationDto
{

    /**
     * @param int $perPage
     * @param ?string $nextCursor
     * @param ?string $previousCursor
     * @template T
     * @param T[]|Collection $items
     */
    public function __construct(
        public int $perPage,
        public ?string $nextCursor,
        public ?string $previousCursor,
        public array|Collection $items
    ) {}
}
