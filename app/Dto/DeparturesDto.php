<?php

declare(strict_types=1);

namespace App\Dto;

use App\Dto\MotisApi\StopDto;
use App\Dto\MotisApi\StopTimeDto;
use Illuminate\Support\Collection;

class DeparturesDto
{
    public readonly StopDto $stop;
    /** @var Collection|StopTimeDto[] */
    public readonly Collection|array $departures;

    public function __construct(
        StopDto $stop,
        Collection $departures
    ) {
        $this->stop       = $stop;
        $this->departures = $departures;
    }
}
