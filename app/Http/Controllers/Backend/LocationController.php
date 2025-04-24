<?php

namespace App\Http\Controllers\Backend;

use App\Dto\MotisApi\StopDto;
use App\Http\Controllers\Controller;
use App\Repositories\LocationRepository;
use App\Services\TransitousRequestService;
use Illuminate\Database\Eloquent\Collection as DbCollection;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Collection;

class LocationController extends Controller
{
    private LocationRepository $locationRepository;
    private TransitousRequestService $transitousRequestService;

    public function __construct(LocationRepository $locationRepository, TransitousRequestService $transitousRequestService)
    {
        $this->locationRepository = $locationRepository;
        $this->transitousRequestService = $transitousRequestService;
    }

    public function nearby(float $latitude, float $longitude): DbCollection|Collection
    {
        if (!$this->locationRepository->recentNearbyRequests($latitude, $longitude)) {
            $this->locationRepository->createRequestLocation($latitude, $longitude);
            $this->locationRepository->fetchNearbyLocations($latitude, $longitude);
        }

        return $this->locationRepository->getNearbyLocations($latitude, $longitude);
    }

    /**
     * @returns Collection|StopDto[]
     * @throws ConnectionException
     */
    public function departures(float $latitude, float $longitude): Collection
    {
        $stops = $this->transitousRequestService->getNearby($latitude, $longitude);
        if ($stops->isEmpty()) {
            return collect();
        }

        /**
         * @var StopDto $firstStop
         */
        $firstStop = $stops->first();
        return $this->transitousRequestService->getDepartures($firstStop->stopId, now());
    }
}
