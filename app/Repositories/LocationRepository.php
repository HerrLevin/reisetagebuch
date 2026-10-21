<?php

namespace App\Repositories;

use App\Dto\AirportDto;
use App\Dto\OverpassLocation;
use App\Models\Country;
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
use Clickbar\Magellan\Facades\WKBGenerator;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

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

    /**
     * Batched equivalent of calling updateOrCreateOsmLocation() once per item in $locations:
     * one lookup query plus one upsert per table (locations, location_identifiers,
     * location_tags) for the whole chunk, instead of several round-trips per location.
     * Country-code resolution happens once per location, synchronously, exactly as in
     * updateOrCreateOsmLocation().
     *
     * @param  OverpassLocation[]  $locations
     *
     * @throws Throwable
     */
    public function bulkUpsertOsmLocations(array $locations): void
    {
        $byKey = [];
        foreach ($locations as $location) {
            $name = $this->osmNameService->getName($location);
            if ($name === null) {
                continue;
            }

            // A single upsert() call can't affect the same ON CONFLICT row twice, so
            // de-dupe defensively even though readFeatures() shouldn't produce repeats.
            $byKey[$location->osmType.'|'.$location->osmId] = [$location, $name];
        }

        if ($byKey === []) {
            return;
        }

        $existing = $this->lookupExistingOsmLocations(array_column($byKey, 0));

        $now = now();
        $locationRows = [];
        $identifierRows = [];
        $tagRows = [];

        foreach ($byKey as $key => [$location, $name]) {
            $existingRow = $existing->get($key);
            $locationId = $existingRow->location_id ?? (string) Str::uuid7();
            $point = Point::makeGeodetic($location->latitude, $location->longitude);

            $locationRows[] = [
                'id' => $locationId,
                'name' => $name,
                'location' => WKBGenerator::generate($point),
                'country_code' => $this->resolveCountryCode($point),
                'created_at' => $now,
                'updated_at' => $now,
            ];

            $identifierRows[] = [
                'id' => (string) Str::uuid7(),
                'location_id' => $locationId,
                'type' => $location->osmType,
                'identifier' => $location->osmId,
                'origin' => 'osm',
                'name' => $name,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            foreach ($location->tags as $tagKey => $tagValue) {
                $tagRows[] = [
                    'id' => (string) Str::uuid7(),
                    'location_id' => $locationId,
                    'key' => $tagKey,
                    'value' => $tagValue,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        DB::transaction(function () use ($locationRows, $identifierRows, $tagRows) {
            DB::table('locations')->upsert(
                $locationRows,
                ['id'],
                [
                    'name' => DB::raw('excluded.name'),
                    'location' => DB::raw('excluded.location'),
                    'country_code' => DB::raw('excluded.country_code'),
                    'updated_at' => DB::raw('excluded.updated_at'),
                ]
            );

            $this->upsertOsmLocationIdentifiers($identifierRows);

            if ($tagRows !== []) {
                DB::table('location_tags')->upsert(
                    $tagRows,
                    ['location_id', 'key'],
                    [
                        'value' => DB::raw('excluded.value'),
                        'updated_at' => DB::raw('excluded.updated_at'),
                    ]
                );
            }
        });
    }

    /**
     * @param  OverpassLocation[]  $locations
     * @return SupportCollection<string, object{type: string, identifier: string, location_id: string}>
     */
    private function lookupExistingOsmLocations(array $locations): SupportCollection
    {
        $tuples = [];
        $bindings = [];
        foreach ($locations as $location) {
            $tuples[] = '(?, ?, ?)';
            $bindings[] = $location->osmType;
            $bindings[] = 'osm';
            $bindings[] = $location->osmId;
        }

        return DB::table('location_identifiers')
            ->join('locations', 'locations.id', '=', 'location_identifiers.location_id')
            ->whereRaw(
                '(location_identifiers.type, location_identifiers.origin, location_identifiers.identifier) in ('.implode(',', $tuples).')',
                $bindings
            )
            ->select('location_identifiers.type', 'location_identifiers.identifier', 'location_identifiers.location_id')
            ->get()
            ->keyBy(fn ($row) => $row->type.'|'.$row->identifier);
    }

    /**
     * location_identifiers has no table-wide unique constraint on (type, origin,
     * identifier) — only a partial one scoped to origin = 'osm' (other origins,
     * e.g. Träwelling cross-posting, legitimately key by (origin, type,
     * location_id) instead and can share an identifier across locations).
     * Builder::upsert() can't express a WHERE-qualified ON CONFLICT target, so
     * this writes the insert/on-conflict SQL directly, matching that partial index.
     *
     * @param  array<int, array{id: string, location_id: string, type: string, identifier: string, origin: string, name: string, created_at: \Illuminate\Support\Carbon, updated_at: \Illuminate\Support\Carbon}>  $rows
     */
    private function upsertOsmLocationIdentifiers(array $rows): void
    {
        if ($rows === []) {
            return;
        }

        $columns = ['id', 'location_id', 'type', 'identifier', 'origin', 'name', 'created_at', 'updated_at'];
        $placeholders = [];
        $bindings = [];
        foreach ($rows as $row) {
            $placeholders[] = '('.implode(',', array_fill(0, count($columns), '?')).')';
            foreach ($columns as $column) {
                $bindings[] = $row[$column] instanceof DateTimeInterface ? $row[$column]->toDateTimeString() : $row[$column];
            }
        }

        DB::statement(
            'insert into location_identifiers ('.implode(',', $columns).') values '.implode(',', $placeholders).
            " on conflict (type, identifier) where origin = 'osm' do update set
                location_id = excluded.location_id,
                name = excluded.name,
                updated_at = excluded.updated_at",
            $bindings
        );
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

    private function recentRequestLocationQuery(Point $position, int $radius): Builder
    {
        return RequestLocation::select()
            ->addSelect(ST::distanceSphere($position, 'location')->as('distance'))
            ->where(ST::distanceSphere($position, 'location'), '<=', $radius)
            ->where(
                'last_requested_at',
                '>=',
                now()->subMinutes(config('app.overpass.timeout'))
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

    /**
     * Whether this area has ever been queried via Overpass before, regardless
     * of how long ago (unlike recentNearbyRequests(), which only looks within
     * the debounce window). Used to tell "never fetched, but already covered
     * by a Geofabrik import" apart from "fetched before, due for a refresh".
     */
    public function hasEverRequestedNearby(Point $position, int $radius): bool
    {
        return RequestLocation::where(ST::distanceSphere($position, 'location'), '<=', $radius)->exists();
    }

    /**
     * Whether Locations already exist within radius (e.g. from a Geofabrik
     * import), making a live Overpass fetch for that area unnecessary.
     */
    public function hasNearbyLocations(Point $position, int $radius): bool
    {
        return Location::where(ST::distanceSphere($position, 'location'), '<=', $radius)
            ->where('name', '!=', '')
            ->exists();
    }

    public function deleteOldNearbyRequests(): void
    {
        RequestLocation::where(
            'last_requested_at',
            '<=',
            now()->subMinutes(config('app.overpass.timeout'))
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
        $location->country_code = $this->resolveCountryCode($location->location);
        $location->save();

        $location->identifiers()->updateOrCreate(
            ['identifier' => $identifier, 'type' => $identifierType, 'origin' => $origin],
            ['name' => $name]
        );

        $location->save();
    }

    /**
     * Simplified (1:50m) country polygons can miss narrow coastal/urban points by a
     * couple of kilometers, so fall back to the nearest country within this tolerance
     * rather than only exact point-in-polygon containment.
     */
    private const COUNTRY_LOOKUP_TOLERANCE_METERS = 5000;

    public function resolveCountryCode(Point $point): ?string
    {
        return Country::query()
            ->select('iso_a2')
            ->addSelect(ST::distanceSphere('geometry', $point)->as('distance'))
            ->where(ST::dWithinGeography('geometry', $point, self::COUNTRY_LOOKUP_TOLERANCE_METERS), '=', true)
            ->orderBy('distance')
            ->value('iso_a2');
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

    public function getByOsmId(string $nodeId): Collection
    {
        return Location::with(['tags', 'identifiers'])
            ->select('locations.*')
            ->join('location_identifiers', 'location_identifiers.location_id', '=', 'locations.id')
            ->whereIn('location_identifiers.type', ['node', 'way', 'relation'])
            ->where('location_identifiers.origin', 'osm')
            ->where('location_identifiers.identifier', $nodeId)
            ->get();
    }
}
