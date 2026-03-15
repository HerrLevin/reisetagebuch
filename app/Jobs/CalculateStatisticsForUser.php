<?php

namespace App\Jobs;

use App\Repositories\PostRepository;
use App\Repositories\UserRepository;
use App\Repositories\UserStatisticsRepository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class CalculateStatisticsForUser implements ShouldQueue
{
    use Queueable;

    private string $userId;

    public function __construct(string $userId)
    {
        $this->userId = $userId;
    }

    public function handle(): void
    {
        $repository = app(UserStatisticsRepository::class);
        $postRepository = app(PostRepository::class);
        $userRepository = app(UserRepository::class);

        $followCounts = $userRepository->getFollowCountsForUser($this->userId);
        $posts = $postRepository->getPostCountsForUser($this->userId);
        $distance = $postRepository->getTotalDistanceForUser($this->userId);
        $duration = $postRepository->getTotalDurationForUser($this->userId);
        $visitedLocations = $postRepository->getVisitedLocationsForUser($this->userId);

        $repository->setData(
            $this->userId,
            $posts['total'],
            $posts['transport'],
            $posts['location'],
            $visitedLocations,
            $followCounts['followers'],
            $followCounts['followings'],
            $distance,
            $duration,
        );
    }
}
