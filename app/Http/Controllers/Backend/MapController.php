<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\TransportTripStop;
use App\Repositories\TransportTripRepository;
use Clickbar\Magellan\Data\Geometries\LineString;

class MapController extends Controller
{
    private TransportTripRepository $transportTripRepository;

    public function __construct(TransportTripRepository $transportTripRepository)
    {
        $this->transportTripRepository = $transportTripRepository;
    }

    public function fromTo(string $from, string $to): ?LineString
    {
        $start = $this->transportTripRepository->getStopById($from);
        $end = $this->transportTripRepository->getStopById($to);

        if (!$start || !$end) {
            return null;
        }

        $stops = $this->transportTripRepository->getStopsBetween($start, $end);

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
