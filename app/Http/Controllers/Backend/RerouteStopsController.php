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
            if (! $previousStop) {
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
                Log::warning('RerouteStops: Unsupported transport mode, skipping', ['mode' => $mode]);

                continue;
            }
            Log::debug('RerouteStops: Transport mode', ['mode' => $mode, 'pathType' => $pathType]);
            $this->rerouteBetween($previousStop, $stop, $pathType);
        }
    }

    private function rerouteBetween(TransportTripStop $start, TransportTripStop $end, string $pathType): void
    {
        Log::debug('RerouteStops', [$start, $end, $pathType]);

        $startTime = $start->departure_time ?? $start->arrival_time;
        $endTime = $end->arrival_time ?? $end->departure_time;

        $duration = -1;
        if ($startTime && $endTime) {
            $duration = (int) round($startTime->diffInSeconds($endTime));
            Log::debug('RerouteStops', [$duration, $pathType]);
        }

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

        $route = $this->geoJsonParser->parse($route);

        try {
            $segment = $this->transportTripRepository->createRouteSegment($start->location, $end->location, $duration, $pathType, $route);
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
}
