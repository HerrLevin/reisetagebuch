<?php

namespace Tests\Unit\Services;

use App\Dto\ActivityPub\Extensions\RtbLocationExtension;
use App\Dto\ActivityPub\Extensions\RtbTransportExtension;
use App\Enums\PostMetaInfo\TravelReason;
use App\Enums\TransportMode;
use App\Services\ActivityPubExtensionParser;
use Tests\TestCase;

class ActivityPubExtensionParserTest extends TestCase
{
    private function parser(): ActivityPubExtensionParser
    {
        return new ActivityPubExtensionParser;
    }

    private function validLocationExtension(): array
    {
        return [
            'rtbExtension' => [
                'rtbVersion' => 1,
                'postType' => 'location',
                'body' => 'My own words about this stop',
                'location' => [
                    'id' => 'loc-1',
                    'name' => 'Berlin Hbf',
                    'latitude' => 52.52,
                    'longitude' => 13.405,
                    'timezone' => 'Europe/Berlin',
                    'emoji' => '🚉',
                    'tags' => [['key' => 'addr:city', 'value' => 'Berlin']],
                    'identifiers' => [['type' => 'stop', 'origin' => 'motis', 'identifier' => 'de:11000:900003201']],
                    'travelReason' => 'leisure',
                    'visitedAt' => '2026-01-01T10:00:00+00:00',
                ],
            ],
        ];
    }

    private function validTransportExtension(): array
    {
        $stop = [
            'id' => 'stop-1',
            'name' => 'Berlin Hbf',
            'location' => [
                'id' => 'loc-1',
                'name' => 'Berlin Hbf',
                'latitude' => 52.52,
                'longitude' => 13.405,
                'timezone' => 'Europe/Berlin',
                'emoji' => '🚉',
                'tags' => [],
                'identifiers' => [],
            ],
            'arrivalTime' => null,
            'departureTime' => '2026-01-01T10:00:00+00:00',
            'arrivalDelay' => null,
            'departureDelay' => 0,
        ];

        return [
            'rtbExtension' => [
                'rtbVersion' => 1,
                'postType' => 'transport',
                'transport' => [
                    'originStop' => $stop,
                    'destinationStop' => $stop,
                    'trip' => [
                        'id' => 'trip-1',
                        'foreignId' => 'foreign-1',
                        'mode' => 'RAIL',
                        'lineName' => 'RE1',
                        'routeLongName' => null,
                        'tripShortName' => null,
                        'displayName' => 'RE1',
                        'routeColor' => null,
                        'routeTextColor' => null,
                    ],
                    'manualDepartureTime' => null,
                    'manualArrivalTime' => null,
                    'travelReason' => 'commute',
                    'distance' => 12345,
                    'duration' => 600,
                    'userGeometry' => ['type' => 'LineString', 'coordinates' => [[13.4, 52.5], [13.5, 52.6]]],
                ],
            ],
        ];
    }

    public function test_parses_a_valid_location_extension(): void
    {
        $result = $this->parser()->parse($this->validLocationExtension());

        $this->assertInstanceOf(RtbLocationExtension::class, $result);
        $this->assertSame('Berlin Hbf', $result->location->name);
        $this->assertSame(52.52, $result->location->latitude);
        $this->assertSame(TravelReason::LEISURE, $result->travelReason);
    }

    public function test_parses_a_valid_transport_extension(): void
    {
        $result = $this->parser()->parse($this->validTransportExtension());

        $this->assertInstanceOf(RtbTransportExtension::class, $result);
        $this->assertSame('trip-1', $result->trip->id);
        $this->assertSame(TransportMode::RAIL, $result->trip->mode);
        $this->assertSame(12345, $result->distance);
        $this->assertNotNull($result->userGeometry);
    }

    public function test_parsed_location_extension_casts_back_to_the_original_wire_shape(): void
    {
        $extension = $this->validLocationExtension();

        $result = $this->parser()->parse($extension);

        $this->assertSame($extension['rtbExtension'], $result->toArray());
    }

