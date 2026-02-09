<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend;

use App\Dto\MotisApi\TripDto;
use App\Enums\TransportMode;
use App\Exceptions\BrouterRouteCreationFailed;
use App\Http\Controllers\Controller;
use App\Models\TransportTrip;
use App\Models\TransportTripStop;
use App\Repositories\TransportTripRepository;
use App\Services\BrouterRequestService;
use Clickbar\Magellan\Data\Geometries\Geometry;
use Clickbar\Magellan\Data\Geometries\LineString;
use Clickbar\Magellan\Data\Geometries\Point;
use Clickbar\Magellan\IO\Parser\Geojson\GeojsonParser;
use GuzzleHttp\Exception\GuzzleException;
use Log;

class RerouteStopsController extends Controller
{
    private BrouterRequestService $brouterRequestService;

    private TransportTripRepository $transportTripRepository;

    private GeoJsonParser $geoJsonParser;

    public function __construct(
        BrouterRequestService $brouterRequestService,
        TransportTripRepository $transportTripRepository,
        GeojsonParser $geoJsonParser
    ) {
        $this->brouterRequestService = $brouterRequestService;
        $this->transportTripRepository = $transportTripRepository;
        $this->geoJsonParser = $geoJsonParser;
    }

    /**
     * @param  TransportTripStop[]  $stops
     */
    public function rerouteStops(TripDto|TransportTrip $tripDto, array $stops): void
    {
        foreach ($stops as $key => $stop) {
            $previousStop = $stops[$key - 1] ?? null;
            if (! $previousStop || $stop['route_segment_id'] !== null) {
                continue;
            }

            if ($tripDto instanceof TripDto) {
                $leg = $tripDto->legs[0] ?? null;
                $mode = $leg?->mode;
            } else {
                $mode = $tripDto->mode;
            }

            $pathType = $this->getPathType(TransportMode::tryFrom($mode));
            if (! $pathType) {
                Log::warning('RerouteStops: Unsupported transport mode, interpolating', ['mode' => $mode]);
                $this->interpolateBetween($previousStop, $stop, 'mode:'.$mode);

                continue;
            }
            Log::debug('RerouteStops: Transport mode', ['mode' => $mode, 'pathType' => $pathType]);
            $this->rerouteBetween($previousStop, $stop, $pathType);
        }
    }

    private function interpolateBetween(TransportTripStop $start, TransportTripStop $end, string $pathType): void
    {
        $duration = $this->getDuration($start, $end);

        $segment = $this->transportTripRepository->getRouteSegmentBetweenStops($start, $end, $duration, $pathType, true);
        if ($segment) {
            Log::debug('RerouteStops: interpolated segment already exists, setting for stop', ['segment' => $segment->id]);
            $this->transportTripRepository->setRouteSegmentForStop($start, $segment);

            return; // already rerouted
        }

        $this->interpolateGreatCircle($start, $end);
        $this->storeReroute($start, $end, 'interpolated', $duration, $this->interpolateGreatCircle($start, $end), true);
    }

    private function getDuration(TransportTripStop $start, TransportTripStop $end): int
    {
        $startTime = $start->departure_time ?? $start->arrival_time;
        $endTime = $end->arrival_time ?? $end->departure_time;

        if ($startTime && $endTime) {
            return (int) round($startTime->diffInSeconds($endTime));
        }

        return -1;
    }

