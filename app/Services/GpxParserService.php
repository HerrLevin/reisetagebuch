<?php

declare(strict_types=1);

namespace App\Services;

use Clickbar\Magellan\Data\Geometries\LineString;
use Clickbar\Magellan\Data\Geometries\Point;
use SimpleXMLElement;

class GpxParserService
{
    public function parse(string $gpxContent): LineString
    {
        $xml = new SimpleXMLElement($gpxContent);

        // Handle GPX namespace
        $namespaces = $xml->getNamespaces(true);
        $ns = $namespaces[''] ?? null;

        $points = [];

        if ($ns) {
            $xml->registerXPathNamespace('gpx', $ns);
            $trackpoints = $xml->xpath('//gpx:trkpt');
        } else {
            $trackpoints = $xml->xpath('//trkpt');
        }

        foreach ($trackpoints as $trkpt) {
            $lat = (float) $trkpt['lat'];
            $lon = (float) $trkpt['lon'];
            $points[] = Point::makeGeodetic($lat, $lon);
        }

        if (count($points) < 2) {
            abort(422, 'GPX file must contain at least 2 track points');
        }

        return LineString::make($points);
    }
}
