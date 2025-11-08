<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TransportTrip extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'foreign_trip_id',
        'provider',
        'mode',
        'line_name',
        'route_long_name',
        'trip_short_name',
        'display_name',
        'route_color',
        'route_text_color',
        'last_refreshed_at',
        'user_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'last_refreshed_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::deleting(function (TransportTrip $trip) {
            TransportTripStop::whereTransportTripId($trip->id)->delete();
        });
    }

    public function stops(): HasMany
    {
        return $this->hasMany(TransportTripStop::class, 'transport_trip_id', 'id')->orderBy('stop_sequence');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
