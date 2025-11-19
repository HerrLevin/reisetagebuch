<?php

namespace App\Repositories;

use App\Dto\AirportDto;
use App\Models\Location;
use App\Models\LocationIdentifier;
use App\Models\LocationPost;
use App\Models\RequestLocation;
use App\Models\TimestampedUserWaypoint;
use App\Models\TransportPost;
use App\Services\OsmNameService;
use Carbon\Carbon;
use Clickbar\Magellan\Data\Geometries\Point;
use Clickbar\Magellan\Database\PostgisFunctions\ST;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;

class LocationRepository
{
    private OsmNameService $osmNameService;

    public function __construct(OsmNameService $osmNameService)
    {
        $this->osmNameService = $osmNameService;
    }

    public function getLocationsForUser(string $userId, Carbon $fromDate, Carbon $untilDate): Collection
    {
        return LocationPost::join('posts', 'posts.id', '=', 'location_posts.post_id')
            ->where('posts.user_id', $userId)
            ->where('posts.created_at', '>=', $fromDate)
            ->where('posts.created_at', '<=', $untilDate)
            ->orderBy('posts.created_at', 'desc')
            ->limit(100_000)
            ->get();
    }

    public function getTimestampedUserWaypoints(string $userId, Carbon $fromDate, Carbon $untilDate): Collection
    {
        $query = TimestampedUserWaypoint::where('user_id', $userId)
            ->where('created_at', '>=', $fromDate)
            ->where('created_at', '<=', $untilDate)
            ->orderBy('created_at', 'desc')
            ->limit(100_000);

        return $query->get();
    }

    /**
     * @return Collection<int, TransportPost>
     */
    public function getTransportPostLocationsForUser(string $userId, Carbon $fromDate, Carbon $untilDate): Collection
    {
        return TransportPost::join('posts', 'posts.id', '=', 'transport_posts.post_id')
            ->join('transport_trip_stops as origin_stops', 'origin_stops.id', '=', 'transport_posts.origin_stop_id')
            ->join('transport_trip_stops as destination_stops', 'destination_stops.id', '=', 'transport_posts.destination_stop_id')
            ->where('posts.user_id', $userId)
            ->where(function ($query) use ($fromDate, $untilDate) {
                $query->where(function ($q) use ($fromDate, $untilDate) {
                    $q->where('origin_stops.departure_time', '>=', $fromDate)
                        ->where('origin_stops.departure_time', '<=', $untilDate);
                })->orWhere(function ($q) use ($fromDate, $untilDate) {
                    $q->where('destination_stops.arrival_time', '>=', $fromDate)
                        ->where('destination_stops.arrival_time', '<=', $untilDate);
                });
            })
            ->orderBy('posts.created_at', 'desc')
            ->limit(100_000)
            ->get();
    }

    public function updateOrCreateOsmLocation($location): void
    {
        $name = $this->osmNameService->getName($location);
        if ($name === null) {
            return;
        }

        $identifier = LocationIdentifier::where([
            ['type', '=', $location->osmType],
            ['identifier', '=', $location->osmId],
            ['origin', '=', 'osm'],
        ])->with('location')->first();
        $dbLocation = $identifier->location ?? null;

        if ($dbLocation === null) {
            $dbLocation = new Location;
        }

        $this->setLocationData(
            $dbLocation,
            $name,
            $location->latitude,
            $location->longitude,
            $location->osmId,
            $location->osmType,
            'osm'
        );

        foreach ($location->tags as $key => $value) {
            $dbLocation->tags()->updateOrCreate([
                'key' => $key,
                'value' => $value,
            ]);
        }
    }

    public function updateOrCreateAirport(AirportDto $airport): void
    {
        $identifier = LocationIdentifier::where([
            ['type', '=', 'airport'],
            ['identifier', '=', $airport->foreignIdentifier],
            ['origin', '=', $airport->provider],
        ])->with('location')->first();
        $dbLocation = $identifier->location ?? null;

        if ($dbLocation === null) {
            $dbLocation = new Location;
        }

        $dbLocation->type = 'airport';

        $this->setLocationData(
            $dbLocation,
            $airport->name,
            $airport->latitude,
            $airport->longitude,
            $airport->foreignIdentifier,
            'airport',
            $airport->provider
        );

        if ($airport->iataCode) {
            $dbLocation->identifiers()->updateOrCreate(
                ['identifier' => $airport->iataCode, 'type' => 'iata', 'origin' => $airport->provider],
                ['name' => $airport->name]
            );
        }
        if ($airport->icaoCode) {
            $dbLocation->identifiers()->updateOrCreate(
                ['identifier' => $airport->icaoCode, 'type' => 'icao', 'origin' => $airport->provider],
                ['name' => $airport->name]
            );
        }
        if ($airport->gpsCode) {
            $dbLocation->identifiers()->updateOrCreate(
                ['identifier' => $airport->gpsCode, 'type' => 'gps', 'origin' => $airport->provider],
                ['name' => $airport->name]
            );
        }
        if (empty($airport->gpsCode) && empty($airport->icaoCode) && empty($airport->iataCode) && $airport->localCode) {
            $dbLocation->identifiers()->updateOrCreate(
                ['identifier' => $airport->localCode, 'type' => 'local', 'origin' => $airport->provider],
                ['name' => $airport->name]
            );
        }

        $dbLocation->save();
    }

