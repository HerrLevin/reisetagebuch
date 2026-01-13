<?php

namespace Feature\Controllers\Api;

use App\Http\Controllers\Api\LocationController;
use App\Http\Controllers\Backend\LocationController as LocationControllerBackend;
use App\Http\Requests\GeocodeRequest;
use App\Jobs\PrefetchJob;
use Clickbar\Magellan\Data\Geometries\Point;
use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\DataProvider;
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
        $request = Request::create(uri: '/api/location/prefetch/48.8566/2.3522', cookies: ['rtb_allow_history' => 'allow']);
        $request->setUserResolver(function () {
            return (object) ['id' => 1]; // Mock user ID
        });

        $this->expectException(HttpException::class);
        $locationController->prefetch(1, 2, $request);

        Queue::assertPushed(function (PrefetchJob $job) {
            return $job->point->getLatitude() === 48.8566 && $job->point->getLongitude() === 2.3522;
        });
    }

    public static function historyProvider(): array
    {
        return [
            'allow, allow-cookie' => [1, ['rtb_allow_history' => 'allow']],
            'allow, allow-cookie random content' => [1, ['rtb_allow_history' => 'fooBarBaz']],
            'disallow, disallow-cookie' => [0, ['rtb_disallow_history' => 'true']],
            'disallow, disallow-cookie random content' => [0, ['rtb_disallow_history' => 'fooBarBaz']],
            'disallow, no cookies' => [0, []],
            'disallow, both cookies' => [0, ['rtb_allow_history' => 'allow', 'rtb_disallow_history' => 'true']],
        ];
    }

    #[DataProvider('historyProvider')]
    public function test_prefetch_disallow_history(int $count, array $cookies): void
    {
        Queue::fake();
        $locationControllerMock = $this->createMock(LocationControllerBackend::class);
        $locationControllerMock->expects($this->exactly($count))
            ->method('createTimestampedUserWaypoint')
            ->with($this->isString(), $this->isInstanceOf(Point::class));

        $locationController = new LocationController($locationControllerMock);
        $request = Request::create(uri: '/api/location/prefetch/48.8566/2.3522', cookies: $cookies);
        $request->setUserResolver(function () {
            return (object) ['id' => 1]; // Mock user ID
        });

        $this->expectException(HttpException::class);
        $locationController->prefetch(1, 2, $request);

        Queue::assertPushed(function (PrefetchJob $job) {
            return $job->point->getLatitude() === 48.8566 && $job->point->getLongitude() === 2.3522;
        });
    }

    #[DataProvider('historyProvider')]
    public function test_geocode_disallow_history(int $count, array $cookies): void
    {
        $locationControllerMock = $this->createMock(LocationControllerBackend::class);
        $locationControllerMock->expects($this->exactly($count))
            ->method('createTimestampedUserWaypoint')
            ->with($this->isString(), $this->isInstanceOf(Point::class));

        $locationController = new LocationController($locationControllerMock);
        $request = GeocodeRequest::create(
            uri: '/api/location/geocode',
            parameters: ['query' => 'Eiffel Tower', 'latitude' => 48.8566, 'longitude' => 2.3522],
            cookies: $cookies
        );
        $request->setUserResolver(function () {
            return (object) ['id' => 1]; // Mock user ID
        });

        $result = $locationController->geocode($request);
        $this->assertEquals([], $result);
    }
}
