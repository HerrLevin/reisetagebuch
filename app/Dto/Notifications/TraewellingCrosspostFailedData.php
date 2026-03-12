<?php

namespace App\Dto\Notifications;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'TraewellingCrosspostFailedData',
    description: 'Data for a traewelling crosspost failed notification',
    required: [
        'postId',
        'errormessage',
    ],
    properties: [
        new OA\Property(
            property: 'postId',
            description: 'ID of the liked post',
            type: 'string',
            format: 'uuid'
        ),
        new OA\Property(
            property: 'errorMessage',
            description: 'Error message describing the reason for the crosspost failure',
            type: 'string',
            format: 'text'
        ),
    ]
)]
readonly class TraewellingCrosspostFailedData
{
    public function __construct(
        public string $postId,
        public string $errorMessage,
    ) {}
}
