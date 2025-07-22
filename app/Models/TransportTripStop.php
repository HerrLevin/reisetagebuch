<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $id
 * @property string $transport_trip_id
 * @property string $location_id
 * @property \Illuminate\Support\Carbon|null $arrival_time Arrival time at the stop, in UTC
 * @property \Illuminate\Support\Carbon|null $departure_time Departure time from the stop, in UTC
 * @property int|null $arrival_delay Delay in seconds at arrival, 0 if on time, null if not applicable
 * @property int|null $departure_delay Delay in seconds at departure, 0 if on time, null if not applicable
 * @property int $stop_sequence Sequence number of the stop in the trip, starting from 0
 * @property bool $cancelled Indicates if the stop was cancelled, true if cancelled, false otherwise
 * @property string|null $route_segment_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Location $location
 * @property-read \App\Models\RouteSegment|null $routeSegment
 * @property-read \App\Models\TransportTrip $transportTrip
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransportTripStop newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransportTripStop newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransportTripStop query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransportTripStop whereArrivalDelay($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransportTripStop whereArrivalTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransportTripStop whereCancelled($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransportTripStop whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransportTripStop whereDepartureDelay($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransportTripStop whereDepartureTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransportTripStop whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransportTripStop whereLocationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransportTripStop whereRouteSegmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransportTripStop whereStopSequence($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransportTripStop whereTransportTripId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransportTripStop whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class TransportTripStop extends Model
{
    use HasUuids;

    protected $fillable = [
        'transport_trip_id',
        'location_id',
        'arrival_time',
        'departure_time',
        'arrival_delay',
        'departure_delay',
        'stop_sequence',
        'cancelled',
        'route_segment_id',
    ];

    protected $casts = [
        'arrival_time' => 'datetime',
        'departure_time' => 'datetime',
        'arrival_delay' => 'integer',
        'departure_delay' => 'integer',
        'stop_sequence' => 'integer',
        'cancelled' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function transportTrip(): BelongsTo
    {
        return $this->belongsTo(TransportTrip::class, 'transport_trip_id', 'id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'location_id', 'id');
    }

    public function routeSegment(): BelongsTo
    {
        return $this->belongsTo(RouteSegment::class, 'route_segment_id', 'id');
    }
}
