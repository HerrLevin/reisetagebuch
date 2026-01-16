<?php

declare(strict_types=1);

namespace App\Dto;

use App\Dto\MotisApi\StopDto;
use App\Dto\MotisApi\StopTimeDto;
use Illuminate\Support\Collection;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'DeparturesDto',
    description: 'Data Transfer Object for Departures at a Stop',
    required: ['stop', 'departures'],
    type: 'object'
)]
class DeparturesDto
{
    #[OA\Property(
        property: 'stop',
        ref: StopDto::class,
        description: 'Stop Data Transfer Object'
    )]
    public readonly StopDto $stop;

    #[OA\Property(
        property: 'departures',
        description: 'Collection of departure stop times',
        type: 'array',
        items: new OA\Items(ref: StopTimeDto::class)
    )]
    /** @var Collection|StopTimeDto[] */
    public readonly Collection|array $departures;

    public function __construct(
        StopDto $stop,
        Collection $departures
    ) {
        $this->stop = $stop;
        $this->departures = $departures;
    }
}
