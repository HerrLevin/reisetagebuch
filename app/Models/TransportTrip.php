<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
