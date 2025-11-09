<?php

namespace App\Http\Controllers\Backend;

use App\Dto\DeparturesDto;
use App\Dto\LocationHistoryDto;
use App\Dto\MotisApi\GeocodeResponseEntry;
use App\Dto\MotisApi\LocationType;
use App\Dto\MotisApi\StopDto;
use App\Dto\MotisApi\StopPlaceDto;
use App\Dto\MotisApi\TripDto;
use App\Dto\RequestLocationDto;
use App\Http\Controllers\Controller;
use App\Http\Resources\LocationDto;
use App\Http\Resources\LocationHistoryEntryDto;
use App\Http\Resources\TripHistoryEntryDto;
use App\Hydrators\DbTripHydrator;
use App\Hydrators\TripDtoHydrator;
use App\Jobs\PrefetchJob;
use App\Jobs\RerouteStops;
use App\Models\RequestLocation;
use App\Models\TimestampedUserWaypoint;
use App\Models\TransportTripStop;
use App\Repositories\LocationRepository;
use App\Repositories\TransportTripRepository;
use App\Services\OverpassRequestService;
use App\Services\TransitousRequestService;
use Carbon\Carbon;
use Clickbar\Magellan\Data\Geometries\Point;
use Exception;
use Illuminate\Database\Eloquent\Collection as DbCollection;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Collection;
use Log;
use Throwable;

class LocationController extends Controller
{
    private LocationRepository $locationRepository;

    private TransitousRequestService $transitousRequestService;

    private TransportTripRepository $transportTripRepository;

    private TripDtoHydrator $tripDtoHydrator;

    private OverpassRequestService $overpassRequestService;

    private MapController $mapController;

    public function __construct(
        LocationRepository $locationRepository,
        TransitousRequestService $transitousRequestService,
        TransportTripRepository $transportTripRepository,
        TripDtoHydrator $tripDtoHydrator,
        OverpassRequestService $overpassRequestService,
        MapController $mapController
    ) {
        $this->locationRepository = $locationRepository;
        $this->transitousRequestService = $transitousRequestService;
        $this->transportTripRepository = $transportTripRepository;
        $this->tripDtoHydrator = $tripDtoHydrator;
        $this->overpassRequestService = $overpassRequestService;
        $this->mapController = $mapController;
    }

    /**
     * @return LocationDto[]|Collection
     */
    public function index(string $userId, Carbon $fromDate, Carbon $untilDate): LocationHistoryDto
    {
        $locations = $this->locationRepository->getLocationsForUser($userId, $fromDate, $untilDate);
        $timestampedLocations = $this->locationRepository->getTimestampedUserWaypoints($userId, $fromDate, $untilDate);
        $transportPosts = $this->locationRepository->getTransportPostLocationsForUser($userId, $fromDate, $untilDate);

        $locations = $locations->merge($timestampedLocations);
        $routes = collect();

        // Sort locations by created_at in descending order
        $locations = $locations->sortByDesc('created_at')->map(function ($location) {
            if ($location instanceof TimestampedUserWaypoint) {
                return LocationHistoryEntryDto::fromWaypoint($location);
            }

            return LocationHistoryEntryDto::fromLocationPost($location);
        })->values();

        // Add transport post locations
        foreach ($transportPosts as $post) {
            try {
                $postLocations = LocationHistoryEntryDto::fromTransportPost($post);
            } catch (Throwable $e) {
                Log::error('Error processing transport post locations', [$post]);
                report($e);

                continue;
            }
            $route = $this->mapController->fromTo($post->origin_stop_id, $post->destination_stop_id);
            $routes->push(new TripHistoryEntryDto($post, $route));
            foreach ($postLocations as $loc) {
                $locations->push($loc);
            }
        }

        return new LocationHistoryDto($locations, $routes);
    }

    public function getRecentRequestLocation(Point $point): ?RequestLocationDto
    {
        $radius = config('app.recent_location.radius');
        $recent = $this->locationRepository->getRecentRequestLocation($point, $radius);

        return $recent ? RequestLocationDto::fromModel($recent) : null;
    }

