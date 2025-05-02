<?php

namespace App\Http\Controllers\Inertia;

use App\Http\Controllers\Backend\LocationController as BackendLocationController;
use App\Http\Controllers\Controller;
use App\Http\Requests\NearbyLocationRequest;
use App\Http\Requests\StopoverRequest;
use App\Http\Resources\LocationDto;
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
        $locations = $this->locationController->nearby($request->latitude, $request->longitude);

        return inertia('NewPostDialog/ListLocations', [
            'locations' => $locations->map(fn($location) => new LocationDto($location)),
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
        ]);
    }

    public function departures(NearbyLocationRequest $request): Response|ResponseFactory
    {
        $departures = $this->locationController->departures($request->latitude, $request->longitude);

        return inertia('NewPostDialog/ListDepartures', [
            'departures' => $departures,
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
