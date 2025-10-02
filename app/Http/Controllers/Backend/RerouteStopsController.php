<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend;

use App\Dto\MotisApi\TripDto;
use App\Exceptions\BrouterRouteCreationFailed;
use App\Http\Controllers\Controller;
use App\Models\TransportTrip;
use App\Models\TransportTripStop;
use App\Repositories\TransportTripRepository;
use App\Services\BrouterRequestService;
use Clickbar\Magellan\IO\Parser\Geojson\GeojsonParser;
use GuzzleHttp\Exception\GuzzleException;

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

            $pathType = $this->getPathType($mode);
            $this->rerouteBetween($previousStop, $stop, $pathType);
        }
    }

    private function rerouteBetween(TransportTripStop $start, TransportTripStop $end, ?string $pathType): void
    {
        if (! $pathType) {
            return;
        }

        $startTime = $start->departure_time ?? $start->arrival_time;
        $endTime = $end->arrival_time ?? $end->departure_time;

        $duration = -1;
        if ($startTime && $endTime) {
            $duration = (int) round($startTime->diffInSeconds($endTime));
        }

        $segment = $this->transportTripRepository->getRouteSegmentBetweenStops($start, $end, $duration, $pathType);
        if ($segment) {
            $this->transportTripRepository->setRouteSegmentForStop($start, $segment);

            return; // already rerouted
        }
        try {
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
            report($e);
        }
    }

    private function getPathType(string $mode): ?string
    {
        $railModes = [
            'RAIL',
            'HIGHSPEED_RAIL',
            'LONG_DISTANCE',
            'NIGHT_RAIL',
            'REGIONAL_FAST_RAIL',
            'REGIONAL_RAIL',
            'TRAM',
            'SUBWAY',
            'METRO',
            'FUNICULAR',
        ];
        $roadModes = [
            'CAR',
            'CAR_PARKING',
            'ODM',
            'FLEX',
            'BUS',
            'COACH',
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
