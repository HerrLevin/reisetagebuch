<?php

namespace App\Http\Controllers\Inertia;

use App\Http\Controllers\Backend\LocationController as BackendLocationController;
use App\Http\Controllers\Controller;
use App\Http\Requests\NearbyLocationRequest;
use App\Http\Resources\LocationResource;
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
            'locations' => LocationResource::collection($locations),
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
        ]);
    }
}
