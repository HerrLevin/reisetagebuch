<?php

namespace App\Services;

use App\Dto\OverpassLocation;
use App\Exceptions\OverpassApiOverloaded;
use Clickbar\Magellan\Data\Geometries\Point;
use Generator;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class OverpassRequestService
{
    private Point $point;

    private int $radius;

    private Client $client;

    private VersionService $versionService;

    private const string INTERPRETER_URL = 'https://overpass-api.de/api/interpreter?data=';

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

    private const array FILTERS_FORCE_NAME = [
        'leisure',
        'natural',
        'bridge',
        'building' => [
            'office',
        ],
        'parking',
        'admin_level' => ['2', '4', '8', '9', '10', '11'],
    ];

    private const array FILTERS = [
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

    public function __construct(int $radius = 200, ?Client $client = null, ?VersionService $versionService = null)
    {
        $this->radius = $radius;
        $this->versionService = $versionService ?? new VersionService;
        $this->client = $client ?? new Client;
    }

    private function getHeaders(): array
    {
        return [
            'Accept' => 'application/json',
            'User-Agent' => $this->versionService->getRequestUserAgent(),
            'Accept-Encoding' => 'gzip, deflate',
        ];
    }

    public function setRadius(int $radius): void
    {
        $this->radius = $radius;
    }

    public function setCoordinates(Point $point): void
    {
        $this->point = $point;
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
        $query .= $this->getNwrFor(self::FILTERS_FORCE_NAME, '[name]');
        $query .= $this->getNwrFor(self::FILTERS);

        $query .= ');out center;';

        return $query;
    }

    private function getNwrFor(array $filters, string $append = '')
    {
        $query = '';
        foreach ($filters as $key => $filter) {
            if (is_array($filter)) {
                $filters = implode('|', $filter);
                $append .= $this->getExcludes($key);
                $query .= sprintf(
                    'nwr(around:%d,%f,%f)["%s"~"%s"]%s;',
                    $this->radius,
                    $this->point->getLatitude(),
                    $this->point->getLongitude(),
                    $key,
                    $filters,
                    $append
                );
            } else {
                $append .= $this->getExcludes($filter);
                $query .= sprintf(
                    'nwr(around:%d,%f,%f)["%s"]%s;',
                    $this->radius,
                    $this->point->getLatitude(),
                    $this->point->getLongitude(),
                    $filter,
                    $append
                );
            }
        }

        return $query;
    }

    /**
     * @throws OverpassApiOverloaded
     */
    public function getById(string $id, string $type = 'node'): array
    {
        if (! in_array($type, ['node', 'way', 'relation'])) {
            throw new InvalidArgumentException('Invalid type: '.$type);
        }

        $query = sprintf('[out:json];%s(id:%s);out;', $type, $id);
        Log::debug('Overpass query: '.$query);

        $url = self::INTERPRETER_URL.urlencode($query);
        Log::debug('Overpass URL: '.$url);
        try {
            $response = $this->client->request('GET', $url, ['headers' => $this->getHeaders()]);
        } catch (GuzzleException $e) {
            if ($e->getCode() === 504) {
                throw new OverpassApiOverloaded;
            }

            return [];
        }
        $response = $response->getBody()->getContents();

        return json_decode($response, true);
    }

    /**
     * @throws OverpassApiOverloaded
     */
    public function getElements(): array
    {
        $query = $this->getQuery();
        Log::debug('Overpass query: '.$query);

        $url = self::INTERPRETER_URL.urlencode($query);
        try {
            $response = $this->client->get($url, ['headers' => $this->getHeaders()]);
        } catch (GuzzleException $exception) {
            if ($exception->getCode() === 504) {
                throw new OverpassApiOverloaded;
            }

            return [];
        }
        $response = $response->getBody()->getContents();

        return json_decode($response, true)['elements'][0];
    }

    /**
     * @return Generator<OverpassLocation>
     */
    public function parseLocations(array $response): Generator
    {
        $elements = $response['elements'] ?? [];
        foreach ($elements as $element) {
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
