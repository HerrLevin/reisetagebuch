<?php

namespace Tests\Unit\Controllers\Backend;

use App\Dto\DeparturesDto;
use App\Dto\MotisApi\LegDto;
use App\Dto\MotisApi\StopDto;
use App\Dto\MotisApi\TripDto;
use App\Http\Controllers\Backend\LocationController;
use App\Hydrators\TripDtoHydrator;
use App\Jobs\RerouteStops;
use App\Models\RequestLocation;
use App\Repositories\LocationRepository;
use App\Repositories\TransportTripRepository;
use App\Services\OverpassRequestService;
use App\Services\TransitousRequestService;
use Clickbar\Magellan\Data\Geometries\Point;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Client\ConnectionException;
use Mockery;
use PHPUnit\Framework\MockObject\Exception;
use Queue;
use Tests\TestCase;

class LocationControllerTest extends TestCase
{
    private LocationRepository $repository;

    private TransitousRequestService $transitousRequestService;

    private LocationController $controller;

    private TransportTripRepository $transportTripRepository;

    private TripDtoHydrator $tripDtoHydrator;

    private OverpassRequestService $overpassRequestService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = $this->createMock(LocationRepository::class);
        $this->transitousRequestService = $this->createMock(TransitousRequestService::class);
        $this->transportTripRepository = $this->createMock(TransportTripRepository::class);
        $this->tripDtoHydrator = $this->createMock(TripDtoHydrator::class);
        $this->overpassRequestService = $this->createMock(OverpassRequestService::class);

        $this->controller = new LocationController(
            $this->repository,
            $this->transitousRequestService,
            $this->transportTripRepository,
            $this->tripDtoHydrator,
            $this->overpassRequestService
        );
    }

    public function test_fetch_nearby_locations_runs_through_all_elements_even_on_exception()
    {
        $mockPoint = Mockery::mock(Point::class);
        $requestLocation = Mockery::mock(RequestLocation::class);

        $this->overpassRequestService->expects($this->once())
            ->method('setCoordinates')->with($mockPoint);
        $this->overpassRequestService->expects($this->once())
            ->method('getElements')
            ->willReturn([
                'elements' => [1, 2, 3],
            ]);
        $requestLocation->shouldReceive('update')->once();

        $this->overpassRequestService->expects($this->once())
            ->method('parseLocations')
            ->willReturnCallback(function () {
                yield 'loc1';
                yield 'loc2';
                yield 'loc3';
            });

        $this->repository->expects($this->exactly(3))
            ->method('updateOrCreateLocation')
            ->willReturnOnConsecutiveCalls(
                null,
                $this->throwException(new \Exception('fail')),
                null
            );

        $requestLocation->shouldAllowMockingProtectedMethods();
        $requestLocation->shouldReceive('increment')->times(3);
        $requestLocation->shouldReceive('update')->once();
        $requestLocation->shouldReceive('getAttribute')->with('to_fetch')->andReturn(3);

        // The method under test
        $this->controller->fetchNearbyLocations($mockPoint, $requestLocation);
        $this->assertTrue(true); // If we reach here, the loop did not break on exception
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

        $this->overpassRequestService->expects($this->once())
            ->method('getElements')
            ->willReturn(['elements' => []]);

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

        $this->overpassRequestService->expects($this->never())
            ->method('getElements');

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

        $this->overpassRequestService->expects($this->once())
            ->method('getElements')
            ->willReturn(['elements' => []]);

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
