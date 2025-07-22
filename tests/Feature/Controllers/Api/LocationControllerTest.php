<?php

namespace Feature\Controllers\Api;

use App\Http\Controllers\Api\LocationController;
use App\Http\Controllers\Backend\LocationController as LocationControllerBackend;
use Clickbar\Magellan\Data\Geometries\Point;
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

        $this->expectException(HttpException::class);
        $locationController->prefetch(1, 2);
    }
}
