<?php

namespace Tests\Unit\Controllers\Backend;

use App\Dto\DeparturesDto;
use App\Dto\MotisApi\StopDto;
use App\Dto\MotisApi\TripDto;
use App\Http\Controllers\Backend\LocationController;
use App\Repositories\LocationRepository;
use App\Services\TransitousRequestService;
use Clickbar\Magellan\Data\Geometries\Point;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Client\ConnectionException;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

class LocationControllerTest extends TestCase
{
    private LocationRepository $repository;
    private TransitousRequestService $transitousRequestService;
    private LocationController $controller;

    public function setUp(): void
    {
        parent::setUp();
        $this->repository = $this->createMock(LocationRepository::class);
        $this->transitousRequestService = $this->createMock(TransitousRequestService::class);
        $this->controller = new LocationController($this->repository, $this->transitousRequestService);
    }

    /**
     * @throws Exception
     */
    public function testPrefetch(): void
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

    public function testPrefetchWithNoRequests(): void
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

    public function testNearby(): void
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
            ->willReturn(new Collection());

        $result = $this->controller->nearby($point);
        $this->assertInstanceOf(Collection::class, $result);
    }

    /**
     * @throws Exception
     * @throws ConnectionException
     */
    public function testDepartures(): void
    {
        $point = $this->createMock(Point::class);
        $stops = new Collection([new StopDto('id', 'name', 1.1, 1.1, 123)]);
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
    public function testDeparturesWithNoStops(): void
    {
        $point = $this->createMock(Point::class);
        $stops = new Collection();
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
    public function testStopovers(): void
    {
        $tripId = 'tripId';
        $startId = 'startId';
        $startTime = now();
        $trip = new TripDto($tripId, $startId, $startTime, []);

        $this->transitousRequestService->expects($this->once())
            ->method('getStopTimes')
            ->with($tripId)
            ->willReturn($trip);

        $result = $this->controller->stopovers($tripId, $startId, $startTime);
        $this->assertEquals($trip, $result);
    }
}
