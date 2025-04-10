<?php

namespace App\Repositories;

use App\Helpers\LocationHelper;
use App\Models\Location;
use App\Models\LocationIdentifier;
use App\Models\RequestLocation;
use App\Services\OverpassRequestService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;

class LocationRepository
{
    public function fetchNearbyLocations(float $latitude, float $longitude): SupportCollection
    {
        $service = new OverpassRequestService($latitude, $longitude);

        $data = collect();
        foreach ($service->getLocations() as $location) {
            $identifier = LocationIdentifier::where([
                ['type', '=', $location->osmType],
                ['identifier', '=', $location->osmId],
                ['origin', '=', 'osm'],
            ])->with('location')->first();
            $dbLocation = $identifier->location ?? null;

            if ($dbLocation === null) {
                $dbLocation = Location::create([
                    'name' => $location->name,
                    'latitude' => $location->latitude,
                    'longitude' => $location->longitude,
                ]);
                $dbLocation->identifiers()->create([
                    'identifier' => $location->osmId,
                    'type' => $location->osmType,
                    'origin' => 'osm',
                    'name' => $location->name,
                ]);
                $dbLocation->save();
            }

            foreach ($location->tags as $key => $value) {
                $dbLocation->tags()->updateOrCreate([
                    'key' => $key,
                    'value' => $value,
                ]);
            }

            $data->push($dbLocation);
        }

        return $data;
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

    public function getNearbyLocations(float $latitude, float $longitude): Collection|SupportCollection
    {
        return LocationHelper::nearbyQueryFilter(
            Location::class,
            $latitude,
            $longitude,
            100,
        )
            ->where([['name', '!=', '']])
            ->with('tags')
            ->orderByDesc('distance')
            ->get();
    }

    public function getLocationById(string $id, bool $withRelations = true): ?Location
    {
        if ($withRelations) {
            return Location::with(['tags', 'identifiers'])->find($id);
        }

        return Location::find($id);
    }
}
