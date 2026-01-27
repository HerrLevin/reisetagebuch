<?php

namespace Tests\Unit\Services;

use App\Exceptions\BrouterRouteCreationFailed;
use App\Services\BrouterRequestService;
use App\Services\VersionService;
use Clickbar\Magellan\Data\Geometries\Point;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Foundation\Testing\TestCase;
use Mockery;
use PHPUnit\Framework\MockObject\MockObject;

class BRouterRequestServiceTest extends TestCase
{
    private BrouterRequestService $service;

    private MockObject $clientMock;

    private MockObject $versionServiceMock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->app->singleton(ExceptionHandler::class, function () {
            $mock = Mockery::mock(ExceptionHandler::class);
            $mock->shouldReceive('report')->andReturn(null);
            $mock->shouldReceive('storage_path')->andReturn('');

            return $mock;
        });
        $this->clientMock = $this->createMock(Client::class);
        $this->versionServiceMock = $this->createMock(VersionService::class);
        $this->service = new BrouterRequestService($this->versionServiceMock, $this->clientMock);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_get_route_road()
    {
        $start = $this->createMock(Point::class);
        $start->method('getLongitude')->willReturn(1.0);
        $start->method('getLatitude')->willReturn(2.0);
        $stop = $this->createMock(Point::class);
        $stop->method('getLongitude')->willReturn(3.0);
        $stop->method('getLatitude')->willReturn(4.0);
        $pathType = 'road';
        $expectedGeometry = '{"type":"LineString","coordinates":[[1,2],[3,4]]}';
        $responseBody = json_encode([
            'features' => [
                ['geometry' => json_decode($expectedGeometry, true)],
            ],
        ]);

        $this->clientMock->expects($this->once())
            ->method('get')
            ->with($this->stringContains('profile=car-fast'))
            ->willReturn(new Response(200, [], $responseBody));

        $result = $this->service->getRoute($start, $stop, $pathType);
        $this->assertEquals($expectedGeometry, $result);
    }

    public function test_get_route_rail()
    {
        $start = $this->createMock(Point::class);
        $start->method('getLongitude')->willReturn(1.0);
        $start->method('getLatitude')->willReturn(2.0);
        $stop = $this->createMock(Point::class);
        $stop->method('getLongitude')->willReturn(3.0);
        $stop->method('getLatitude')->willReturn(4.0);
        $pathType = 'rail';
        $expectedGeometry = '{"type":"LineString","coordinates":[[1,2],[3,4]]}';
        $responseBody = json_encode([
            'features' => [
                ['geometry' => json_decode($expectedGeometry, true)],
            ],
        ]);

        $this->clientMock->expects($this->once())
            ->method('post')
            ->with($this->stringContains('profile/custom_'))
            ->willReturn(new Response(200, [], json_encode(['profileid' => 'custom123'])));

        $this->clientMock->expects($this->once())
            ->method('get')
            ->with($this->stringContains('profile=custom123'))
            ->willReturn(new Response(200, [], $responseBody));

        $result = $this->service->getRoute($start, $stop, $pathType);
        $this->assertEquals($expectedGeometry, $result);
    }

    public function test_cache_temp_profile()
    {
        $this->clientMock->expects($this->once())
            ->method('post')
            ->with($this->stringContains('profile/custom_'))
            ->willReturn(new Response(200, [], json_encode(['profileid' => 'custom123'])));
        $start = $this->createMock(Point::class);
        $start->method('getLongitude')->willReturn(1.0);
        $start->method('getLatitude')->willReturn(2.0);
        $stop = $this->createMock(Point::class);
        $stop->method('getLongitude')->willReturn(3.0);
        $stop->method('getLatitude')->willReturn(4.0);
        $stop2 = $this->createMock(Point::class);
        $stop2->method('getLongitude')->willReturn(5.0);
        $stop2->method('getLatitude')->willReturn(6.0);
        $pathType = 'rail';
        $expectedGeometry = '{"type":"LineString","coordinates":[[1,2],[3,4]]}';
        $responseBody = json_encode([
            'features' => [
                ['geometry' => json_decode($expectedGeometry, true)],
            ],
        ]);

        $this->clientMock->method('get')
            ->willReturnOnConsecutiveCalls(new Response(200, [], $responseBody), new Response(200, [], $responseBody));

        // First call to getRoute - should create temp profile
        $result1 = $this->service->getRoute($start, $stop, $pathType);
        $this->assertEquals($expectedGeometry, $result1);

        // Second call to getRoute - should use cached temp profile
        $result2 = $this->service->getRoute($start, $stop2, $pathType);
        $this->assertEquals($expectedGeometry, $result2);
    }

