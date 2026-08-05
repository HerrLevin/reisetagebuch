<?php

namespace Tests\Feature;

use App\Dto\MotisApi\LegDto;
use App\Dto\MotisApi\LegGeometryDto;
use App\Dto\MotisApi\TripDto;
use App\Enums\RouteSegmentSource;
use App\Enums\TransportMode;
use App\Http\Controllers\Backend\RerouteStopsController;
use App\Models\Location;
use App\Models\RouteSegment;
use App\Models\TransportTrip;
use App\Models\TransportTripStop;
use App\Repositories\TransportTripRepository;
use App\Services\BrouterRequestService;
use App\Services\GeoService;
use Clickbar\Magellan\Data\Geometries\LineString;
use Clickbar\Magellan\Data\Geometries\Point;
use Clickbar\Magellan\IO\Parser\Geojson\GeojsonParser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;
use Traewelling\GooglePolyline\MagellanPolylineTranscoder;

class RerouteStopsControllerTest extends TestCase
{
    use RefreshDatabase;

    private function makeController(?BrouterRequestService $brouterRequestService = null): RerouteStopsController
    {
        return new RerouteStopsController(
            $brouterRequestService ?? Mockery::mock(BrouterRequestService::class),
            new TransportTripRepository,
            app(GeojsonParser::class),
            new MagellanPolylineTranscoder,
            new GeoService
        );
    }

    private function makeStop(TransportTrip $trip, int $sequence, float $lat, float $lon): TransportTripStop
    {
        $location = Location::factory()->create([
            'location' => Point::makeGeodetic($lat, $lon),
        ]);

        return TransportTripStop::factory()->create([
            'transport_trip_id' => $trip->id,
            'location_id' => $location->id,
            'stop_sequence' => $sequence,
            'arrival_time' => null,
            'departure_time' => null,
            'route_segment_id' => null,
        ])->load('location');
    }

    /**
     * @return array{0: TripDto, 1: TransportTrip, 2: TransportTripStop[]}
     */
    private function makeTransitousScenario(): array
    {
        $trip = TransportTrip::factory()->create([
            'foreign_trip_id' => 'test-trip-1',
            'mode' => TransportMode::RAIL->value,
        ]);

        $stopA = $this->makeStop($trip, 0, 0.0, 0.0);
        $stopB = $this->makeStop($trip, 1, 0.0, 1.0);
        $stopC = $this->makeStop($trip, 2, 0.0, 2.0);

        $transcoder = new MagellanPolylineTranscoder;
        $trackPoints = [
            Point::makeGeodetic(0.0, 0.0),
            Point::makeGeodetic(0.0, 0.5),
            Point::makeGeodetic(0.0, 1.0),
            Point::makeGeodetic(0.0, 1.5),
            Point::makeGeodetic(0.0, 2.0),
        ];
        $encoded = $transcoder->encodePolyline($trackPoints, 6);

        $legGeometry = new LegGeometryDto()->setPoints($encoded)->setPrecision(6);
        $leg = new LegDto()
            ->setMode(TransportMode::RAIL->value)
            ->setTripId('test-trip-1')
            ->setLegGeometry($legGeometry);

        $tripDto = new TripDto()->setLegs([$leg]);

        return [$tripDto, $trip, [$stopA, $stopB, $stopC]];
    }

    public function test_creates_transitous_segments_split_per_stop_pair(): void
    {
        [$tripDto, , $stops] = $this->makeTransitousScenario();
        $brouter = Mockery::mock(BrouterRequestService::class);
        $brouter->shouldNotReceive('getRoute');

        $this->makeController($brouter)->rerouteStops($tripDto, $stops);

        $stopA = $stops[0]->fresh();
        $stopB = $stops[1]->fresh();

        $this->assertNotNull($stopA->route_segment_id);
        $this->assertNotNull($stopB->route_segment_id);
        $this->assertNotEquals($stopA->route_segment_id, $stopB->route_segment_id);

        $segment1 = RouteSegment::find($stopA->route_segment_id);
        $segment2 = RouteSegment::find($stopB->route_segment_id);

        $this->assertEquals(RouteSegmentSource::TRANSITOUS, $segment1->source);
        $this->assertEquals(RouteSegmentSource::TRANSITOUS, $segment2->source);
        $this->assertGreaterThanOrEqual(2, count($segment1->geometry->getPoints()));
        $this->assertGreaterThanOrEqual(2, count($segment2->geometry->getPoints()));
    }

