<?php

namespace Tests\Unit\Controllers\Backend;

use App\Dto\DeparturesDto;
use App\Dto\MotisApi\LegDto;
use App\Dto\MotisApi\StopDto;
use App\Dto\MotisApi\TripDto;
use App\Http\Controllers\Backend\LocationController;
use App\Hydrators\TripDtoHydrator;
use App\Jobs\RerouteStops;
use App\Repositories\LocationRepository;
use App\Repositories\TransportTripRepository;
use App\Services\TransitousRequestService;
use Clickbar\Magellan\Data\Geometries\Point;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Client\ConnectionException;
use PHPUnit\Framework\MockObject\Exception;
use Queue;

class LocationControllerTest extends \Tests\TestCase
{
    private LocationRepository $repository;

    private TransitousRequestService $transitousRequestService;

    private LocationController $controller;

    private TransportTripRepository $transportTripRepository;

    private TripDtoHydrator $tripDtoHydrator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = $this->createMock(LocationRepository::class);
        $this->transitousRequestService = $this->createMock(TransitousRequestService::class);
        $this->transportTripRepository = $this->createMock(TransportTripRepository::class);
        $this->tripDtoHydrator = $this->createMock(TripDtoHydrator::class);

        $this->controller = new LocationController(
            $this->repository,
            $this->transitousRequestService,
            $this->transportTripRepository,
            $this->tripDtoHydrator
        );
    }

    /**
     * @throws Exception
     */
    public function test_prefetch(): void
    {
        $point = $this->createMock(Point::class);
        $this->repository->expects($this->once())
            ->method('recentNearbyRequests')
            ->with($point)
            ->willReturn(false);

        $this->repository->expects($this->once())
            ->method('deleteOldNearbyRequests');

        $this->repository->expects($this->once())
            ->method('createRequestLocation')
            ->with($point);

        $this->repository->expects($this->once())
            ->method('fetchNearbyLocations')
            ->with($point);

        $this->controller->prefetch($point);
    }

    public function test_prefetch_with_no_requests(): void
    {
        $point = $this->createMock(Point::class);
        $this->repository->expects($this->once())
            ->method('recentNearbyRequests')
            ->with($point)
            ->willReturn(true);

        $this->repository->expects($this->never())
            ->method('deleteOldNearbyRequests');

        $this->repository->expects($this->never())
            ->method('createRequestLocation');

        $this->repository->expects($this->never())
            ->method('fetchNearbyLocations');

        $this->controller->prefetch($point);
    }

    public function test_nearby(): void
    {
        $point = $this->createMock(Point::class);
        $this->repository->expects($this->once())
            ->method('recentNearbyRequests')
            ->with($point)
            ->willReturn(false);

        $this->repository->expects($this->once())
            ->method('deleteOldNearbyRequests');

        $this->repository->expects($this->once())
            ->method('createRequestLocation')
            ->with($point);

        $this->repository->expects($this->once())
            ->method('fetchNearbyLocations')
            ->with($point);

        $this->repository->expects($this->once())
            ->method('getNearbyLocations')
            ->with($point)
            ->willReturn(new Collection);

        $result = $this->controller->nearby($point);
        $this->assertInstanceOf(Collection::class, $result);
    }

    /**
     * @throws Exception
     * @throws ConnectionException
     */
    public function test_departures(): void
    {
        $point = $this->createMock(Point::class);

        $demoStop = new StopDto()
            ->setStopId('stopId')
            ->setName('Demo Stop')
            ->setLatitude(48.8566)
            ->setLongitude(2.3522)
            ->setDistance(100);

        $stops = new Collection([$demoStop]);
        $departures = new Collection([$this->createMock(TripDto::class)]);
        $time = now();
        $this->transitousRequestService->expects($this->once())
            ->method('getNearby')
            ->with($point)
            ->willReturn($stops);
        $this->transitousRequestService->expects($this->once())
            ->method('getDepartures')
            ->with($stops->first()->stopId, $time)
            ->willReturn($departures);
        $result = $this->controller->departuresNearby($point, $time);
        $this->assertInstanceOf(DeparturesDto::class, $result);
        $this->assertEquals($departures, $result->departures);
    }

    /**
     * @throws Exception
     * @throws ConnectionException
     */
    public function test_departures_with_no_stops(): void
    {
        $point = $this->createMock(Point::class);
        $stops = new Collection;
        $time = now();
        $this->transitousRequestService->expects($this->once())
            ->method('getNearby')
            ->with($point)
            ->willReturn($stops);
        $result = $this->controller->departuresNearby($point, $time);
        $this->assertNull($result);
    }

    /**
     * @throws Exception
     */
    public function test_stopovers(): void
    {
        Queue::fake([
            RerouteStops::class,
        ]);
        $tripId = 'tripId';
        $startId = 'startId';
        $startTime = now();
        $leg = new LegDto()
            ->setMode('bus')
            ->setRouteShortName('Route 42')
            ->setFrom($this->createStopPlaceDto())
            ->setIntermediateStops([])
            ->setTo($this->createStopPlaceDto());

        $trip = new TripDto()->setLegs([$leg]);

        $this->transitousRequestService->expects($this->once())
            ->method('getStopTimes')
            ->with($tripId)
            ->willReturn($trip);

        $result = $this->controller->stopovers($tripId, $startId, $startTime);
        $this->assertEquals($trip, $result);

        Queue::assertPushed(RerouteStops::class);
    }
}
