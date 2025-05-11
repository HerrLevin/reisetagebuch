<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 
 *
 * @property string $id
 * @property string $post_id
 * @property string|null $origin_id
 * @property string|null $destination_id
 * @property \Illuminate\Support\Carbon $departure
 * @property \Illuminate\Support\Carbon $arrival
 * @property int|null $departure_delay
 * @property int|null $arrival_delay
 * @property string $mode
 * @property string|null $line
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Location|null $destination
 * @property-read \App\Models\Location|null $origin
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransportPost newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransportPost newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransportPost query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransportPost whereArrival($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransportPost whereArrivalDelay($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransportPost whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransportPost whereDeparture($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransportPost whereDepartureDelay($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransportPost whereDestinationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransportPost whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransportPost whereLine($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransportPost whereMode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransportPost whereOriginId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransportPost wherePostId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransportPost whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class TransportPost extends Model
{
    use HasUuids;

    protected $fillable = [
        'id',
        'post_id',
        'origin_id',
        'destination_id',
        'departure',
        'arrival',
        'mode',
        'line',
        'departure_delay',
        'arrival_delay'
    ];

    protected $casts = [
        'departure' => 'datetime',
        'arrival' => 'datetime',
        'departure_delay' => 'integer',
        'arrival_delay' => 'integer',
    ];

    public function origin(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'origin_id');
    }

    public function destination(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'destination_id');
    }
}
