<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\TransportTripStop;
use App\Repositories\TransportTripRepository;
use Clickbar\Magellan\Data\Geometries\LineString;
use Clickbar\Magellan\Data\Geometries\MultiPoint;
use Illuminate\Support\Collection;

class MapController extends Controller
{
    private TransportTripRepository $transportTripRepository;

    public function __construct(TransportTripRepository $transportTripRepository)
    {
        $this->transportTripRepository = $transportTripRepository;
    }

    public function stopsFromTo(string $fromStopId, string $toStopId): Collection
    {
        $start = $this->transportTripRepository->getStopById($fromStopId);
        $end = $this->transportTripRepository->getStopById($toStopId);

        if (! $start || ! $end) {
            return collect();
        }

        return $this->transportTripRepository->getStopsBetween($start, $end);
    }

    public function stopPointGeometryFromTo(string $fromStopId, string $toStopId): ?MultiPoint
    {
        $stops = $this->stopsFromTo($fromStopId, $toStopId);

        if ($stops->isEmpty()) {
            return null;
        }

        $points = [];
        foreach ($stops as $stop) {
            /** @var TransportTripStop $stop */
            $points[] = $stop->location->location;
        }

        return MultiPoint::make($points);
    }

    public function fromTo(string $fromStopId, string $toStopId): ?LineString
    {
        $stops = $this->stopsFromTo($fromStopId, $toStopId);

        if ($stops->isEmpty()) {
            return null;
        }

        // todo: there HAS to be a better way to do this with PostGIS
        $points = [];
        foreach ($stops as $key => $stop) {
            /** @var TransportTripStop $stop */
            $foo = $stop->routeSegment;

            if ($foo && $key < count($stops) - 1) {
                foreach ($foo->geometry->getPoints() as $point) {
                    $points[] = $point;
                }
            } else {
                $points[] = $stop->location->location;
            }
        }

        return LineString::make($points);
    }
}
