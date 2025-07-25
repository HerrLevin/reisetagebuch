<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * 
 *
 * @property string $id
 * @property string|null $foreign_trip_id Unique identifier for the trip in the external system
 * @property string|null $provider Name of the data provider, e.g., "TransportAPI"
 * @property string $mode Transport mode, e.g., "bus", "train", "car"
 * @property string|null $line_name Name of the transport line, e.g., "Line 1", "Route A"
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TransportTripStop> $stops
 * @property-read int|null $stops_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransportTrip newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransportTrip newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransportTrip query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransportTrip whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransportTrip whereForeignTripId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransportTrip whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransportTrip whereLineName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransportTrip whereMode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransportTrip whereProvider($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TransportTrip whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class TransportTrip extends Model
{
    use HasUuids;

    protected $fillable = [
        'foreign_trip_id',
        'provider',
        'mode',
        'line_name',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function stops(): HasMany
    {
        return $this->hasMany(TransportTripStop::class, 'transport_trip_id', 'id');
    }
}
