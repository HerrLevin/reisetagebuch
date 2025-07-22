<?php

namespace App\Services;

use App\Dto\OverpassLocation;
use Generator;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class OverpassRequestService
{
    private float $latitude;

    private float $longitude;

    private int $radius;

    private Client $client;

    private const array EXCLUDE = [
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

    private const array FILTERS = [
        'amenity',
        'place' => ['village'],
        'admin_level' => ['2', '4', '8', '9', '10', '11'],
        'historic',
        'tourism',
        'office',
        'shop',
        'parking',
        'building' => [
            'office',
        ],
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

    public function __construct(float $latitude, float $longitude, int $radius = 200)
    {
        $this->latitude = $latitude;
        $this->longitude = $longitude;
        $this->radius = $radius;
        $this->client = new Client;
    }

    private function getExcludes(string $key): string
    {
        if (! isset(static::EXCLUDE[$key])) {
            return '';
        }
        $excludes = static::EXCLUDE[$key] ?? [];

        $string = '';
        foreach ($excludes as $exclude) {
            $string .= sprintf('["%s"!~"%s"]', $key, $exclude);
        }

        return $string;
    }

    private function getQuery(): string
    {
        $query = '[out:json][timeout:25];(';
        foreach (static::FILTERS as $key => $filter) {
            if (is_array($filter)) {
                $filters = implode('|', $filter);
                $excludes = $this->getExcludes($key);
                $query .= sprintf(
                    'nwr(around:%d,%f,%f)["%s"~"%s"]%s;',
                    $this->radius,
                    $this->latitude,
                    $this->longitude,
                    $key,
                    $filters,
                    $excludes
                );
            } else {
                $excludes = $this->getExcludes($filter);
                $query .= sprintf(
                    'nwr(around:%d,%f,%f)["%s"]%s;',
                    $this->radius,
                    $this->latitude,
                    $this->longitude,
                    $filter,
                    $excludes
                );
            }
        }

        $query .= ');out center;';

        return $query;
    }

    private function getElements(): array
    {
        $query = $this->getQuery();

        $url = 'https://overpass-api.de/api/interpreter?data='.urlencode($query);
        try {
            $response = $this->client->get($url);
        } catch (GuzzleException) {
            return [];
        }
        $response = $response->getBody()->getContents();

        return json_decode($response, true);
    }

    /**
     * @return Generator<OverpassLocation>
     */
    public function getLocations(): Generator
    {
        $response = $this->getElements();

        foreach ($response['elements'] as $element) {
            if (in_array($element['type'], ['node', 'way', 'relation'])) {
                yield new OverpassLocation(
                    osmId: $element['id'],
                    latitude: $element['lat'] ?? $element['center']['lat'] ?? 0,
                    longitude: $element['lon'] ?? $element['center']['lon'] ?? 0,
                    osmType: $element['type'],
                    tags: $element['tags']
                );
            }
        }
    }
}
