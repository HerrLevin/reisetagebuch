<?php

namespace App\Http\Controllers\Inertia;

use App\Http\Controllers\Backend\VenueController as BackendVenueController;
use App\Http\Controllers\Controller;
use App\Http\Requests\NearbyVenueRequest;
use App\Repositories\VenueRepository;
use Inertia\Response;
use Inertia\ResponseFactory;

class VenueController extends Controller
{
    private BackendVenueController $venueController;
    private VenueRepository $venueRepository;

    public function __construct(BackendVenueController $venueController, VenueRepository $venueRepository)
    {
        $this->venueRepository = $venueRepository;
        $this->venueController = $venueController;
    }

    public function nearby(NearbyVenueRequest $request): Response|ResponseFactory
    {
        $venues = $this->venueRepository->getNearbyVenues($request->latitude, $request->longitude);

        return inertia('NewPostDialog/ListLocations', [
            'venues' => $venues,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
        ]);
    }
}
