<?php

namespace App\Dto;

use App\Traits\JsonResponseObject;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'LikeResponseDto',
    required: ['likedByUser', 'likeCount'],
    properties: [
        new OA\Property(
            property: 'likedByUser',
            description: 'Indicates if the post is liked by the user',
            type: 'boolean'
        ),
        new OA\Property(
            property: 'likeCount',
            description: 'Total number of likes on the post',
            type: 'integer'
        ),
    ],
    type: 'object'
)]
readonly class LikeDto
{
    use JsonResponseObject;

    public bool $likedByUser;

    public int $likeCount;

    public function __construct(bool $likedByUser, int $likeCount)
    {
        $this->likedByUser = $likedByUser;
        $this->likeCount = $likeCount;
    }
}
