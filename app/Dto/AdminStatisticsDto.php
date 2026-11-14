<?php

namespace App\Dto;

use App\Traits\JsonResponseObject;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'AdminStatisticsDto',
    title: 'Admin Statistics DTO',
    description: 'Global statistics of this instance',
    required: ['users', 'activeUsersMonth', 'posts', 'postsMonth', 'invites', 'federatedServers', 'federatedServersMonth', 'remoteFollowers', 'remotePosts', 'remotePostsMonth'],
    properties: [
        new OA\Property(property: 'users', description: 'Number of registered users', type: 'integer', example: 42),
        new OA\Property(property: 'activeUsersMonth', description: 'Users updated within the last month', type: 'integer', example: 12),
        new OA\Property(property: 'posts', description: 'Number of posts', type: 'integer', example: 1337),
        new OA\Property(property: 'postsMonth', description: 'Posts created within the last month', type: 'integer', example: 120),
        new OA\Property(property: 'invites', description: 'Number of invites', type: 'integer', example: 7),
        new OA\Property(property: 'federatedServers', description: 'Number of known ActivityPub servers', type: 'integer', example: 23),
        new OA\Property(property: 'federatedServersMonth', description: 'ActivityPub servers seen within the last month', type: 'integer', example: 9),
        new OA\Property(property: 'remoteFollowers', description: 'Number of followers from other ActivityPub servers', type: 'integer', example: 64),
        new OA\Property(property: 'remotePosts', description: 'Number of posts received from other ActivityPub servers', type: 'integer', example: 512),
        new OA\Property(property: 'remotePostsMonth', description: 'Remote posts received within the last month', type: 'integer', example: 80),
    ],
)]
readonly class AdminStatisticsDto
{
    use JsonResponseObject;

    public function __construct(
        public int $users,
        public int $activeUsersMonth,
        public int $posts,
        public int $postsMonth,
        public int $invites,
        public int $federatedServers,
        public int $federatedServersMonth,
        public int $remoteFollowers,
        public int $remotePosts,
        public int $remotePostsMonth,
    ) {}
}
