<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Location;
use App\Models\RouteSegment;
use App\Models\TransportTrip;
use App\Models\TransportTripStop;
use Carbon\Carbon;

class TransportTripRepository
{
    public function getTripByIdentifier(
        ?string $foreignId = null,
        ?string $provider = null,
        ?array $with = null
    ): ?TransportTrip {
        $query = TransportTrip::where('foreign_trip_id', $foreignId)
            ->where('provider', $provider);

        if ($with) {
            $query->with($with);
        }
            return $query->first();
    }

    public function getOrCreateTrip(
        string $mode,
        ?string $foreignId = null,
        ?string $provider = null,
        ?string $lineName = null
    ): TransportTrip {
        $trip = $this->getTripByIdentifier($foreignId, $provider);

        if ($trip) {
            return $trip;
        }

        $trip = new TransportTrip();
        $trip->mode = $mode;
        $trip->foreign_trip_id = $foreignId;
        $trip->provider = $provider;
        $trip->line_name = $lineName;
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
        $stop = new TransportTripStop();
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
}
