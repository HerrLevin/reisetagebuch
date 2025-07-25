<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransportPost extends Model
{
    use HasUuids;

    protected $fillable = [
        'id',
        'post_id',
        'transport_trip_id',
        'origin_stop_id',
        'destination_stop_id',
    ];

    public function origin(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'origin_id');
    }

    public function destination(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'destination_id');
    }

    public function transportTrip(): BelongsTo
    {
        return $this->belongsTo(TransportTrip::class, 'transport_trip_id');
    }

    public function originStop(): BelongsTo
    {
        return $this->belongsTo(TransportTripStop::class, 'origin_stop_id');
    }

    public function destinationStop(): BelongsTo
    {
        return $this->belongsTo(TransportTripStop::class, 'destination_stop_id');
    }
}
