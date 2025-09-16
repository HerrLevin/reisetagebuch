<?php

namespace App\Http\Controllers\Inertia;

use App\Http\Controllers\Backend\LocationController as BackendLocationController;
use App\Http\Controllers\Controller;
use App\Http\Requests\DeparturesRequest;
use App\Http\Requests\LocationHistoryRequest;
use App\Http\Requests\NearbyLocationRequest;
use App\Http\Requests\StopoverRequest;
use App\Http\Resources\LocationDto;
use App\Models\User;
use Carbon\Carbon;
use Clickbar\Magellan\Data\Geometries\Point;
use Inertia\Response;
use Inertia\ResponseFactory;

class LocationController extends Controller
{
    private BackendLocationController $locationController;

    public function __construct(BackendLocationController $locationController)
    {
        $this->locationController = $locationController;
    }

    public function index(LocationHistoryRequest $request): Response|ResponseFactory
    {
        $when = $request->when ? Carbon::parse($request->when) : Carbon::now();

        $data = $this->locationController->index($request->user()->id, $when->startOfDay(), $when->clone()->endOfDay());

        return inertia('LocationHistory/Index', [
            'locations' => $data,
            'when' => $when->toIso8601String(),
        ]);
    }

    public function nearby(NearbyLocationRequest $request): Response|ResponseFactory
    {
        $point = Point::makeGeodetic($request->latitude, $request->longitude);
        $locations = $this->locationController->nearby($point);

        return inertia('NewPostDialog/ListLocations', [
            'locations' => $locations->map(fn ($location) => new LocationDto($location)),
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
        ]);
    }

    public function departures(DeparturesRequest $request): Response|ResponseFactory
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

        return inertia('NewPostDialog/ListDepartures', [
            'departures' => $departures,
            'filter' => $filter,
            'requestTime' => $time->toIso8601String(),
            'requestIdentifier' => $request->identifier,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
        ]);
    }

    public function stopovers(StopoverRequest $request): Response|ResponseFactory
    {
        $trip = $this->locationController->stopovers(
            tripId: $request->tripId,
            startId: $request->startId,
            startTime: $request->startTime
        );

        return inertia('NewPostDialog/ListStopovers', [
            'trip' => $trip,
            'startTime' => $request->startTime,
            'startId' => $request->startId,
            'tripId' => $request->tripId,
        ]);
    }
}
