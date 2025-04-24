<?php

namespace App\Http\Controllers\Backend;

use App\Dto\DeparturesDto;
use App\Dto\MotisApi\StopDto;
use App\Dto\MotisApi\TripDto;
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
     * @throws ConnectionException
     */
    public function departures(float $latitude, float $longitude): ?DeparturesDto
    {
        $stops = $this->transitousRequestService->getNearby($latitude, $longitude);
        if ($stops->isEmpty()) {
            return null;
        }

        /**
         * @var StopDto $firstStop
         */
        $firstStop = $stops->first();
        return new DeparturesDto(
            stop: $firstStop,
            departures: $this->transitousRequestService->getDepartures($firstStop->stopId, now())
        );
    }

    public function stopovers(string $tripId, string $startId, string $startTime): ?TripDto
    {
        //todo: make more than one request
        return $this->transitousRequestService->getStopTimes($tripId);
    }
}