    public function prefetch(Point $point, int $radius): void
    {
        if (! $this->locationRepository->recentNearbyRequests($point, $radius)) {
            $requestLocation = $this->locationRepository->createRequestLocation($point, $radius);
            $this->fetchNearbyLocations($point, $requestLocation, $radius);
        }
    }

    public function createTimestampedUserWaypoint(string $userId, Point $point): void
    {
        if ($this->locationRepository->canTimestampedUserWaypointBeCreated($userId, $point)) {
            $this->locationRepository->createTimestampedUserWaypoint($userId, $point);
        }
    }

    public function nearby(Point $point): DbCollection|Collection
    {
        // Prefetch nearby locations if not already done by job
        PrefetchJob::dispatch($point);
        $radius = config('app.recent_location.radius');
        $this->prefetch($point, $radius);

        $locations = $this->locationRepository->getNearbyLocations($point);

        // remove locations with motis identifier
        return $locations->filter(function ($location) {
            return ! $location->identifiers->contains(function ($identifier) {
                return $identifier->origin === 'motis';
            });
        });
    }

    public function fetchNearbyLocations(Point $point, ?RequestLocation $requestLocation, int $radius): void
    {
        $this->overpassRequestService->setRadius($radius);
        $this->overpassRequestService->setCoordinates($point);

        $response = $this->overpassRequestService->getElements();

        $requestLocation?->update([
            'to_fetch' => count($response['elements'] ?? []),
            'fetched' => 0,
        ]);

        foreach ($this->overpassRequestService->parseLocations($response) as $location) {
            try {
                $this->locationRepository->updateOrCreateOsmLocation($location);
            } catch (Exception $e) {
                Log::error('Error processing location', [$location]);
                report($e);
            }

            $requestLocation?->increment('fetched');
        }

        $requestLocation->update([
            'fetched' => $requestLocation->to_fetch,
        ]);
    }

    /**
     * @throws ConnectionException
     */
    public function departuresNearby(Point $point, Carbon $time, array $filter = [], ?int $radius = null): ?DeparturesDto
    {
        $stops = $this->transitousRequestService->getNearby($point);
        if ($stops->isEmpty()) {
            return null;
        }

        /**
         * @var StopDto $firstStop
         */
        $firstStop = $stops->first();

        return new DeparturesDto(
            stop: $firstStop,
            departures: $this->transitousRequestService->getDepartures($firstStop->stopId, $time, $filter, $radius)
        );
    }

    public function departuresByIdentifier(string $identifier, Carbon $when, array $filter = [], ?int $radius = null): ?DeparturesDto
    {
        $radius = $radius ?? config('app.motis.single_location_radius', 100);
        $departures = $this->transitousRequestService->getDepartures($identifier, $when, $filter, $radius);
        if ($departures->isEmpty()) {
            return null;
        }

        /** @var StopPlaceDto $firstStop */
        $firstStop = $departures->first()?->place;

        return new DeparturesDto(
            stop: $firstStop,
            departures: $departures
        );
    }

    /**
     * @return GeocodeResponseEntry[]
     *
     * @throws ConnectionException
     */
    public function geocode(string $query, ?Point $point): array
    {
        return $this->transitousRequestService->geocode($query, null, LocationType::STOP, $point);
    }

    public function geocodeAirport(string $query, ?Point $point): array
    {
        $airports = [];
        // if only uppercase letters, search for identifier
        if (preg_match('/^[A-Z]{3,4}$/', $query)) {
            foreach ($this->locationRepository->findAirportsByIdentifier($query) as $airport) {
                $airports[] = GeocodeResponseEntry::fromLocation($airport);
            }

            return $airports;
        }

        foreach ($this->locationRepository->findAirportsByName($query, $point) as $airport) {
            $airports[] = GeocodeResponseEntry::fromLocation($airport);
        }

        return $airports;
    }

