<?php

namespace Feature\Controllers\Api;

use App\Http\Controllers\Api\LocationController;
use App\Http\Controllers\Backend\LocationController as LocationControllerBackend;
use Clickbar\Magellan\Data\Geometries\Point;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class LocationControllerTest extends TestCase
{
    public function test_prefetch(): void
    {
        $locationControllerMock = $this->createMock(LocationControllerBackend::class);
        $locationControllerMock->expects($this->once())
            ->method('prefetch')
            ->with($this->isInstanceOf(Point::class));

        $locationController = new LocationController($locationControllerMock);
        $request = Request::create('/api/location/prefetch/48.8566/2.3522', 'GET');
        $request->setUserResolver(function () {
            return (object) ['id' => 1]; // Mock user ID
        });

        $this->expectException(HttpException::class);
        $locationController->prefetch(1, 2, $request);
    }
}
