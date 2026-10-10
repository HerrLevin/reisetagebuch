<?php

namespace Tests\Unit\Dto\ActivityPub\Extensions;

use App\Dto\ActivityPub\Extensions\RtbExtensionFactory;
use App\Dto\ActivityPub\Extensions\RtbIdentifierData;
use App\Dto\ActivityPub\Extensions\RtbLocationData;
use App\Dto\ActivityPub\Extensions\RtbLocationExtension;
use App\Dto\ActivityPub\Extensions\RtbStopData;
use App\Dto\ActivityPub\Extensions\RtbTagData;
use App\Dto\ActivityPub\Extensions\RtbTransportExtension;
use App\Dto\ActivityPub\Extensions\RtbTripData;
use App\Enums\PostMetaInfo\TravelReason;
use App\Enums\TransportMode;
use Tests\TestCase;

class RtbExtensionTest extends TestCase
{
    private function locationData(): RtbLocationData
    {
        return new RtbLocationData(
            id: 'loc-1',
            name: 'Berlin Hbf',
            latitude: 52.52,
            longitude: 13.405,
            timezone: 'Europe/Berlin',
            emoji: '🚉',
            tags: [new RtbTagData('addr:city', 'Berlin')],
            identifiers: [new RtbIdentifierData('stop', 'motis', 'de:11000:900003201')],
        );
    }

    public function test_rtb_location_data_toarray_and_fromarray_round_trip(): void
    {
        $data = $this->locationData();

        $reconstructed = RtbLocationData::fromArray($data->toArray());

        $this->assertEquals($data, $reconstructed);
    }

    public function test_rtb_location_extension_toarray_and_fromarray_round_trip(): void
    {
        $extension = new RtbLocationExtension(
            rtbVersion: RtbExtensionFactory::CURRENT_VERSION,
            location: $this->locationData(),
            travelReason: TravelReason::LEISURE,
            visitedAt: '2026-01-01T10:00:00+00:00',
        );

        $reconstructed = RtbLocationExtension::fromArray($extension->toArray());

        $this->assertEquals($extension, $reconstructed);
    }

    public function test_rtb_transport_extension_toarray_and_fromarray_round_trip(): void
    {
        $stop = new RtbStopData(
            id: 'stop-1',
            name: 'Berlin Hbf',
            location: $this->locationData(),
            arrivalTime: null,
            departureTime: '2026-01-01T10:00:00+00:00',
            arrivalDelay: null,
            departureDelay: 0,
        );

        $extension = new RtbTransportExtension(
            rtbVersion: RtbExtensionFactory::CURRENT_VERSION,
            originStop: $stop,
            destinationStop: $stop,
            trip: new RtbTripData(
                id: 'trip-1',
                foreignId: 'foreign-1',
                foreignIdSourceUrl: 'https://api.transitous.org/api',
                foreignIdSourceFormat: 'motis',
                mode: TransportMode::RAIL,
                lineName: 'RE1',
                routeLongName: null,
                tripShortName: null,
                displayName: 'RE1',
                routeColor: null,
                routeTextColor: null,
            ),
            manualDepartureTime: null,
            manualArrivalTime: null,
            travelReason: TravelReason::COMMUTE,
            distance: 12345,
            duration: 600,
            userGeometry: ['type' => 'LineString', 'coordinates' => [[13.4, 52.5]]],
        );

        $reconstructed = RtbTransportExtension::fromArray($extension->toArray());

        $this->assertEquals($extension, $reconstructed);
    }

    public function test_factory_dispatches_by_post_type(): void
    {
        $location = new RtbLocationExtension(1, $this->locationData(), null, null);

        $this->assertInstanceOf(RtbLocationExtension::class, RtbExtensionFactory::fromArray($location->toArray()));
    }

    public function test_factory_rejects_unsupported_version(): void
    {
        $data = new RtbLocationExtension(1, $this->locationData(), null, null)->toArray();
        $data['rtbVersion'] = 2;

        $this->assertNull(RtbExtensionFactory::fromArray($data));
    }

    public function test_factory_rejects_non_array_input(): void
    {
        $this->assertNull(RtbExtensionFactory::fromArray('not-an-array'));
        $this->assertNull(RtbExtensionFactory::fromArray(null));
    }

    public function test_location_extension_body_is_sanitized_against_script_injection(): void
    {
        $data = new RtbLocationExtension(1, $this->locationData(), null, null)->toArray();
        $data['body'] = '<script>alert(1)</script>Hello <b>world</b>';

        $extension = RtbLocationExtension::fromArray($data);

        $this->assertStringNotContainsString('<script>', $extension->body);
        $this->assertStringContainsString('Hello', $extension->body);
    }

    public function test_transport_extension_body_is_sanitized_against_script_injection(): void
    {
        $data = new RtbTransportExtension(
            rtbVersion: 1,
            originStop: new RtbStopData('stop-1', 'Berlin Hbf', $this->locationData(), null, null, null, null),
            destinationStop: new RtbStopData('stop-2', 'Hamburg Hbf', $this->locationData(), null, null, null, null),
            trip: new RtbTripData('trip-1', null, null, null, TransportMode::RAIL, 'RE1', null, null, 'RE1', null, null),
            manualDepartureTime: null,
            manualArrivalTime: null,
            travelReason: null,
            distance: 0,
            duration: 0,
            userGeometry: null,
        )->toArray();
        $data['body'] = '<img src=x onerror=alert(1)>Trip notes';

        $extension = RtbTransportExtension::fromArray($data);

        $this->assertStringNotContainsString('onerror', $extension->body);
        $this->assertStringContainsString('Trip notes', $extension->body);
    }
}
