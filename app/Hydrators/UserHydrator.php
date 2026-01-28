<?php

declare(strict_types=1);

namespace App\Hydrators;

use App\Http\Resources\UserDto;
use App\Http\Resources\UserStatisticsDto;
use App\Models\User;
use App\Models\UserStatistics;
use Illuminate\Routing\UrlGenerator;

class UserHydrator
{
    private UrlGenerator $urlGenerator;

    public function __construct(?UrlGenerator $urlGenerator = null)
    {
        $this->urlGenerator = $urlGenerator ?? app(UrlGenerator::class);
    }

    public function modelToDto(User $user): UserDto
    {
        $dto = new UserDto;
        $dto->id = $user->id;
        $dto->name = $user->name;
        $dto->username = $user->username;
        $dto->avatar = $user->profile?->avatar ? $this->urlGenerator->to('/files/'.$user->profile?->avatar) : null;
        $dto->header = $user->profile?->header ? $this->urlGenerator->to('/files/'.$user->profile?->header) : null;
        $dto->bio = $user->profile?->bio;
        $dto->website = $user->profile?->website;
        $dto->statistics = $this->statisticsToDto($user->statistics);
        $dto->createdAt = $user->created_at->toIso8601String();

        return $dto;
    }

    private function statisticsToDto(UserStatistics $stats): UserStatisticsDto
    {
        return new UserStatisticsDto(
            postsCount: $stats->posts_count,
            transportPostsCount: $stats->transport_posts_count,
            locationPostsCount: $stats->location_posts_count,
            followersCount: $stats->followers_count,
            followingCount: $stats->following_count,
            travelledDistance: $stats->travelled_distance,
            travelledDuration: $stats->travelled_duration,
            visitedCountriesCount: $stats->visited_countries_count,
            visitedLocationsCount: $stats->visited_locations_count,
        );
    }
}
