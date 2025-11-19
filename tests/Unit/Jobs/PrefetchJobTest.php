<?php

namespace Tests\Unit\Jobs;

use App\Http\Controllers\Backend\LocationController;
use App\Http\Controllers\Backend\MapController;
use App\Hydrators\TripDtoHydrator;
use App\Jobs\PrefetchJob;
use App\Repositories\LocationRepository;
use App\Repositories\TransportTripRepository;
use App\Services\OverpassRequestService;
use App\Services\TransitousRequestService;
use Clickbar\Magellan\Data\Geometries\Point;
use Illuminate\Foundation\Testing\TestCase;

class PrefetchJobTest extends TestCase
{
    public function test_constructor_sets_properties()
    {
        $point = $this->createMock(Point::class);
        $locationController = $this->createMock(LocationController::class);

        $job = new PrefetchJob($point, $locationController);

        $this->assertSame($point, $job->point);
    }

    public function test_handle_calls_prefetch_on_location_controller()
    {
        $point = $this->createMock(Point::class);
        $repository = $this->createMock(LocationRepository::class);
        $repository->expects($this->once())
            ->method('recentNearbyRequests')
            ->with($point)
            ->willReturn(false);

        $repository->expects($this->once())
            ->method('createRequestLocation')
            ->with($point);

        $overpassRequestService = $this->createMock(OverpassRequestService::class);
        $overpassRequestService->expects($this->once())
            ->method('getElements')
            ->willReturn(['elements' => []]);

        $locationController = new LocationController(
            $repository,
            $this->createMock(TransitousRequestService::class),
            $this->createMock(TransportTripRepository::class),
            $this->createMock(TripDtoHydrator::class),
            $overpassRequestService,
            $this->createMock(MapController::class)
        );

        $job = new PrefetchJob($point, $locationController);
        $job->handle();
    }
}
