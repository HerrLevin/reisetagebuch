<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Repositories\LocationRepository;
use Illuminate\Database\Eloquent\Collection;

class LocationController extends Controller
{
    private LocationRepository $locationRepository;

    public function __construct(LocationRepository $locationRepository)
    {
        $this->locationRepository = $locationRepository;
    }

    public function nearby(float $latitude, float $longitude): Collection|\Illuminate\Support\Collection
    {
        if (!$this->locationRepository->recentNearbyRequests($latitude, $longitude)) {
            $this->locationRepository->createRequestLocation($latitude, $longitude);
            $this->locationRepository->fetchNearbyLocations($latitude, $longitude);
        }

        return $this->locationRepository->getNearbyLocations($latitude, $longitude);
    }
}
