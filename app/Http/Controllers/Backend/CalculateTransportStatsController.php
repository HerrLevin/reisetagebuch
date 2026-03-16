<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Repositories\PostRepository;
use App\Repositories\TransportTripRepository;
use App\Repositories\UserStatisticsRepository;
use Carbon\Carbon;
use Clickbar\Magellan\Data\Geometries\LineString;
use Illuminate\Support\Facades\Log;
use Location\Coordinate;
use Location\Distance\Vincenty;
use Location\Polyline;
use Throwable;

class CalculateTransportStatsController extends Controller
{
    private PostRepository $postRepository;

    private TransportTripRepository $transportTripRepository;

    private UserStatisticsRepository $statisticsRepository;

    public function __construct(
        PostRepository $postRepository,
        TransportTripRepository $transportTripRepository,
        UserStatisticsRepository $statisticsRepository
    ) {
        $this->postRepository = $postRepository;
        $this->transportTripRepository = $transportTripRepository;
        $this->statisticsRepository = $statisticsRepository;
    }

    private function getPost(string $transportPostId): ?Post
    {
        $post = $this->postRepository->internalGetById($transportPostId);
        if ($post->transportPost === null) {
            Log::warning('Transport post not found for distance calculation', ['post_id' => $transportPostId, 'post' => $post]);

            return null;
        }

        return $post;
    }

    public function calculateStatsForPost(string $transportPostId): void
    {
        $post = $this->getPost($transportPostId);
        if ($post === null) {
            return;
        }

        if ($post->transportPost->user_geometry) {
            $distance = $this->calculateDistanceForLine($post->transportPost->user_geometry);
        } else {
            $distance = $this->calculateDistance($post);
        }

        $duration = $this->calculateDuration($post);

        $originalDistance = $post->transportPost->distance;
        $originalDuration = $post->transportPost->duration;

        $this->postRepository->updateStats($post->id, $distance, $duration);
        $this->statisticsRepository->updateTransportPostStats($post->user_id, $distance, $duration, $originalDistance, $originalDuration);
    }

    private function calculateDistance(Post $post): ?int
    {
        try {
            $stops = $this->transportTripRepository->getStopsBetween(
                $post->transportPost->transport_trip_id,
                $post->transportPost->originStop->stop_sequence,
                $post->transportPost->destinationStop->stop_sequence,
            );
            // remove last stop from stops
            $stops->pop();
            $distance = 0;
            foreach ($stops as $stop) {
                $distance += $stop->routeSegment?->distance ?? 0;
            }
            Log::debug('Calculated distance for transport post', ['post_id' => $post->id, 'distance' => $distance]);

            return $distance;
        } catch (Throwable $e) {
            // Log any other errors that occur during distance calculation.
            Log::error('Error calculating distance for transport post', ['error' => $e->getMessage()]);

            return null;
        }
    }

    private function calculateDistanceForLine(LineString $lineString): int
    {
        $track = new Polyline;
        foreach ($lineString->getPoints() as $coordinate) {
            $track->addPoint(new Coordinate($coordinate->getLatitude(), $coordinate->getLongitude()));
        }

        return (int) $track->getLength(new Vincenty);
    }

    private function calculateDuration(Post $post): int
    {
        $arrival = $this->arrival($post);
        $departure = $this->departure($post);

        return (int) abs($arrival->diffInSeconds($departure)) ?? 0;
    }

    private function arrival(Post $post): Carbon
    {
        if ($post->transportPost->manual_arrival) {
            return $post->transportPost->manual_arrival;
        }

        $arrivalTime = $post->transportPost->destinationStop->arrival_time;

        return $arrivalTime->addSeconds($post->transportPost->destinationStop->arrival_delay);
    }

    private function departure(Post $post): Carbon
    {
        if ($post->transportPost->manual_departure) {
            return $post->transportPost->manual_departure;
        }

        $departureTime = $post->transportPost->originStop->departure_time;

        return $departureTime->addSeconds($post->transportPost->originStop->departure_delay);
    }
}
