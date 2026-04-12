<?php

namespace App\Dto;

use App\Traits\JsonResponseObject;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ImprintDto',
    title: 'Imprint DTO',
    description: 'Data Transfer Object representing the configured imprint text',
    required: ['content'],
    properties: [
        new OA\Property(
            property: 'content',
            description: 'The imprint content, rendered as free-form text',
            type: 'string',
            nullable: true,
        ),
    ],
)]
readonly class ImprintDto
{
    use JsonResponseObject;

    public function __construct(
        public ?string $content,
    ) {}
}
