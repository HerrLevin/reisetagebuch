<?php

namespace App\Repositories;

use App\Helpers\LocationHelper;
use App\Models\RequestLocation;
use App\Models\Venue;
use App\Services\OverpassService;

class VenueRepository
{
    public function fetchNearbyVenues(float $latitude, float $longitude): void
    {
        $service = new OverpassService($latitude, $longitude);

        foreach ($service->getVenues() as $venue) {
            Venue::updateOrCreate(
                [
                    'osm_type' => $venue->osmType,
                    'osm_id' => $venue->osmId,
                ],
                [
                    'name' => $venue->name,
                    'latitude' => $venue->latitude,
                    'longitude' => $venue->longitude,
                ]
            );
        }
    }

    public function createRequestLocation(float $latitude, float $longitude): void
    {
        RequestLocation::create([
            'latitude' => $latitude,
            'longitude' => $longitude,
            'last_requested_at' => now(),
        ]);
    }

    public function recentNearbyRequests(float $latitude, float $longitude): bool
    {
        $locations = LocationHelper::nearbyQueryFilter(
            RequestLocation::class,
            $latitude,
            $longitude,
            50,
        );
        $locations->where([['last_requested_at', '>=', now()->subMinutes(30)]]);
        $locations = $locations->get();

        return $locations->where('distance', '<=', 50)->isNotEmpty();
    }

    public function getNearbyVenues(float $latitude, float $longitude)
    {
        if (!$this->recentNearbyRequests($latitude, $longitude)) {
            $this->fetchNearbyVenues($latitude, $longitude);
            $this->createRequestLocation($latitude, $longitude);
        }

        return LocationHelper::nearbyQueryFilter(
            Venue::class,
            $latitude,
            $longitude,
            200,
        )->where([['name', '!=', '']])->get();
    }
}