    public function test_get_route_rail_fallback()
    {
        $start = $this->createMock(Point::class);
        $start->method('getLongitude')->willReturn(1.0);
        $start->method('getLatitude')->willReturn(2.0);
        $stop = $this->createMock(Point::class);
        $stop->method('getLongitude')->willReturn(3.0);
        $stop->method('getLatitude')->willReturn(4.0);
        $pathType = 'rail';
        $expectedGeometry = '{"type":"LineString","coordinates":[[1,2],[3,4]]}';
        $responseBody = json_encode([
            'features' => [
                ['geometry' => json_decode($expectedGeometry, true)],
            ],
        ]);

        $this->clientMock->expects($this->once())
            ->method('post')
            ->willReturn(new Response(500, [], 'error'));

        $this->clientMock->expects($this->once())
            ->method('get')
            ->with($this->stringContains('profile=rail'))
            ->willReturn(new Response(200, [], $responseBody));

        $result = $this->service->getRoute($start, $stop, $pathType);
        $this->assertEquals($expectedGeometry, $result);
    }

    public function test_get_route_invalid_path_type()
    {
        $start = $this->createMock(Point::class);
        $start->method('getLongitude')->willReturn(1.0);
        $start->method('getLatitude')->willReturn(2.0);
        $stop = $this->createMock(Point::class);
        $stop->method('getLongitude')->willReturn(3.0);
        $stop->method('getLatitude')->willReturn(4.0);
        $pathType = 'invalid';

        $this->expectException(BrouterRouteCreationFailed::class);
        $this->service->getRoute($start, $stop, $pathType);
    }

    public function test_get_route_bad_status()
    {
        $start = $this->createMock(Point::class);
        $start->method('getLongitude')->willReturn(1.0);
        $start->method('getLatitude')->willReturn(2.0);
        $stop = $this->createMock(Point::class);
        $stop->method('getLongitude')->willReturn(3.0);
        $stop->method('getLatitude')->willReturn(4.0);
        $pathType = 'road';

        $this->clientMock->expects($this->once())
            ->method('get')
            ->willReturn(new Response(500, [], 'error'));

        $this->expectException(BrouterRouteCreationFailed::class);
        $this->service->getRoute($start, $stop, $pathType);
    }

    public function test_get_route_error_in_json()
    {
        $start = $this->createMock(Point::class);
        $start->method('getLongitude')->willReturn(1.0);
        $start->method('getLatitude')->willReturn(2.0);
        $stop = $this->createMock(Point::class);
        $stop->method('getLongitude')->willReturn(3.0);
        $stop->method('getLatitude')->willReturn(4.0);
        $pathType = 'road';
        $responseBody = json_encode(['error' => 'some error']);

        $this->clientMock->expects($this->once())
            ->method('get')
            ->willReturn(new Response(200, [], $responseBody));

        $this->expectException(BrouterRouteCreationFailed::class);
        $this->service->getRoute($start, $stop, $pathType);
    }

    public function test_get_route_no_features()
    {
        $start = $this->createMock(Point::class);
        $start->method('getLongitude')->willReturn(1.0);
        $start->method('getLatitude')->willReturn(2.0);
        $stop = $this->createMock(Point::class);
        $stop->method('getLongitude')->willReturn(3.0);
        $stop->method('getLatitude')->willReturn(4.0);
        $pathType = 'road';
        $responseBody = json_encode(['features' => []]);

        $this->clientMock->expects($this->once())
            ->method('get')
            ->willReturn(new Response(200, [], $responseBody));

        $this->expectException(BrouterRouteCreationFailed::class);
        $this->service->getRoute($start, $stop, $pathType);
    }
}
