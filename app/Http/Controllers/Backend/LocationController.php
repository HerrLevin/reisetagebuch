<?php

namespace App\Http\Controllers\Backend;

use App\Dto\DeparturesDto;
use App\Dto\MotisApi\StopDto;
use App\Dto\MotisApi\TripDto;
use App\Http\Controllers\Controller;
use App\Repositories\LocationRepository;
use App\Services\TransitousRequestService;
use Carbon\Carbon;
use Clickbar\Magellan\Data\Geometries\Point;
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

    public function prefetch(Point $point): void
    {
        if (!$this->locationRepository->recentNearbyRequests($point)) {
            $this->locationRepository->deleteOldNearbyRequests();
            $this->locationRepository->createRequestLocation($point);
            $this->locationRepository->fetchNearbyLocations($point);
        }
    }

    public function nearby(Point $point): DbCollection|Collection
    {
        $this->prefetch($point);

        return $this->locationRepository->getNearbyLocations($point);
    }

    /**
     * @throws ConnectionException
     */
    public function departures(Point $point, Carbon $time): ?DeparturesDto
    {
        $stops = $this->transitousRequestService->getNearby($point);
        if ($stops->isEmpty()) {
            return null;
        }

        /**
         * @var StopDto $firstStop
         */
        $firstStop = $stops->first();
        return new DeparturesDto(
            stop: $firstStop,
            departures: $this->transitousRequestService->getDepartures($firstStop->stopId, $time)
        );
    }

    public function stopovers(string $tripId, string $startId, string $startTime): ?TripDto
    {
        //todo: make more than one request
        return $this->transitousRequestService->getStopTimes($tripId);
    }
}
