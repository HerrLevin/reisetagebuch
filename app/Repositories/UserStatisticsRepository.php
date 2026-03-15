<?php

namespace App\Repositories;

use App\Models\User;

class UserStatisticsRepository
{
    public function storeTransportPostCreation(string $userId): void
    {
        $user = User::find($userId);
        if ($user === null) {
            return;
        }

        $user->statistics()->increment('transport_posts_count');
        $user->statistics()->increment('posts_count');
    }

    public function storeTransportPostDeletion(string $userId, int $distance, int $duration): void
    {
        $user = User::find($userId);
        if ($user === null) {
            return;
        }

        $user->statistics()->decrement('transport_posts_count');
        $user->statistics()->decrement('posts_count');
        $user->statistics()->decrement('travelled_distance', $distance);
        $user->statistics()->decrement('travelled_duration', $duration);
    }

    public function storeLocationPostCreation(string $userId): void
    {
        $user = User::find($userId);
        if ($user === null) {
            return;
        }

        $user->statistics()->increment('location_posts_count');
        $user->statistics()->increment('posts_count');
    }

    public function storeLocationPostDeletion(string $userId): void
    {
        $user = User::find($userId);
        if ($user === null) {
            return;
        }

        $user->statistics()->decrement('location_posts_count');
        $user->statistics()->decrement('posts_count');
    }

    public function storeTextPostCreation(string $userId): void
    {
        $user = User::find($userId);
        if ($user === null) {
            return;
        }

        $user->statistics()->increment('posts_count');
    }

    public function storeTextPostDeletion(string $userId): void
    {
        $user = User::find($userId);
        if ($user === null) {
            return;
        }

        $user->statistics()->decrement('posts_count');
    }

    public function updateTransportPostStats(string $userId, int $newDistance, int $newDuration, ?int $oldDistance = null, ?int $oldDuration = null): void
    {
        $user = User::find($userId);
        if ($user === null) {
            return;
        }

        if (! empty($oldDistance)) {
            $user->statistics()->decrement('travelled_distance', $oldDistance);
        }

        if (! empty($oldDuration)) {
            $user->statistics()->decrement('travelled_duration', $oldDuration);
        }

        $user->statistics()->increment('travelled_distance', $newDistance);
        $user->statistics()->increment('travelled_duration', $newDuration);
    }

    public function setData(
        string $userId,
        int $postsCount,
        int $transportPostsCount,
        int $locationPostsCount,
        int $visitedLocationsCount,
        int $followersCount,
        int $followingCount,
        int $travelledDistance,
        int $travelledDuration
    ): void {
        $user = User::find($userId);
        if ($user === null) {
            return;
        }

        $user->statistics()->update([
            'posts_count' => $postsCount,
            'transport_posts_count' => $transportPostsCount,
            'location_posts_count' => $locationPostsCount,
            'visited_locations_count' => $visitedLocationsCount,
            'followers_count' => $followersCount,
            'following_count' => $followingCount,
            'travelled_distance' => $travelledDistance,
            'travelled_duration' => $travelledDuration,

            // todo: implement these statistics later
            // 'visited_countries_count' => $user->trips()->distinct('country')->count('country'),
        ]);
    }
}
