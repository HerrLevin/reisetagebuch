<?php

namespace App\Http\Resources;

use App\Traits\JsonResponseObject;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'UserStatisticsDto',
    description: 'User Statistics Data Object',
    required: [
        'postsCount',
        'transportPostsCount',
        'locationPostsCount',
        'followersCount',
        'followingCount',
        'travelledDistance',
        'travelledDuration',
        'visitedCountriesCount',
        'visitedLocationsCount',
    ],
    type: 'object'
)]
class UserStatisticsDto
{
    use JsonResponseObject;

    public function __construct(
        #[OA\Property('postsCount', description: 'Total number of posts by the user', type: 'integer', format: 'int32')]
        public int $postsCount,

        #[OA\Property('transportPostsCount', description: 'Number of transport-related posts by the user', type: 'integer', format: 'int32')]
        public int $transportPostsCount,

        #[OA\Property('locationPostsCount', description: 'Number of location-related posts by the user', type: 'integer', format: 'int32')]
        public int $locationPostsCount,

        #[OA\Property('followersCount', description: 'Number of followers the user has', type: 'integer', format: 'int32')]
        public int $followersCount,

        #[OA\Property('followingCount', description: 'Number of users the user is following', type: 'integer', format: 'int32')]
        public int $followingCount,

        #[OA\Property('travelledDistance', description: 'Total distance travelled by the user in meters', type: 'integer', format: 'int32')]
        public int $travelledDistance,

        #[OA\Property('travelledDuration', description: 'Total duration of travel by the user in minutes', type: 'integer', format: 'int32')]
        public int $travelledDuration,

        #[OA\Property('visitedCountriesCount', description: 'Number of distinct countries visited by the user', type: 'integer', format: 'int32')]
        public int $visitedCountriesCount,

        #[OA\Property('visitedLocationsCount', description: 'Number of distinct locations visited by the user', type: 'integer', format: 'int32')]
        public int $visitedLocationsCount,
    ) {}
}