    public function test_prefers_existing_transitous_segment_over_new_one(): void
    {
        [$tripDto, , $stops] = $this->makeTransitousScenario();
        [$stopA, $stopB] = $stops;

        $existing = (new TransportTripRepository)->createRouteSegment(
            $stopA->location,
            $stopB->location,
            null,
            'rail',
            LineString::make([Point::makeGeodetic(0.0, 0.0), Point::makeGeodetic(0.0, 1.0)]),
            false,
            RouteSegmentSource::TRANSITOUS
        );

        $brouter = Mockery::mock(BrouterRequestService::class);
        $brouter->shouldNotReceive('getRoute');

        $this->makeController($brouter)->rerouteStops($tripDto, $stops);

        $stopA = $stopA->fresh();
        $this->assertEquals($existing->id, $stopA->route_segment_id);

        $existing->refresh();
        $this->assertEquals(RouteSegmentSource::TRANSITOUS, $existing->source);
        $this->assertCount(2, $existing->geometry->getPoints());
    }

    public function test_upgrades_existing_brouter_segment_with_transitous_geometry(): void
    {
        [$tripDto, , $stops] = $this->makeTransitousScenario();
        [$stopA, $stopB] = $stops;

        $existing = (new TransportTripRepository)->createRouteSegment(
            $stopA->location,
            $stopB->location,
            null,
            'rail',
            LineString::make([Point::makeGeodetic(0.0, 0.0), Point::makeGeodetic(0.0, 1.0)]),
            false,
            RouteSegmentSource::BROUTER
        );

        $brouter = Mockery::mock(BrouterRequestService::class);
        $brouter->shouldNotReceive('getRoute');

        $this->makeController($brouter)->rerouteStops($tripDto, $stops);

        $stopA = $stopA->fresh();
        $this->assertEquals($existing->id, $stopA->route_segment_id);

        $existing->refresh();
        $this->assertEquals(RouteSegmentSource::TRANSITOUS, $existing->source);
        $this->assertGreaterThan(2, count($existing->geometry->getPoints()));
    }

    public function test_falls_back_to_brouter_when_leg_has_no_geometry(): void
    {
        $trip = TransportTrip::factory()->create([
            'foreign_trip_id' => 'test-trip-2',
            'mode' => TransportMode::RAIL->value,
        ]);
        $stopA = $this->makeStop($trip, 0, 0.0, 0.0);
        $stopB = $this->makeStop($trip, 1, 0.0, 1.0);

        $leg = new LegDto()
            ->setMode(TransportMode::RAIL->value)
            ->setTripId('test-trip-2');
        $tripDto = new TripDto()->setLegs([$leg]);

        $brouter = Mockery::mock(BrouterRequestService::class);
        $brouter->shouldReceive('getRoute')->once()->andReturn(json_encode([
            'type' => 'LineString',
            'coordinates' => [[0.0, 0.0], [1.0, 0.0]],
        ]));

        $this->makeController($brouter)->rerouteStops($tripDto, [$stopA, $stopB]);

        $stopA = $stopA->fresh();
        $this->assertNotNull($stopA->route_segment_id);
        $segment = RouteSegment::find($stopA->route_segment_id);
        $this->assertEquals(RouteSegmentSource::BROUTER, $segment->source);
    }

    public function test_local_transport_trip_still_uses_brouter(): void
    {
        $trip = TransportTrip::factory()->create([
            'mode' => TransportMode::RAIL->value,
        ]);
        $stopA = $this->makeStop($trip, 0, 0.0, 0.0);
        $stopB = $this->makeStop($trip, 1, 0.0, 1.0);

        $brouter = Mockery::mock(BrouterRequestService::class);
        $brouter->shouldReceive('getRoute')->once()->andReturn(json_encode([
            'type' => 'LineString',
            'coordinates' => [[0.0, 0.0], [1.0, 0.0]],
        ]));

        $this->makeController($brouter)->rerouteStops($trip, [$stopA, $stopB]);

        $stopA = $stopA->fresh();
        $this->assertNotNull($stopA->route_segment_id);
        $segment = RouteSegment::find($stopA->route_segment_id);
        $this->assertEquals(RouteSegmentSource::BROUTER, $segment->source);
    }
}
