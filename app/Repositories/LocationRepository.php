<?php

namespace App\Repositories;

use App\Models\Location;
use App\Models\LocationIdentifier;
use App\Models\RequestLocation;
use App\Services\OverpassRequestService;
use Clickbar\Magellan\Data\Geometries\Point;
use Clickbar\Magellan\Database\PostgisFunctions\ST;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;

class LocationRepository
{
    public function fetchNearbyLocations(Point $point): SupportCollection
    {
        $service = new OverpassRequestService($point->getLatitude(), $point->getLongitude());

        $data = collect();
        foreach ($service->getLocations() as $location) {
            $identifier = LocationIdentifier::where([
                ['type', '=', $location->osmType],
                ['identifier', '=', $location->osmId],
                ['origin', '=', 'osm'],
            ])->with('location')->first();
            $dbLocation = $identifier->location ?? null;

            if ($dbLocation === null) {
                $dbLocation = $this->createLocation(
                    $location->name,
                    $location->latitude,
                    $location->longitude,
                    $location->osmId,
                    $location->osmType,
                    'osm'
                );
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

    public function createRequestLocation(Point $point): void
    {
        $requestLocation = new RequestLocation();
        $requestLocation->location = $point;
        $requestLocation->last_requested_at = now();

        $requestLocation->save();
    }

    public function recentNearbyRequests(Point $position): bool
    {
        $locations = RequestLocation::select()
            ->addSelect(ST::distanceSphere($position, 'location')->as('distance'))
            ->where(ST::distanceSphere($position, 'location'), '<=', 50);

        $locations->where([['last_requested_at', '>=', now()->subMinutes(30)]]);
        $locations = $locations->get();

        return $locations->where('distance', '<=', 50)->isNotEmpty();
    }

    public function getNearbyLocations(Point $position): Collection|SupportCollection
    {
        return Location::select()
            ->addSelect(ST::distanceSphere($position, 'location')->as('distance'))
            ->where(ST::distanceSphere($position, 'location'), '<=', 100)
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

    public function getLocationByIdentifier(string $identifier, string $type, ?string $origin = null): ?Location
    {
        return Location::whereHas('identifiers', function ($query) use ($identifier, $type, $origin) {
            $query->where('identifier', $identifier)
                ->where('type', $type);
            if ($origin) {
                $query->where('origin', $origin);
            }
        })->first();
    }

    public function getOrCreateLocationByIdentifier(
        string $name,
        float  $latitude,
        float  $longitude,
        string $identifier,
        string $type,
        string $origin,
    ): Location
    {
        $location = $this->getLocationByIdentifier($identifier, $type, $origin);

        if ($location === null) {
            $location = $this->createLocation($name, $latitude, $longitude, $identifier, $type, $origin);
        }

        return $location;
    }

    public function createLocation(
        string $name,
        float  $latitude,
        float  $longitude,
        string $identifier,
        string $identifierType,
        string $origin
    ): Location
    {
        $location = new Location();
        $location->name = $name;
        $location->location = Point::makeGeodetic($latitude, $longitude);
        $location->save();

        $location->identifiers()->create([
            'identifier' => $identifier,
            'type' => $identifierType,
            'origin' => $origin,
            'name' => $name,
        ]);
        $location->save();

        return $location;
    }
}
