<?php

namespace App\Dto;

use App\Models\RequestLocation;
use App\Traits\JsonResponseObject;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'RequestLocationDto',
    description: 'Data Transfer Object for Request Location',
    required: ['fetched', 'toFetch', 'updatedAt', 'lastRequestedAt'],
    type: 'object'
)]
class RequestLocationDto
{
    use JsonResponseObject;

    #[OA\Property(
        property: 'fetched',
        description: 'Number of locations fetched',
        type: 'integer'
    )]
    public int $fetched;

    #[OA\Property(
        property: 'toFetch',
        description: 'Number of locations to fetch',
        type: 'integer'
    )]
    public int $toFetch;

    #[OA\Property(
        property: 'updatedAt',
        description: 'Last update time of the request location data',
        type: 'string',
        format: 'date-time'
    )]
    public string $updatedAt;

    #[OA\Property(
        property: 'lastRequestedAt',
        description: 'Last time a request was made for location data',
        type: 'string',
        format: 'date-time',
        nullable: true
    )]
    public ?string $lastRequestedAt;

    public static function fromModel(RequestLocation $model): self
    {
        $dto = new self;
        $dto->fetched = $model->fetched;
        $dto->toFetch = $model->to_fetch;
        $dto->updatedAt = $model->updated_at->toIso8601String() ?? '';
        $dto->lastRequestedAt = $model->last_requested_at?->toIso8601String() ?? null;

        return $dto;
    }
}
