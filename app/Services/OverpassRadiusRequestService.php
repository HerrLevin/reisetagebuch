<?php

namespace App\Services;

use App\Dto\OverpassLocation;
use App\Exceptions\OverpassApiOverloaded;
use App\Services\Osm\OsmTagFilter;
use Clickbar\Magellan\Data\Geometries\Point;
use Generator;
use Illuminate\Support\Facades\Log;

class OverpassRadiusRequestService extends OverpassRequestService
{
    private Point $point;

    private int $radius;

    private const array EXCLUDE = OsmTagFilter::EXCLUDE;

    private const array FILTERS_FORCE_NAME = OsmTagFilter::FILTERS_FORCE_NAME;

    private const array FILTERS = OsmTagFilter::FILTERS;

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

    private function getNwrFor(array $filters, string $append = ''): string
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
    public function getElements(): array
    {
        $query = $this->getQuery();

        return $this->request($query);
    }

    /**
     * @return Generator<OverpassLocation>
     */
    public function parseLocations(array $response): Generator
    {
        $elements = $response['elements'] ?? [];
        Log::debug('Overpass elements count: '.count($elements));
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
