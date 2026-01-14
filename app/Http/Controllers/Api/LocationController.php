<?php

namespace App\Http\Controllers\Api;

use App\Dto\ErrorDto;
use App\Dto\LocationHistoryDto;
use App\Dto\RequestLocationDto;
use App\Http\Controllers\Backend\LocationController as BackendLocationController;
use App\Http\Controllers\Controller;
use App\Http\Requests\DeparturesRequest;
use App\Http\Requests\GeocodeRequest;
use App\Http\Requests\LocationHistoryRequest;
use App\Http\Requests\NearbyLocationRequest;
use App\Http\Requests\StopoverRequest;
use App\Http\Resources\LocationDto;
use App\Jobs\PrefetchJob;
use App\Models\User;
use Carbon\Carbon;
use Clickbar\Magellan\Data\Geometries\Point;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    private BackendLocationController $locationController;

    public function __construct(BackendLocationController $locationController)
    {
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

    public function prefetch(float $latitude, float $longitude, Request $request): void
    {
        $point = Point::makeGeodetic($latitude, $longitude);
        if ($this->canStoreHistory($request)) {
            $this->locationController->createTimestampedUserWaypoint($request->user()->id, $point);
        }
        PrefetchJob::dispatch($point);

        abort('204');
    }

    public function getRecentRequestLocation(float $latitude, float $longitude): ?RequestLocationDto
    {
        $point = Point::makeGeodetic($latitude, $longitude);
        $location = $this->locationController->getRecentRequestLocation($point);

        if ($location === null) {
            abort(404, new ErrorDto('Location not found'));
        }

        return $location;
    }

    public function search(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'query' => 'nullable|string|min:3',
        ]);

        $point = Point::makeGeodetic($request->latitude, $request->longitude);

        $radius = 50_000; // 50km radius
        if (empty($request->get('query'))) {
            $radius = config('app.nearby.radius');
        }

        $locations = $this->locationController->searchNearby($point, $request->input('query'), $radius);

        return array_values($locations->map(fn ($location) => new LocationDto($location))->toArray());
    }

    public function nearby(NearbyLocationRequest $request)
    {
        $point = Point::makeGeodetic($request->latitude, $request->longitude);
        $locations = $this->locationController->nearby($point);

        return $locations->map(fn ($location) => new LocationDto($location))->toArray();
    }

    public function geocode(GeocodeRequest $request)
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

    private function geocodeMotis(string $query, ?Point $point)
    {
        try {
            $locations = $this->locationController->geocode($query, $point);
        } catch (ConnectionException $e) {
            abort(503, new ErrorDto('Connection error to geocoding service'));
        }

        return $locations;
    }

    public function index(LocationHistoryRequest $request): LocationHistoryDto
    {
        $when = $request->when ? Carbon::parse($request->when) : Carbon::now();

        return $this->locationController->index(
            $request->user()->id,
            $when->startOfDay(),
            $when->clone()->endOfDay()
        );
    }

    public function departures(DeparturesRequest $request): JsonResponse
    {
        $filter = $request->filter ? explode(',', $request->filter) : [];
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

        return response()->json([
            'departures' => $departures,
            'filter' => $filter,
            'requestTime' => $time->toIso8601String(),
            'requestIdentifier' => $request->identifier,
            'requestLatitude' => (float) $request->latitude,
            'requestLongitude' => (float) $request->longitude,
        ]);
    }

    public function stopovers(StopoverRequest $request): JsonResponse
    {
        $trip = $this->locationController->stopovers(
            tripId: $request->tripId,
            startId: $request->startId,
            startTime: $request->startTime
        );

        return response()->json([
            'trip' => $trip,
            'startTime' => $request->startTime,
            'startId' => $request->startId,
            'tripId' => $request->tripId,
        ]);
    }
}