    public function test_missing_extension_key_returns_null(): void
    {
        $this->assertNull($this->parser()->parse(['content' => 'plain mastodon note']));
    }

    public function test_non_array_extension_returns_null(): void
    {
        $this->assertNull($this->parser()->parse(['rtbExtension' => 'not-an-array']));
    }

    public function test_unknown_version_returns_null(): void
    {
        $extension = $this->validLocationExtension();
        $extension['rtbExtension']['rtbVersion'] = 99;

        $this->assertNull($this->parser()->parse($extension));
    }

    public function test_missing_version_returns_null(): void
    {
        $extension = $this->validLocationExtension();
        unset($extension['rtbExtension']['rtbVersion']);

        $this->assertNull($this->parser()->parse($extension));
    }

    public function test_unknown_post_type_returns_null(): void
    {
        $extension = $this->validLocationExtension();
        $extension['rtbExtension']['postType'] = 'poll';

        $this->assertNull($this->parser()->parse($extension));
    }

    public function test_out_of_range_coordinates_reject_location(): void
    {
        $extension = $this->validLocationExtension();
        $extension['rtbExtension']['location']['latitude'] = 999;

        $this->assertNull($this->parser()->parse($extension));
    }

    public function test_missing_coordinates_reject_location(): void
    {
        $extension = $this->validLocationExtension();
        unset($extension['rtbExtension']['location']['latitude']);

        $this->assertNull($this->parser()->parse($extension));
    }

    public function test_invalid_enum_values_are_nulled_not_rejected(): void
    {
        $extension = $this->validLocationExtension();
        $extension['rtbExtension']['location']['travelReason'] = 'not-a-real-reason';

        $result = $this->parser()->parse($extension);

        $this->assertInstanceOf(RtbLocationExtension::class, $result);
        $this->assertNull($result->travelReason);
    }

    public function test_malformed_tags_are_dropped_individually(): void
    {
        $extension = $this->validLocationExtension();
        $extension['rtbExtension']['location']['tags'] = [
            ['key' => 'addr:city', 'value' => 'Berlin'],
            ['key' => 'missing-value'],
            'not-even-an-array',
        ];

        $result = $this->parser()->parse($extension);

        $this->assertInstanceOf(RtbLocationExtension::class, $result);
        $this->assertCount(1, $result->location->tags);
    }

    public function test_oversized_geometry_is_dropped_but_transport_still_parses(): void
    {
        $extension = $this->validTransportExtension();
        $extension['rtbExtension']['transport']['userGeometry'] = [
            'type' => 'LineString',
            'coordinates' => array_fill(0, 50_000, [13.4, 52.5]),
        ];

        $result = $this->parser()->parse($extension);

        $this->assertInstanceOf(RtbTransportExtension::class, $result);
        $this->assertNull($result->userGeometry);
    }

    public function test_missing_origin_stop_rejects_whole_transport_payload(): void
    {
        $extension = $this->validTransportExtension();
        unset($extension['rtbExtension']['transport']['originStop']);

        $this->assertNull($this->parser()->parse($extension));
    }

    public function test_deeply_malformed_input_never_throws(): void
    {
        $result = $this->parser()->parse([
            'rtbExtension' => [
                'rtbVersion' => 1,
                'postType' => 'transport',
                'transport' => 'this should be an array, not a string',
            ],
        ]);

        $this->assertNull($result);
    }

    public function test_adversarial_nested_types_never_throw(): void
    {
        $result = $this->parser()->parse([
            'rtbExtension' => [
                'rtbVersion' => 1,
                'postType' => 'location',
                'location' => [
                    'latitude' => 52.52,
                    'longitude' => 13.405,
                    'tags' => 'not-an-array',
                    'identifiers' => ['this-is-a-string-not-an-array-of-arrays'],
                    'name' => ['nested' => 'array-instead-of-string'],
                ],
            ],
        ]);

        $this->assertInstanceOf(RtbLocationExtension::class, $result);
        $this->assertSame([], $result->location->tags);
        $this->assertSame([], $result->location->identifiers);
    }
}
