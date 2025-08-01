<?php

namespace Tests\Unit\Jobs;

use App\Http\Controllers\Backend\LocationController;
use App\Jobs\PrefetchJob;
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
        $locationController = $this->createMock(LocationController::class);
        $locationController->expects($this->once())
            ->method('prefetch')
            ->with($point);

        $job = new PrefetchJob($point, $locationController);
        $job->handle();
    }
}