    public function updateStopoverTimes(string $foreignTripId): void
    {
        Log::debug('Update stopover times for trip '.$foreignTripId);
        $trip = $this->transportTripRepository->getTripByIdentifier(
            foreignId: $foreignTripId,
            with: ['stops', 'stops.location.identifiers']
        );

        if ($trip === null) {
            return;
        }
        $trip->last_refreshed_at = Carbon::now();
        $trip->save();

        $dto = $this->transitousRequestService->getStopTimes($foreignTripId);

        if (! $dto->legs[0]->realTime) {
            Log::debug('No real-time data for trip '.$foreignTripId);

            return;
        }

        $stopovers = [$dto->legs[0]->from, ...$dto->legs[0]->intermediateStops, $dto->legs[0]->to];
        /** @var TransportTripStop[] $stopModels */
        $order = 0;
        /** @var StopPlaceDto $stopover */
        foreach ($stopovers as $stopover) {
            $stop = $trip->stops->firstWhere('stop_sequence', $order);
            if ($stop) {
                Log::debug('Updating stopover '.$stopover->name.' for trip '.$foreignTripId);
                $this->transportTripRepository->updateStopTimes(
                    $stop,
                    $stopover->scheduledArrival,
                    $stopover->scheduledDeparture,
                    $stopover->scheduledArrival?->diffInSeconds($stopover->arrival) ?? $stop->arrival_delay,
                    $stopover->scheduledDeparture?->diffInSeconds($stopover->departure) ?? $stop->departure_delay,
                    $stopover->cancelled ?? false
                );
            }
            $order++;
        }
    }

    public function stopovers(string $tripId, string $startId, string $startTime): ?TripDto
    {
        $trip = $this->transportTripRepository->getTripByIdentifier(
            $tripId,
            null,
            ['stops', 'stops.location.identifiers']
        );

        $hydrator = new DbTripHydrator;
        if ($trip !== null) {
            return $hydrator->hydrateTrip($trip);
        }

        $dto = $this->transitousRequestService->getStopTimes($tripId);

        // create a database trip
        $trip = $this->transportTripRepository->getOrCreateTrip(
            $dto->legs[0]->mode,
            $tripId,
            'transitous',
            $dto->legs[0]->routeShortName,
            $dto->legs[0]->routeLongName,
            $dto->legs[0]->tripShortName,
            $dto->legs[0]->displayName,
            $dto->legs[0]->routeColor,
            $dto->legs[0]->routeTextColor
        );

        $realtime = $dto->legs[0]->realTime;

        // create stopovers
        $stopovers = [$dto->legs[0]->from, ...$dto->legs[0]->intermediateStops, $dto->legs[0]->to];
        /** @var StopPlaceDto[] $stops */
        $stops = [];
        /** @var TransportTripStop[] $stopModels */
        $stopModels = [];
        $order = 0;
        /** @var StopPlaceDto $stopover */
        foreach ($stopovers as $stopover) {
            $location = $this->locationRepository->getOrCreateLocationByIdentifier(
                $stopover->name,
                $stopover->latitude,
                $stopover->longitude,
                $stopover->stopId,
                'stop',
                'motis'
            );

            $stop = $this->transportTripRepository->addStopToTrip(
                $trip,
                $location,
                $order,
                $stopover->scheduledArrival,
                $stopover->scheduledDeparture,
                $realtime ? $stopover->scheduledArrival?->diffInSeconds($stopover->arrival) : null,
                $realtime ? $stopover->scheduledDeparture?->diffInSeconds($stopover->departure) : null,
                $stopover->cancelled ?? false,
                null // todo: get route segment between stops
            );

            $stopModels[] = $stop;
            $stops[] = $this->tripDtoHydrator->hydrateStopPlace($stop, $location);
            $order++;
        }

        $dto->legs[0]->setFrom($stops[0]);
        $dto->legs[0]->setTo($stops[count($stops) - 1]);
        $dto->legs[0]->setIntermediateStops(
            array_slice($stops, 1, count($stops) - 2)
        );

        RerouteStops::dispatch($dto, $stopModels);

        // return a trip dto with the stopovers
        return $dto;
    }
}
