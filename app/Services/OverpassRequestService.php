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
    private const array FILTERS = [
        'place' => ['village'],
        'admin_level' => ['2','4','8', '9', '10', '11'],
        'amenity' => [
            'cafe',
            'bank',
            'pub',
            'bar',
            'biergarten',
            'restaurant',
            'fast_food',
            'food_court',
            'ice_cream',
            'pharmacy',
            'doctors',
            'clinic',
            'library',
            'toilets',
            'fountain',
            'lounge',
            'college',
            'dancing_school',
            'driving_school',
            'first_aid_school',
            'kindergarten',
            'language_school',
            'parcel_locker',
        ],
        'historic',
        'tourism',
        'office',
        'shop',
        'parking',
        'building' => [
            'office',
        ],
        'landuse' => [
            'events'
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
        $this->client = new Client();
    }

    private function getQuery(): string
    {
        $query = "[out:json][timeout:25];(";
        foreach (static::FILTERS as $key => $filter) {
            if (is_array($filter)) {
                $filters = implode('|', $filter);
                $query .= sprintf(
                    'nwr(around:%d,%f,%f)["%s"~"%s"]["name"];',
                    $this->radius,
                    $this->latitude,
                    $this->longitude,
                    $key,
                    $filters
                );
            } else {
                $query .= "nwr(around:$this->radius,$this->latitude,$this->longitude)[\"$filter\"][\"name\"];";
            }
        }

        $query .= ");out center;";
        return $query;
    }

    private function getElements(): array
    {
        $query = $this->getQuery();

        $url = "https://overpass-api.de/api/interpreter?data=" . urlencode($query);
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
            if (in_array($element['type'], ['node', 'way', 'relation']) && isset($element['tags']['name'])) {
                yield new OverpassLocation(
                    osmId: $element['id'],
                    name: $element['tags']['name'] ?? '',
                    latitude: $element['lat'] ?? $element['center']['lat'] ?? 0,
                    longitude: $element['lon'] ?? $element['center']['lon'] ?? 0,
                    osmType: $element['type'],
                    tags: $element['tags']
                );
            }
        }
    }
}
