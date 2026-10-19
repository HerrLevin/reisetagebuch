<?php

declare(strict_types=1);

namespace App\Services\Osm;

/**
 * Single source of truth for which OSM tags are worth keeping as a Location.
 * Shared by the live Overpass radius query (OverpassRadiusRequestService) and
 * the offline Geofabrik importer, so the two paths can never drift apart.
 */
class OsmTagFilter
{
    public const array EXCLUDE = [
        'amenity' => [
            'waste_basket',
            'bicycle_parking',
            'bench',
            'lounger',
            'trolley_bay',
            'vending_machine',
            'clock',
            'telephone',
            'parking_entrance',
            'loading_dock',
            'recycling',
            'parking_space',
        ],
    ];

    public const array FILTERS_FORCE_NAME = [
        'leisure',
        'natural',
        'bridge',
        'building' => [
            'office',
        ],
        'parking',
        'admin_level' => ['2', '4', '8', '9', '10', '11'],
    ];

    public const array FILTERS = [
        'amenity',
        'place' => ['village'],
        'historic',
        'tourism',
        'office',
        'shop',
        'landuse' => [
            'events',
        ],
        'railway' => [
            'station',
            'tram_stop',
        ],
        'public_transport' => [
            'platform',
            'stop_area',
        ],
        'highway' => [
            'bus_stop',
        ],
    ];

    /**
     * Whether an element with these tags would be kept by the Overpass radius
     * query / should be imported from a Geofabrik extract.
     */
    public static function matches(array $tags): bool
    {
        if (self::matchesSet(self::FILTERS, $tags)) {
            return true;
        }

        return isset($tags['name']) && self::matchesSet(self::FILTERS_FORCE_NAME, $tags);
    }

    private static function matchesSet(array $filters, array $tags): bool
    {
        foreach ($filters as $key => $filter) {
            if (is_int($key)) {
                $key = $filter;
                $filter = null;
            }

            if (! isset($tags[$key])) {
                continue;
            }

            if ($filter !== null && ! in_array($tags[$key], $filter, true)) {
                continue;
            }

            if (in_array($tags[$key], self::EXCLUDE[$key] ?? [], true)) {
                continue;
            }

            return true;
        }

        return false;
    }

    /**
     * All tag keys that can trigger a match, for building an overinclusive
     * `osmium tags-filter` expression (the exact include/exclude/force-name
     * logic above is then re-applied in PHP on the filtered extract).
     */
    public static function allKeys(): array
    {
        $keys = [];
        foreach ([self::FILTERS, self::FILTERS_FORCE_NAME] as $set) {
            foreach ($set as $key => $filter) {
                $keys[] = is_int($key) ? $filter : $key;
            }
        }

        return array_values(array_unique($keys));
    }
}
