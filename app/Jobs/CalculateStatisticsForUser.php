<?php

namespace App\Jobs;

use App\Models\User;
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
        $user = User::whereId($this->userId)->firstOrFail();

        $posts = $user->posts()->count();

        $transportPosts = $user->posts()->whereHas('transportPost')->count();
        $locationPosts = $user->posts()->whereHas('locationPost')->count();

        // get unique visited locations count
        $visitedLocations = $user->posts()
            ->whereHas('locationPost')
            ->with('locationPost')
            ->get()
            ->pluck('locationPost.location_id')
            ->unique()
            ->count();

        $user->statistics()->update([
            'posts_count' => $posts,
            'transport_posts_count' => $transportPosts,
            'location_posts_count' => $locationPosts,
            'visited_locations_count' => $visitedLocations,

            // todo: implement these statistics later
            // 'followers_count' => $user->followers()->count(),
            // 'following_count' => $user->following()->count(),
            // 'travelled_distance' => $user->trips()->sum('distance'),
            // 'travelled_duration' => $user->trips()->sum('duration'),
            // 'visited_countries_count' => $user->trips()->distinct('country')->count('country'),
        ]);
    }
}
