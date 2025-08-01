<?php

declare(strict_types=1);

namespace Tests\Unit\Repositories;

use App\Models\RequestLocation;
use App\Repositories\LocationRepository;
use App\Services\OsmNameService;
use App\Services\OverpassRequestService;
use Clickbar\Magellan\Data\Geometries\Point;
use Exception;
use Mockery;
use Tests\TestCase;

class LocationRepositoryTest extends TestCase
{
    public function test_fetch_nearby_locations_runs_through_all_elements_even_on_exception()
    {
        $mockOsmNameService = Mockery::mock(OsmNameService::class);
        $mockService = Mockery::mock(OverpassRequestService::class);
        $mockPoint = Mockery::mock(Point::class);
        $requestLocation = Mockery::mock(RequestLocation::class);

        $mockService->shouldReceive('setCoordinates')->once()->with($mockPoint);
        $mockService->shouldReceive('getElements')->once()->andReturn([
            'elements' => [1, 2, 3],
        ]);
        $requestLocation->shouldReceive('update')->once();

        $mockService->shouldReceive('parseLocations')->once()->andReturnUsing(function () {
            yield 'loc1';
            yield 'loc2';
            yield 'loc3';
        });

        $repo = Mockery::mock(LocationRepository::class.'[updateOrCreateLocation]', [$mockOsmNameService, $mockService]);
        $repo->shouldAllowMockingProtectedMethods();
        $repo->shouldReceive('updateOrCreateLocation')
            ->with('loc1')->once()->andReturnNull();
        $repo->shouldReceive('updateOrCreateLocation')
            ->with('loc2')->once()->andThrow(new Exception('fail'));
        $repo->shouldReceive('updateOrCreateLocation')
            ->with('loc3')->once()->andReturnNull();

        $requestLocation->shouldAllowMockingProtectedMethods();
        $requestLocation->shouldReceive('increment')->times(3);
        $requestLocation->shouldReceive('update')->once();
        $requestLocation->shouldReceive('getAttribute')->with('to_fetch')->andReturn(3);

        // The method under test
        $repo->fetchNearbyLocations($mockPoint, $requestLocation);
        $this->assertTrue(true); // If we reach here, the loop did not break on exception
    }
}