    private function rerouteBetween(TransportTripStop $start, TransportTripStop $end, string $pathType): void
    {
        Log::debug('RerouteStops', [$start, $end, $pathType]);

        $duration = $this->getDuration($start, $end);

        $segment = $this->transportTripRepository->getRouteSegmentBetweenStops($start, $end, $duration, $pathType);
        if ($segment) {
            Log::debug('RerouteStops: Segment already exists, setting for stop', ['segment' => $segment->id]);
            $this->transportTripRepository->setRouteSegmentForStop($start, $segment);

            return; // already rerouted
        }
        try {
            Log::debug('Getting new route from BRouter', [
                'from' => $start->location->location,
                'to' => $end->location->location,
                'type' => $pathType,
            ]);
            $route = $this->brouterRequestService->getRoute($start->location->location, $end->location->location, $pathType);
        } catch (BrouterRouteCreationFailed|GuzzleException $e) {
            report($e);
            $route = null;
        }

        if ($route) {
            $route = $this->geoJsonParser->parse($route);
            $this->storeReroute($start, $end, $pathType, $duration, $route);

            return;
        }

        $route = $this->interpolateGreatCircle($start, $end);
        $this->storeReroute($start, $end, $pathType, $duration, $route, true);
    }

    private function storeReroute(TransportTripStop $start, TransportTripStop $end, string $pathType, int $duration, Geometry $route, bool $interpolated = false): void
    {
        try {
            $segment = $this->transportTripRepository->createRouteSegment($start->location, $end->location, $duration, $pathType, $route, $interpolated);
            $this->transportTripRepository->setRouteSegmentForStop($start, $segment);
        } catch (\Exception $e) {
            Log::error('RerouteStops: Failed to create route segment', ['error' => $e->getMessage()]);
            report($e);
        }
    }

    private function getPathType(?TransportMode $mode): ?string
    {
        $railModes = [
            TransportMode::RAIL,
            TransportMode::HIGHSPEED_RAIL,
            TransportMode::LONG_DISTANCE,
            TransportMode::NIGHT_RAIL,
            TransportMode::REGIONAL_FAST_RAIL,
            TransportMode::REGIONAL_RAIL,
            TransportMode::TRAM,
            TransportMode::SUBWAY,
            TransportMode::METRO,
            TransportMode::FUNICULAR,
        ];
        $roadModes = [
            TransportMode::CAR,
            TransportMode::CAR_PARKING,
            TransportMode::ODM,
            TransportMode::FLEX,
            TransportMode::BUS,
            TransportMode::COACH,
        ];

        if (in_array($mode, $railModes, true)) {
            return 'rail';
        }
        if (in_array($mode, $roadModes, true)) {
            return 'road';
        }

        return null;
    }

    public function interpolateGreatCircle(TransportTripStop $from, TransportTripStop $to, int $numPoints = 20): Geometry
    {
        $from = $from->location->location;
        $to = $to->location->location;
        $lat1 = deg2rad($from->getLatitude());
        $lon1 = deg2rad($from->getLongitude());
        $lat2 = deg2rad($to->getLatitude());
        $lon2 = deg2rad($to->getLongitude());

        $points = [$from];
        $centralAngle = $this->centralAngle($lat1, $lon1, $lat2, $lon2);
        for ($i = 1; $i <= $numPoints; $i++) {
            $f = $i / ($numPoints + 1);
            // Slerp (spherical linear interpolation)
            $a = sin((1 - $f) * $centralAngle) / sin($centralAngle);
            $b = sin($f * $centralAngle) / sin($centralAngle);
            $x = $a * cos($lat1) * cos($lon1) + $b * cos($lat2) * cos($lon2);
            $y = $a * cos($lat1) * sin($lon1) + $b * cos($lat2) * sin($lon2);
            $z = $a * sin($lat1) + $b * sin($lat2);
            $lat = atan2($z, sqrt($x * $x + $y * $y));
            $lon = atan2($y, $x);
            // Use the same class as $from if possible, otherwise fallback to array
            $points[] = Point::makeGeodetic(rad2deg($lat), rad2deg($lon));
        }
        $points[] = $to; // Ensure the last point is included

        return LineString::make($points);
    }

    /**
     * Calculate the central angle between two points on a sphere.
     */
    private function centralAngle($lat1, $lon1, $lat2, $lon2): float
    {
        $dLat = $lat2 - $lat1;
        $dLon = $lon2 - $lon1;
        $a = pow(sin($dLat / 2), 2) + cos($lat1) * cos($lat2) * pow(sin($dLon / 2), 2);

        return 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}
