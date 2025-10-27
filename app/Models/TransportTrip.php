<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TransportTrip extends Model
{
    use HasUuids;

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
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function stops(): HasMany
    {
        return $this->hasMany(TransportTripStop::class, 'transport_trip_id', 'id')->orderBy('stop_sequence');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