    /**
     * @return Collection<int, Location>
     */
    public function findAirportsByIdentifier(string $identifier): Collection
    {
        return Location::whereHas('identifiers', function ($query) use ($identifier) {
            $query->where('identifier', $identifier)
                ->where('origin', 'ourairports')
                ->whereIn('type', ['iata', 'icao', 'gps']);
        })
            ->orderByRaw("array_position(array['iata', 'icao', 'gps'], type)")
            ->limit(100)
            ->get();
    }

    public function findAirportsByName(string $name, ?Point $locationBias = null): Collection
    {
        $locations = Location::where('type', 'airport')
            ->whereLike('name', '%'.$name.'%', false);

        if ($locationBias !== null) {
            $locations->addSelect(ST::distanceSphere($locationBias, 'location')->as('distance'))
                ->orderBy('distance');
        }

        return $locations
            ->orderBy('name')
            ->limit(100)
            ->get();
    }

    public function createRequestLocation(Point $point, int $radius): RequestLocation
    {
        $requestLocation = new RequestLocation;
        $requestLocation->location = $point;
        $requestLocation->radius = $radius;
        $requestLocation->to_fetch = 1000;
        $requestLocation->last_requested_at = now();

        $requestLocation->save();

        return $requestLocation;
    }

    private function recentRequestLocationQuery(Point $position, int $radius): \Illuminate\Database\Eloquent\Builder
    {
        return RequestLocation::select()
            ->addSelect(ST::distanceSphere($position, 'location')->as('distance'))
            ->where(ST::distanceSphere($position, 'location'), '<=', $radius)
            ->where('radius', '=', $radius)
            ->where(
                'last_requested_at',
                '>=',
                now()->subMinutes(config('app.recent_location.timeout'))
            );
    }

    public function getRecentRequestLocation(Point $position, int $radius): ?RequestLocation
    {
        return $this->recentRequestLocationQuery($position, $radius)
            ->addSelect('*')
            ->first();
    }

    public function recentNearbyRequests(Point $position, int $radius): bool
    {
        $locations = $this->recentRequestLocationQuery($position, $radius);

        return $locations->count() > 0;
    }

    public function deleteOldNearbyRequests(): void
    {
        RequestLocation::where(
            'last_requested_at',
            '<=',
            now()->subMinutes(config('app.recent_location.timeout'))
        )
            ->delete();
    }

    public function getNearbyLocations(Point $position): Collection|SupportCollection
    {
        return Location::select()
            ->addSelect(ST::distanceSphere($position, 'location')->as('distance'))
            ->where(ST::distanceSphere($position, 'location'), '<=', config('app.nearby.radius'))
            ->where([['name', '!=', '']])
            ->with(['tags', 'identifiers'])
            ->orderBy('distance')
            ->limit(100)
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
        float $latitude,
        float $longitude,
        string $identifier,
        string $type,
        string $origin,
    ): Location {
        $location = $this->getLocationByIdentifier($identifier, $type, $origin);

        if ($location === null) {
            $location = new Location;
            $this->setLocationData($location, $name, $latitude, $longitude, $identifier, $type, $origin);
        }

        return $location;
    }

    public function setLocationData(
        Location $location,
        string $name,
        float $latitude,
        float $longitude,
        string $identifier,
        string $identifierType,
        string $origin
    ): void {
        $location->name = $name;
        $location->location = Point::makeGeodetic($latitude, $longitude);
        $location->save();

        $location->identifiers()->updateOrCreate(
            ['identifier' => $identifier, 'type' => $identifierType, 'origin' => $origin],
            ['name' => $name]
        );

        $location->save();
    }

    public function canTimestampedUserWaypointBeCreated(
        string $userId,
        Point $point
    ): bool {
        return TimestampedUserWaypoint::where('user_id', $userId)
            ->where(ST::dWithinGeography('location', $point, 50), '=', true) // todo: make radius configurable
            ->where('created_at', '>=', now()->subMinutes(10))
            ->count() === 0;
    }

    public function createTimestampedUserWaypoint(
        string $userId,
        Point $point
    ): void {
        $waypoint = new TimestampedUserWaypoint;
        $waypoint->user_id = $userId;
        $waypoint->location = $point;
        $waypoint->save();
    }

    public function searchNearby(Point $position, ?string $search, int $radius): Collection|SupportCollection
    {
        $query = Location::select()
            ->addSelect(ST::distanceSphere($position, 'location')->as('distance'))
            ->where(ST::distanceSphere($position, 'location'), '<=', $radius);

        if (! empty($search)) {
            $query->whereLike('name', '%'.$search.'%', false);
        }

        return $query->with(['tags', 'identifiers'])
            ->orderBy('distance')
            ->limit(50)
            ->get();
    }
}
