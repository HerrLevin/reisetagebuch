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
use Opis\JsonSchema\Validator;
use Tests\TestCase;

/**
 * Validates the resources/schemas/activitypub/rtb-extension.v1.schema.json JSON Schema
 * against both real Rtb* DTO output and deliberately malformed payloads, so the schema
 * is kept in sync with App\Dto\ActivityPub\Extensions\RtbExtensionFactory and friends
 * instead of silently drifting from the actual wire format.
 */
class RtbExtensionSchemaTest extends TestCase
{
    private function schema(): object
    {
        $path = base_path('resources/schemas/activitypub/rtb-extension.v1.schema.json');

        return json_decode(file_get_contents($path));
    }

    private function validate(array $data): bool
    {
        $payload = json_decode(json_encode($data));

        return (new Validator)->validate($payload, $this->schema())->isValid();
    }

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

    private function locationExtension(): RtbLocationExtension
    {
        return new RtbLocationExtension(
            rtbVersion: RtbExtensionFactory::CURRENT_VERSION,
            location: $this->locationData(),
            travelReason: TravelReason::LEISURE,
            visitedAt: '2026-01-01T10:00:00+00:00',
        );
    }

    private function transportExtension(): RtbTransportExtension
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

        return new RtbTransportExtension(
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
    }

    public function test_schema_file_is_valid_json(): void
    {
        $path = base_path('resources/schemas/activitypub/rtb-extension.v1.schema.json');

        json_decode(file_get_contents($path), flags: JSON_THROW_ON_ERROR);

        $this->addToAssertionCount(1);
    }

    public function test_location_extension_output_is_valid(): void
    {
        $this->assertTrue($this->validate($this->locationExtension()->toArray()));
    }

    public function test_location_extension_output_is_valid_with_null_optional_fields(): void
    {
        $extension = new RtbLocationExtension(
            rtbVersion: RtbExtensionFactory::CURRENT_VERSION,
            location: $this->locationData(),
            travelReason: null,
            visitedAt: null,
        );

        $this->assertTrue($this->validate($extension->toArray()));
    }

    public function test_transport_extension_output_is_valid(): void
    {
        $this->assertTrue($this->validate($this->transportExtension()->toArray()));
    }

    public function test_transport_extension_output_is_valid_with_null_user_geometry(): void
    {
        $stop = new RtbStopData(
            id: 'stop-1',
            name: 'Berlin Hbf',
            location: $this->locationData(),
            arrivalTime: null,
            departureTime: null,
            arrivalDelay: null,
            departureDelay: null,
        );

        $extension = new RtbTransportExtension(
            rtbVersion: RtbExtensionFactory::CURRENT_VERSION,
            originStop: $stop,
            destinationStop: $stop,
            trip: new RtbTripData(
                id: 'trip-1',
                foreignId: null,
                foreignIdSourceUrl: null,
                foreignIdSourceFormat: null,
                mode: TransportMode::OTHER,
                lineName: null,
                routeLongName: null,
                tripShortName: null,
                displayName: null,
                routeColor: null,
                routeTextColor: null,
            ),
            manualDepartureTime: null,
            manualArrivalTime: null,
            travelReason: null,
            distance: 0,
            duration: 0,
            userGeometry: null,
        );

        $this->assertTrue($this->validate($extension->toArray()));
    }

    public function test_rejects_unsupported_rtb_version(): void
    {
        $data = $this->locationExtension()->toArray();
        $data['rtbVersion'] = 2;

        $this->assertFalse($this->validate($data));
    }

    public function test_rejects_unknown_post_type(): void
    {
        $data = $this->locationExtension()->toArray();
        $data['postType'] = 'something-else';

        $this->assertFalse($this->validate($data));
    }

    public function test_rejects_mismatched_body_for_post_type(): void
    {
        $data = $this->locationExtension()->toArray();
        unset($data['location']);
        $data['transport'] = $this->transportExtension()->toArray()['transport'];

        $this->assertFalse($this->validate($data));
    }

    public function test_rejects_location_missing_required_field(): void
    {
        $data = $this->locationExtension()->toArray();
        unset($data['location']['latitude']);

        $this->assertFalse($this->validate($data));
    }

    public function test_rejects_location_with_out_of_range_coordinates(): void
    {
        $data = $this->locationExtension()->toArray();
        $data['location']['latitude'] = 200.0;

        $this->assertFalse($this->validate($data));
    }

    public function test_rejects_unknown_travel_reason(): void
    {
        $data = $this->locationExtension()->toArray();
        $data['location']['travelReason'] = 'not-a-real-reason';

        $this->assertFalse($this->validate($data));
    }

    public function test_rejects_unknown_transport_mode(): void
    {
        $data = $this->transportExtension()->toArray();
        $data['transport']['trip']['mode'] = 'TELEPORT';

        $this->assertFalse($this->validate($data));
    }

    public function test_rejects_unexpected_additional_property(): void
    {
        $data = $this->locationExtension()->toArray();
        $data['unexpected'] = 'nope';

        $this->assertFalse($this->validate($data));
    }
}
