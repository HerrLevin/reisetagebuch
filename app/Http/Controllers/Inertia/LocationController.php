<?php

namespace App\Http\Controllers\Inertia;

use App\Http\Controllers\Backend\LocationController as BackendLocationController;
use App\Http\Controllers\Controller;
use App\Http\Requests\NearbyLocationRequest;
use App\Http\Resources\LocationResource;
use App\Repositories\LocationRepository;
use Inertia\Response;
use Inertia\ResponseFactory;

class LocationController extends Controller
{
    private BackendLocationController $locationController;
    private LocationRepository $locationRepository;

    public function __construct(BackendLocationController $locationController, LocationRepository $locationRepository)
    {
        $this->locationRepository = $locationRepository;
        $this->locationController = $locationController;
    }

    public function nearby(NearbyLocationRequest $request): Response|ResponseFactory
    {
        $locations = $this->locationRepository->getNearbyLocations($request->latitude, $request->longitude);

        return inertia('NewPostDialog/ListLocations', [
            'locations' => LocationResource::collection($locations),
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
        ]);
    }
}
