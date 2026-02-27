<?php

namespace App\Http\Controllers\Api;

use App\Dto\DeparturesResponseDto;
use App\Dto\ErrorDto;
use App\Dto\LocationHistoryDto;
use App\Dto\MotisApi\GeocodeResponseEntry;
use App\Dto\RequestLocationDto;
use App\Dto\StopoversResponseDto;
use App\Enums\TransportMode;
use App\Http\Controllers\Backend\LocationController as BackendLocationController;
use App\Http\Requests\DeparturesRequest;
use App\Http\Requests\GeocodeRequest;
use App\Http\Requests\LocationHistoryRequest;
use App\Http\Requests\LocationQueryRequest;
use App\Http\Requests\LocationRequest;
use App\Http\Requests\StopoverRequest;
use App\Http\Resources\LocationDto;
use App\Jobs\PrefetchJob;
use App\Models\User;
use Carbon\Carbon;
use Clickbar\Magellan\Data\Geometries\Point;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class LocationController extends Controller
{
    private BackendLocationController $locationController;

    public function __construct(BackendLocationController $locationController)
    {
        parent::__construct();
        $this->locationController = $locationController;
    }

    private function canStoreHistory(Request $request): bool
    {
        if ($request->hasCookie('rtb_disallow_history') || $request->hasHeader('X-RTB-DISALLOW-HISTORY')) {
            return false;
        }
        if ($request->hasCookie('rtb_allow_history') || $request->hasHeader('X-RTB-ALLOW-HISTORY')) {
            return true;
        }

        return false;
    }

    #[OA\Post(
        path: '/location/prefetch',
        operationId: 'prefetchLocation',
        description: 'Prefetch location data and optionally store user history',
        summary: 'Prefetch location',
        tags: ['Location'],
        parameters: [
            new OA\Parameter(name: 'latitude', in: 'query', required: true, schema: new OA\Schema(type: 'number', format: 'float')),
            new OA\Parameter(name: 'longitude', in: 'query', required: true, schema: new OA\Schema(type: 'number', format: 'float')),
        ],
        responses: [new OA\Response(response: 204, description: Controller::OA_DESC_NO_CONTENT)]
    )]
    public function prefetch(LocationRequest $request): void
    {
        $point = Point::makeGeodetic($request->latitude, $request->longitude);
        if ($this->canStoreHistory($request)) {
            $this->locationController->createTimestampedUserWaypoint($request->user()->id, $point);
        }
        PrefetchJob::dispatch($point);

        abort('204');
    }

    #[OA\Get(
        path: '/location/request-location',
        operationId: 'getRecentRequestLocation',
        description: 'Return a recent location matching the requested coordinates',
        summary: 'Get recent request location',
        tags: ['Location'],
        parameters: [
            new OA\Parameter(name: 'latitude', in: 'query', required: true, schema: new OA\Schema(type: 'number', format: 'float')),
            new OA\Parameter(name: 'longitude', in: 'query', required: true, schema: new OA\Schema(type: 'number', format: 'float')),
        ],
        responses: [
            new OA\Response(response: 200, description: Controller::OA_DESC_SUCCESS, content: new OA\JsonContent(ref: RequestLocationDto::class)),
            new OA\Response(response: 404, description: 'Location not found', content: new OA\JsonContent(ref: ErrorDto::class)),
        ]
    )]
    public function getRecentRequestLocation(LocationRequest $request): ?RequestLocationDto
    {
        $point = Point::makeGeodetic($request->latitude, $request->longitude);
        $location = $this->locationController->getRecentRequestLocation($point);

        if ($location === null) {
            abort(404, new ErrorDto('Location not found'));
        }

        return $location;
    }

    #[OA\Get(
        path: '/locations/nearby',
        operationId: 'searchLocations',
        description: 'Search for locations near a point or by query',
        summary: 'Search locations',
        tags: ['Location'],
        parameters: [
            new OA\Parameter(name: 'latitude', in: 'query', required: true, schema: new OA\Schema(type: 'number', format: 'float')),
            new OA\Parameter(name: 'longitude', in: 'query', required: true, schema: new OA\Schema(type: 'number', format: 'float')),
            new OA\Parameter(name: 'query', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
        ],
        responses: [new OA\Response(response: 200, description: Controller::OA_DESC_SUCCESS, content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: LocationDto::class)))]
    )]
    public function search(LocationQueryRequest $request)
    {
        $point = Point::makeGeodetic($request->latitude, $request->longitude);

        $radius = 50_000; // 50km radius
        if (empty($request->get('query'))) {
            $radius = config('app.nearby.radius');
        }

        $locations = $this->locationController->searchNearby($point, $request->input('query'), $radius);

        return array_values($locations->map(fn ($location) => new LocationDto($location))->toArray());
    }

    #[OA\Get(
        path: '/geocode',
        operationId: 'geocode',
        description: 'Geocode a query using configured providers',
        summary: 'Geocode',
        tags: ['Location'],
        parameters: [
            new OA\Parameter(name: 'query', in: 'query', required: true, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'provider', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'latitude', in: 'query', required: false, schema: new OA\Schema(type: 'number', format: 'float')),
            new OA\Parameter(name: 'longitude', in: 'query', required: false, schema: new OA\Schema(type: 'number', format: 'float')),
        ],
        responses: [new OA\Response(response: 200, description: Controller::OA_DESC_SUCCESS, content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: GeocodeResponseEntry::class)))]
    )]
    public function geocode(GeocodeRequest $request): array
    {
        $point = null;
        if ($request->latitude && $request->longitude) {
            $point = Point::makeGeodetic($request->latitude, $request->longitude);
            if ($this->canStoreHistory($request)) {
                $this->locationController->createTimestampedUserWaypoint($request->user()->id, $point);
            }
        }

        if ($request->provider === 'airport') {
            return $this->locationController->geocodeAirport($request->input('query'), $point);
        }

        return $this->geocodeMotis($request->input('query'), $point);
    }

    /**
     * @return GeocodeResponseEntry[]
     */
    private function geocodeMotis(string $query, ?Point $point)
    {
        try {
            $locations = $this->locationController->geocode($query, $point);
        } catch (ConnectionException $e) {
            abort(503, new ErrorDto('Connection error to geocoding service'));
        }

        return $locations;
    }

    #[OA\Get(
        path: '/locations/history',
        operationId: 'locationHistory',
        description: 'Return location history for the authenticated user for a given day',
        summary: 'Location history',
        tags: ['Location'],
        parameters: [
            new OA\Parameter(name: 'when', in: 'query', required: false, schema: new OA\Schema(type: 'string', format: 'date')),
        ],
        responses: [new OA\Response(response: 200, description: Controller::OA_DESC_SUCCESS, content: new OA\JsonContent(ref: LocationHistoryDto::class))]
    )]
    public function index(LocationHistoryRequest $request): LocationHistoryDto
    {
        $when = $request->when ? Carbon::parse($request->when) : Carbon::now();

        return $this->locationController->index(
            $request->user()->id,
            $when->startOfDay(),
            $when->clone()->endOfDay()
        );
    }

    #[OA\Get(
        path: '/locations/departures',
        operationId: 'departures',
        description: 'Return departures for a given point or station identifier',
        summary: 'Departures',
        tags: ['Location'],
        parameters: [
            new OA\Parameter(name: 'latitude', in: 'query', required: false, schema: new OA\Schema(type: 'number', format: 'float')),
            new OA\Parameter(name: 'longitude', in: 'query', required: false, schema: new OA\Schema(type: 'number', format: 'float')),
            new OA\Parameter(name: 'identifier', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'when', in: 'query', required: false, schema: new OA\Schema(type: 'string', format: 'date-time')),
            new OA\Parameter(name: 'modes', in: 'query', required: false, schema: new OA\Schema(type: 'array', items: new OA\Items(ref: TransportMode::class))),
        ],
        responses: [new OA\Response(response: 200, description: Controller::OA_DESC_SUCCESS, content: new OA\JsonContent(ref: DeparturesResponseDto::class))]
    )]
    public function departures(DeparturesRequest $request): DeparturesResponseDto
    {
        $filter = $request->modes ?? [];
        $time = $request->when ? Carbon::parse($request->when) : now()->subMinutes(2);
        $request->user()->load('settings');
        /** @var User $user */
        $user = $request->user();
        $radius = $user->settings->motis_radius;

        if (! empty($request->identifier)) {
            $departures = $this->locationController->departuresByIdentifier($request->identifier, $time, $filter);
        } else {
            $point = Point::makeGeodetic($request->latitude, $request->longitude);
            $departures = $this->locationController->departuresNearby($point, $time, $filter, $radius);
        }

        return new DeparturesResponseDto(
            departures: $departures,
            modes: $filter,
            requestTime: $time->toIso8601String(),
            requestIdentifier: $request->identifier,
            requestLatitude: (float) $request->latitude,
            requestLongitude: (float) $request->longitude,
        );
    }

    #[OA\Get(
        path: '/locations/stopovers',
        operationId: 'stopovers',
        description: 'Return stopovers for a given trip start',
        summary: 'Stopovers',
        tags: ['Location'],
        parameters: [
            new OA\Parameter(name: 'tripId', in: 'query', required: true, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'startId', in: 'query', required: true, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'startTime', in: 'query', required: true, schema: new OA\Schema(type: 'string')),
        ],
        responses: [new OA\Response(response: 200, description: Controller::OA_DESC_SUCCESS, content: new OA\JsonContent(ref: StopoversResponseDto::class))]
    )]
    public function stopovers(StopoverRequest $request): StopoversResponseDto
    {
        $trip = $this->locationController->stopovers(
            tripId: $request->tripId,
            startId: $request->startId,
            startTime: $request->startTime
        );

        return new StopoversResponseDto(
            trip: $trip,
            startTime: $request->startTime,
            startId: $request->startId,
            tripId: $request->tripId,
        );
    }
}
