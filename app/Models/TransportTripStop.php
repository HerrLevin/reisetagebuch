<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransportTripStop extends Model
{
    use HasFactory, HasUuids;

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
