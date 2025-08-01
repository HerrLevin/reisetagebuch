<?php

namespace Feature\Controllers\Api;

use App\Http\Controllers\Api\LocationController;
use App\Http\Controllers\Backend\LocationController as LocationControllerBackend;
use App\Jobs\PrefetchJob;
use Clickbar\Magellan\Data\Geometries\Point;
use Illuminate\Http\Request;
use Queue;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class LocationControllerTest extends TestCase
{
    public function test_prefetch(): void
    {
        Queue::fake();
        $locationControllerMock = $this->createMock(LocationControllerBackend::class);
        $locationControllerMock->expects($this->once())
            ->method('createTimestampedUserWaypoint')
            ->with($this->isString(), $this->isInstanceOf(Point::class));

        $locationController = new LocationController($locationControllerMock);
        $request = Request::create('/api/location/prefetch/48.8566/2.3522', 'GET');
        $request->setUserResolver(function () {
            return (object) ['id' => 1]; // Mock user ID
        });

        $this->expectException(HttpException::class);
        $locationController->prefetch(1, 2, $request);

        Queue::assertPushed(function (PrefetchJob $job) {
            return $job->point->getLatitude() === 48.8566 && $job->point->getLongitude() === 2.3522;
        });
    }
}
