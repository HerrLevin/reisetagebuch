<?php

namespace Tests\Unit\Hydrators;

use App\Dto\MotisApi\LegDto;
use App\Hydrators\MotisHydrator;
use PHPUnit\Framework\TestCase;

class MotisHydratorTest extends TestCase
{
    public function test_hydrate_stop()
    {
        $data = [
            'stopId' => '123',
            'name' => 'Test Stop',
            'lat' => 12.345678,
            'lon' => 98.765432,
        ];

        $hydrator = new MotisHydrator;
        $stopDto = $hydrator->hydrateStop($data);

        $this->assertEquals('123', $stopDto->stopId);
        $this->assertEquals('Test Stop', $stopDto->name);
        $this->assertEquals(12.345678, $stopDto->latitude);
        $this->assertEquals(98.765432, $stopDto->longitude);
    }

    public function test_hydrate_stop_time()
    {
        $data = [
            'place' => [
                'name' => 'Test Place',
                'stopId' => '123',
                'lat' => 12.345678,
                'lon' => 98.765432,
            ],
            'mode' => 'bus',
            'realTime' => true,
            'headsign' => 'Test HeadSign',
            'agencyName' => 'Test Agency',
            'agencyId' => '456',
            'tripId' => '789',
            'routeShortName' => 'Test Route',
            'source' => 'Test Source',
        ];

        $hydrator = new MotisHydrator;
        $stopTimeDto = $hydrator->hydrateStopTime($data);

        $this->assertEquals('Test Place', $stopTimeDto->place->name);
        $this->assertEquals('123', $stopTimeDto->place->stopId);
        $this->assertEquals(12.345678, $stopTimeDto->place->latitude);
        $this->assertEquals(98.765432, $stopTimeDto->place->longitude);
        $this->assertEquals('bus', $stopTimeDto->mode);
        $this->assertTrue($stopTimeDto->realTime);
        $this->assertEquals('Test HeadSign', $stopTimeDto->headSign);
        $this->assertEquals('Test Agency', $stopTimeDto->agencyName);
        $this->assertEquals('456', $stopTimeDto->agencyId);
        $this->assertEquals('789', $stopTimeDto->tripId);
        $this->assertEquals('Test Route', $stopTimeDto->routeShortName);
        $this->assertEquals('Test Source', $stopTimeDto->source);
    }

    private function legData(): array
    {
        return [
            'duration' => 3600,
            'source' => 'Test Source',
            'from' => [
                'name' => 'Test From',
                'stopId' => '123',
                'lat' => 12.345678,
                'lon' => 98.765432,
            ],
            'to' => [
                'name' => 'Test To',
                'stopId' => '456',
                'lat' => 23.456789,
                'lon' => 87.654321,
            ],
            'mode' => 'bus',
            'realTime' => true,
            'headsign' => 'Test HeadSign',
            'agencyName' => 'Test Agency',
            'agencyId' => '789',
            'tripId' => '012',
            'routeShortName' => 'Test Route',
            'startTime' => '2023-10-01T12:00:00Z',
            'endTime' => '2023-10-01T13:00:00Z',
            'scheduledStartTime' => '2023-10-01T11:59:00Z',
            'scheduledEndTime' => '2023-10-01T12:59:00Z',
            'intermediateStops' => [
                [
                    'name' => 'Intermediate Stop 1',
                    'stopId' => '789',
                    'lat' => 34.567890,
                    'lon' => 76.543210,
                ],
                [
                    'name' => 'Intermediate Stop 2',
                    'stopId' => '012',
                    'lat' => 45.678901,
                    'lon' => 65.432109,
                ],
            ],
        ];
    }

    public function test_hydrate_leg()
    {
        $data = $this->legData();
        $hydrator = new MotisHydrator;
        $legDto = $hydrator->hydrateLeg($data);

        $this->legAsserts($legDto);
    }

    public function test_hydrate_trip()
    {
        $leg = $this->legData();
        $data = [
            'duration' => 7200,
            'startTime' => '2023-10-01T12:00:00Z',
            'endTime' => '2023-10-01T14:00:00Z',
            'transfers' => 0,
            'legs' => [$leg],
        ];

        $hydrator = new MotisHydrator;
        $tripDto = $hydrator->hydrateTrip($data);

        $this->assertEquals(7200, $tripDto->duration);
        $this->assertEquals('2023-10-01T12:00:00Z', $tripDto->startTime->toIso8601ZuluString());
        $this->assertEquals('2023-10-01T14:00:00Z', $tripDto->endTime->toIso8601ZuluString());
        $this->assertEquals(0, $tripDto->transfers);
        $this->assertCount(1, $tripDto->legs);
        $this->assertInstanceOf(LegDto::class, $tripDto->legs[0]);

        $this->legAsserts($tripDto->legs[0]);
    }

    private function legAsserts(LegDto $legDto)
    {

        $this->assertEquals(3600, $legDto->duration);
        $this->assertEquals('Test Source', $legDto->source);
        $this->assertEquals('Test From', $legDto->from->name);
        $this->assertEquals('123', $legDto->from->stopId);
        $this->assertEquals(12.345678, $legDto->from->latitude);
        $this->assertEquals(98.765432, $legDto->from->longitude);
        $this->assertEquals('Test To', $legDto->to->name);
        $this->assertEquals('456', $legDto->to->stopId);
        $this->assertEquals(23.456789, $legDto->to->latitude);
        $this->assertEquals(87.654321, $legDto->to->longitude);
        $this->assertEquals('bus', $legDto->mode);
        $this->assertTrue($legDto->realTime);
        $this->assertEquals('Test HeadSign', $legDto->headSign);
        $this->assertEquals('Test Agency', $legDto->agencyName);
        $this->assertEquals('789', $legDto->agencyId);
        $this->assertEquals('012', $legDto->tripId);
        $this->assertEquals('Test Route', $legDto->routeShortName);
        $this->assertEquals('2023-10-01T12:00:00Z', $legDto->startTime->toIso8601ZuluString());
        $this->assertEquals('2023-10-01T13:00:00Z', $legDto->endTime->toIso8601ZuluString());
        $this->assertEquals('2023-10-01T11:59:00Z', $legDto->scheduledStartTime->toIso8601ZuluString());
        $this->assertEquals('2023-10-01T12:59:00Z', $legDto->scheduledEndTime->toIso8601ZuluString());
        $this->assertCount(2, $legDto->intermediateStops);
        $this->assertEquals('Intermediate Stop 1', $legDto->intermediateStops[0]->name);
        $this->assertEquals('789', $legDto->intermediateStops[0]->stopId);
        $this->assertEquals(34.567890, $legDto->intermediateStops[0]->latitude);
        $this->assertEquals(76.543210, $legDto->intermediateStops[0]->longitude);
        $this->assertEquals('Intermediate Stop 2', $legDto->intermediateStops[1]->name);
        $this->assertEquals('012', $legDto->intermediateStops[1]->stopId);
        $this->assertEquals(45.678901, $legDto->intermediateStops[1]->latitude);
        $this->assertEquals(65.432109, $legDto->intermediateStops[1]->longitude);

    }
}
