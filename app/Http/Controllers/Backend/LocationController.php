<?php

namespace App\Http\Controllers\Backend;

use App\Dto\DeparturesDto;
use App\Dto\MotisApi\GeocodeResponseEntry;
use App\Dto\MotisApi\LocationType;
use App\Dto\MotisApi\StopDto;
use App\Dto\MotisApi\StopPlaceDto;
use App\Dto\MotisApi\TripDto;
use App\Http\Controllers\Controller;
use App\Http\Resources\LocationDto;
use App\Http\Resources\LocationHistoryDto;
use App\Hydrators\TripDtoHydrator;
use App\Jobs\RerouteStops;
use App\Models\TimestampedUserWaypoint;
use App\Models\TransportTripStop;
use App\Repositories\LocationRepository;
use App\Repositories\TransportTripRepository;
use App\Services\TransitousRequestService;
use Carbon\Carbon;
use Clickbar\Magellan\Data\Geometries\Point;
use Illuminate\Database\Eloquent\Collection as DbCollection;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Collection;

class LocationController extends Controller
{
    private LocationRepository $locationRepository;

    private TransitousRequestService $transitousRequestService;

    private TransportTripRepository $transportTripRepository;

    private TripDtoHydrator $tripDtoHydrator;

    public function __construct(
        LocationRepository $locationRepository,
        TransitousRequestService $transitousRequestService,
        TransportTripRepository $transportTripRepository,
        TripDtoHydrator $tripDtoHydrator
    ) {
        $this->locationRepository = $locationRepository;
        $this->transitousRequestService = $transitousRequestService;
        $this->transportTripRepository = $transportTripRepository;
        $this->tripDtoHydrator = $tripDtoHydrator;
    }

    /**
     * @return LocationDto[]|Collection
     */
    public function index(string $userId, Carbon $fromDate, Carbon $untilDate): Collection
    {
        $locations = $this->locationRepository->getLocationsForUser($userId, $fromDate, $untilDate);
        $timestampedLocations = $this->locationRepository->getTimestampedUserWaypoints($userId, $fromDate, $untilDate);

        $locations = $locations->merge($timestampedLocations);

        // Sort locations by created_at in descending order
        return $locations->sortByDesc('created_at')->map(function ($location) {
            if ($location instanceof TimestampedUserWaypoint) {
                return LocationHistoryDto::fromWaypoint($location);
            }

            return LocationHistoryDto::fromLocationPost($location);
        })->values();
    }

    public function prefetch(Point $point): void
    {
        if (! $this->locationRepository->recentNearbyRequests($point)) {
            $this->locationRepository->deleteOldNearbyRequests();
            $this->locationRepository->createRequestLocation($point);
            $this->locationRepository->fetchNearbyLocations($point);
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
        $this->prefetch($point);

        return $this->locationRepository->getNearbyLocations($point);
    }

    /**
     * @throws ConnectionException
     */
    public function departuresNearby(Point $point, Carbon $time, array $filter = []): ?DeparturesDto
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
            departures: $this->transitousRequestService->getDepartures($firstStop->stopId, $time, $filter)
        );
    }

    public function departuresByIdentifier(string $identifier, Carbon $when, array $filter = []): ?DeparturesDto
    {
        $departures = $this->transitousRequestService->getDepartures($identifier, $when, $filter);
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

    public function stopovers(string $tripId, string $startId, string $startTime): ?TripDto
    {
        $dto = $this->transitousRequestService->getStopTimes($tripId);

        // create a database trip
        $trip = $this->transportTripRepository->getOrCreateTrip(
            $dto->legs[0]->mode,
            $tripId,
            'transitous',
            $dto->legs[0]->routeShortName
        );

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
                $stopover->arrival,
                $stopover->departure,
                $stopover->scheduledArrival?->diffInSeconds($stopover->arrival),
                $stopover->scheduledDeparture?->diffInSeconds($stopover->departure),
                false,
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
