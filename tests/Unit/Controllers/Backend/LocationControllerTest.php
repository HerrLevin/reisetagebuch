<?php

namespace Tests\Unit\Controllers\Backend;

use App\Dto\DeparturesDto;
use App\Dto\MotisApi\LegDto;
use App\Dto\MotisApi\StopDto;
use App\Dto\MotisApi\TripDto;
use App\Http\Controllers\Backend\LocationController;
use App\Http\Controllers\Backend\MapController;
use App\Hydrators\TripDtoHydrator;
use App\Jobs\PrefetchJob;
use App\Jobs\RerouteStops;
use App\Models\Location;
use App\Models\RequestLocation;
use App\Models\TransportTrip;
use App\Models\TransportTripStop;
use App\Repositories\LocationRepository;
use App\Repositories\TransportTripRepository;
use App\Services\OverpassLocationRequestService;
use App\Services\OverpassRadiusRequestService;
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

    private OverpassRadiusRequestService $overpassRequestService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = $this->createMock(LocationRepository::class);
        $this->transitousRequestService = $this->createMock(TransitousRequestService::class);
        $this->transportTripRepository = $this->createMock(TransportTripRepository::class);
        $this->tripDtoHydrator = $this->createMock(TripDtoHydrator::class);
        $this->overpassRequestService = $this->createMock(OverpassRadiusRequestService::class);

        $this->controller = new LocationController(
            $this->repository,
            $this->transitousRequestService,
            $this->transportTripRepository,
            $this->tripDtoHydrator,
            $this->overpassRequestService,
            $this->createMock(MapController::class),
            new OverpassLocationRequestService,
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
            ->method('updateOrCreateOsmLocation')
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
        $this->controller->fetchNearbyLocations($mockPoint, $requestLocation, 100);
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
            ->method('createRequestLocation')
            ->with($point);

        $this->overpassRequestService->expects($this->once())
            ->method('getElements')
            ->willReturn(['elements' => []]);

        $radius = config('app.overpass.radius');
        $this->controller->prefetch($point, $radius);
    }

    public function test_prefetch_with_no_requests(): void
    {
        $point = $this->createMock(Point::class);
        $this->repository->expects($this->once())
            ->method('recentNearbyRequests')
            ->with($point)
            ->willReturn(true);

        $this->repository->expects($this->never())
            ->method('createRequestLocation');

        $this->overpassRequestService->expects($this->never())
            ->method('getElements');

        $radius = config('app.overpass.radius');
        $this->controller->prefetch($point, $radius);
    }

    public function test_nearby(): void
    {
        Queue::fake();
        $point = $this->createMock(Point::class);

        $this->repository->expects($this->once())
            ->method('getNearbyLocations')
            ->with($point)
            ->willReturn(new Collection);

        $result = $this->controller->nearby($point);
        $this->assertInstanceOf(Collection::class, $result);
        Queue::assertPushed(PrefetchJob::class);
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
        $departureTime = now();
        $arrivalTime = now()->addMinutes(30);

        $leg = new LegDto()
            ->setMode('bus')
            ->setTripId($tripId)
            ->setRouteShortName('Route 42')
            ->setFrom($this->createStopPlaceDto('fromStopId', 'From Stop', 48.0, 2.0, $arrivalTime, $departureTime))
            ->setIntermediateStops([])
            ->setRealTime(false)
            ->setTo($this->createStopPlaceDto('toStopId', 'To Stop', 49.0, 3.0, $arrivalTime, $departureTime));

        $apiTrip = new TripDto()->setLegs([$leg]);

        // Build model instances for the DB creation flow
        $fromLocation = new Location;
        $fromLocation->forceFill(['id' => 'loc-from', 'name' => 'From Stop']);
        $fromLocation->latitude = 48.0;
        $fromLocation->longitude = 2.0;

        $toLocation = new Location;
        $toLocation->forceFill(['id' => 'loc-to', 'name' => 'To Stop']);
        $toLocation->latitude = 49.0;
        $toLocation->longitude = 3.0;

        $fromStop = new TransportTripStop;
        $fromStop->forceFill([
            'id' => 'stop-from',
            'cancelled' => false,
            'arrival_time' => $arrivalTime,
            'departure_time' => $departureTime,
            'arrival_delay' => null,
            'departure_delay' => null,
        ]);

        $toStop = new TransportTripStop;
        $toStop->forceFill([
            'id' => 'stop-to',
            'cancelled' => false,
            'arrival_time' => $arrivalTime,
            'departure_time' => $departureTime,
            'arrival_delay' => null,
            'departure_delay' => null,
        ]);

        $transportTrip = new TransportTrip;
        $transportTrip->forceFill([
            'mode' => 'bus',
            'foreign_trip_id' => $tripId,
            'provider' => 'transitous',
            'line_name' => 'Route 42',
            'route_long_name' => null,
            'trip_short_name' => null,
            'display_name' => null,
            'route_color' => null,
            'route_text_color' => null,
        ]);
        $transportTrip->setRelation('continuesAs', null);

        // Trip not found in DB
        $this->transportTripRepository->expects($this->once())
            ->method('getTripByIdentifier')
            ->willReturn(null);

        // Fetch from API
        $this->transitousRequestService->expects($this->once())
            ->method('getStopTimes')
            ->with($tripId)
            ->willReturn($apiTrip);

        // Create trip in DB
        $this->transportTripRepository->expects($this->once())
            ->method('getOrCreateTrip')
            ->willReturn($transportTrip);

        // Create locations for each stop
        $this->repository->expects($this->exactly(2))
            ->method('getOrCreateLocationByIdentifier')
            ->willReturnOnConsecutiveCalls($fromLocation, $toLocation);

        // Add stops to trip
        $this->transportTripRepository->expects($this->exactly(2))
            ->method('addStopToTrip')
            ->willReturnOnConsecutiveCalls($fromStop, $toStop);

        // TripDtoHydrator used in createStopovers
        $this->tripDtoHydrator->expects($this->exactly(2))
            ->method('hydrateStopPlace')
            ->willReturnOnConsecutiveCalls(
                $this->createStopPlaceDto('fromStopId', 'From Stop', 48.0, 2.0, $arrivalTime, $departureTime),
                $this->createStopPlaceDto('toStopId', 'To Stop', 49.0, 3.0, $arrivalTime, $departureTime),
            );

        $result = $this->controller->stopovers($tripId);

        $this->assertInstanceOf(TripDto::class, $result);
        $this->assertCount(1, $result->legs);
        $this->assertEquals('bus', $result->legs[0]->mode);
        $this->assertEquals($tripId, $result->legs[0]->tripId);
        $this->assertEquals('Route 42', $result->legs[0]->routeShortName);
        $this->assertNotNull($result->legs[0]->from);
        $this->assertNotNull($result->legs[0]->to);

        Queue::assertPushed(RerouteStops::class);
    }
}
