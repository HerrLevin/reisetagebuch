<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $id
 * @property string $post_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $transport_trip_id
 * @property string $origin_stop_id
 * @property string $destination_stop_id
 * @property-read \App\Models\Location|null $destination
 * @property-read \App\Models\TransportTripStop $destinationStop
 * @property-read \App\Models\Location|null $origin
 * @property-read \App\Models\TransportTripStop $originStop
 * @property-read \App\Models\TransportTrip $transportTrip
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransportPost newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransportPost newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransportPost query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransportPost whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransportPost whereDestinationStopId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransportPost whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransportPost whereOriginStopId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransportPost wherePostId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransportPost whereTransportTripId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransportPost whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
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
