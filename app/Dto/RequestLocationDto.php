<?php

namespace App\Dto;

use App\Models\RequestLocation;
use App\Traits\JsonResponseObject;

class RequestLocationDto
{
    use JsonResponseObject;

    public int $fetched;

    public int $toFetch;

    public string $updatedAt;

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
