<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Location;
use App\Models\RouteSegment;
use App\Models\TransportTrip;
use App\Models\TransportTripStop;
use App\Models\User;
use Carbon\Carbon;
use Clickbar\Magellan\Data\Geometries\Dimension;
use Clickbar\Magellan\Data\Geometries\Geometry;
use Clickbar\Magellan\Data\Geometries\LineString;
use Clickbar\Magellan\Database\PostgisFunctions\ST;
use Illuminate\Database\Eloquent\Collection;
use Log;

class TransportTripRepository
{
    public function getActiveTrips(?string $provider = null): array
    {
        $stops = TransportTripStop::with('transportTrip')
            ->where(function ($query) {
                $query->where('arrival_time', '>', Carbon::now()->subHours(12))
                    ->Where('arrival_time', '<', Carbon::now());
            })
            ->whereNotNull('arrival_time')
            ->get();
        Log::debug('Foobar', ['stops' => $stops->count()]);

        $ids = [];
        /**
         * @var TransportTripStop $stop
         */
        foreach ($stops as $stop) {
            if ($provider !== null && $stop->transportTrip->provider !== $provider) {
                continue;
            }
            if ($stop->transportTrip->last_refreshed_at === null ||
                $stop->transportTrip->last_refreshed_at > Carbon::now()->subMinutes(config('app.transit.refresh_interval'))) {
                continue;
            }

            if (in_array((string) $stop->transportTrip->foreign_trip_id, $ids, true)) {
                continue;
            }

            if ($stop->arrival_time->addMinutes($stop->arrival_delay ?? 30)->isAfter(now())) {
                $ids[] = (string) $stop->transportTrip->foreign_trip_id;
            }
        }

        return array_values($ids);

    }

    public function getTripByIdentifier(
        ?string $foreignId = null,
        ?string $provider = null,
        ?array $with = null
    ): ?TransportTrip {
        $query = TransportTrip::where('foreign_trip_id', $foreignId);
        if ($provider) {
            $query->where('provider', $provider);
        }

        if ($with) {
            $query->with($with);
        }

        return $query->first();
    }

    public function getTripById(
        string $tripId,
        ?array $with = null
    ): ?TransportTrip {
        $query = TransportTrip::where('id', $tripId);

        if ($with) {
            $query->with($with);
        }

        return $query->first();
    }

    public function getOrCreateTrip(
        string $mode,
        ?string $foreignId = null,
        ?string $provider = null,
        ?string $lineName = null,
        ?string $routeLongName = null,
        ?string $tripShortName = null,
        ?string $displayName = null,
        ?string $routeColor = null,
        ?string $routeTextColor = null,
        ?User $user = null
    ): TransportTrip {
        $trip = $this->getTripByIdentifier($foreignId, $provider);

        if ($trip) {
            return $trip;
        }

        $trip = new TransportTrip;
        $trip->mode = $mode;
        $trip->foreign_trip_id = $foreignId;
        $trip->provider = $provider;
        $trip->line_name = $lineName;
        $trip->route_long_name = $routeLongName;
        $trip->trip_short_name = $tripShortName;
        $trip->display_name = $displayName;
        $trip->user_id = $user?->id;
        $trip->route_color = $routeColor;
        $trip->route_text_color = $routeTextColor;
        $trip->last_refreshed_at = now();
        $trip->save();

        return $trip;
    }

    public function addStopToTrip(
        TransportTrip $trip,
        Location $location,
        int $stopSequence,
        ?Carbon $arrivalTime = null,
        ?Carbon $departureTime = null,
        ?int $arrivalDelay = null,
        ?int $departureDelay = null,
        bool $cancelled = false,
        ?RouteSegment $routeSegment = null
    ): TransportTripStop {
        $stop = new TransportTripStop;
        $stop->transport_trip_id = $trip->id;
        $stop->location_id = $location->id;
        $stop->arrival_time = $arrivalTime;
        $stop->departure_time = $departureTime;
        $stop->arrival_delay = $arrivalDelay;
        $stop->departure_delay = $departureDelay;
        $stop->stop_sequence = $stopSequence;
        $stop->cancelled = $cancelled;
        $stop->route_segment_id = $routeSegment?->id;

        $stop->save();

        return $stop;
    }

    public function updateStopTimes(
        TransportTripStop $stop,
        ?Carbon $arrivalTime = null,
        ?Carbon $departureTime = null,
        ?int $arrivalDelay = null,
        ?int $departureDelay = null,
        bool $cancelled = false
    ): void {
        $stop->arrival_time = $arrivalTime;
        $stop->departure_time = $departureTime;
        $stop->arrival_delay = $arrivalDelay;
        $stop->departure_delay = $departureDelay;
        $stop->cancelled = $cancelled;

        $stop->save();
    }

    public function createRouteSegment(
        Location $fromLocation,
        Location $toLocation,
        ?int $duration = null,
        ?string $pathType = null,
        ?Geometry $geometry = null
    ): RouteSegment {
        $segment = new RouteSegment;
        $segment->from_location_id = $fromLocation->id;
        $segment->to_location_id = $toLocation->id;
        $segment->distance = ST::distanceSphere($fromLocation->location, $toLocation->location);
        $segment->duration = $duration;
        $segment->path_type = $pathType;
        if (! $geometry->getDimension()->hasZDimension()) {
            $points = [];
            foreach ($geometry->getPoints() as $point) {
                $point->setZ(0);
                $points[] = $point;
            }
            $geometry = LineString::make($points, $geometry->getSrid(), Dimension::DIMENSION_3DZ);
        }
        $segment->geometry = $geometry;

        $segment->save();

        return $segment;
    }

    public function setRouteSegmentForStop(
        TransportTripStop $stop,
        RouteSegment $routeSegment
    ): void {
        $stop->route_segment_id = $routeSegment->id;
        $stop->save();
    }

    public function getRouteSegmentBetweenStops(
        TransportTripStop $start,
        TransportTripStop $end,
        int $duration,
        string $pathType = 'rail'
    ): ?RouteSegment {
        return RouteSegment::where('from_location_id', $start->location_id)
            ->where('to_location_id', $end->location_id)
            ->where('duration', $duration)
            ->where('path_type', $pathType)
            ->first();
    }

    public function getStopById(string $stopId): ?TransportTripStop
    {
        return TransportTripStop::find($stopId);
    }

    public function getStopsBetween(
        TransportTripStop $start,
        TransportTripStop $end,
        bool $withRouteSegment = true
    ): Collection {
        $query = $this->getStopsForTripQuery($start->transport_trip_id)
            ->whereBetween('stop_sequence', [$start->stop_sequence, $end->stop_sequence]);
        if ($withRouteSegment) {
            $query->with('routeSegment');
        }

        return $query->get();
    }

    private function getStopsForTripQuery(string $tripId): \Illuminate\Database\Eloquent\Builder
    {
        return TransportTripStop::where('transport_trip_id', $tripId)
            ->orderBy('stop_sequence');
    }
}
