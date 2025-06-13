<?php

namespace App\Http\Controllers\Inertia;

use App\Http\Controllers\Backend\LocationController as BackendLocationController;
use App\Http\Controllers\Controller;
use App\Http\Requests\DeparturesRequest;
use App\Http\Requests\NearbyLocationRequest;
use App\Http\Requests\StopoverRequest;
use App\Http\Resources\LocationDto;
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

    public function nearby(NearbyLocationRequest $request): Response|ResponseFactory
    {
        $point = Point::makeGeodetic($request->latitude, $request->longitude);
        $locations = $this->locationController->nearby($point);

        return inertia('NewPostDialog/ListLocations', [
            'locations' => $locations->map(fn($location) => new LocationDto($location)),
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
        ]);
    }

    public function departures(DeparturesRequest $request): Response|ResponseFactory
    {
        $point = Point::makeGeodetic($request->latitude, $request->longitude);
        $filter = $request->filter ? explode(',', $request->filter) : [];
        $departures = $this->locationController->departures($point, now(), $filter);

        return inertia('NewPostDialog/ListDepartures', [
            'departures' => $departures,
            'filter' => $filter,
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
