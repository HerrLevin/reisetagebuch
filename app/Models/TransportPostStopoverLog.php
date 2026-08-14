<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransportPostStopoverLog extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'transport_post_id',
        'transport_trip_stop_id',
        'manual_arrival',
        'manual_departure',
    ];

    protected $casts = [
        'manual_arrival' => 'datetime',
        'manual_departure' => 'datetime',
    ];

    public function transportPost(): BelongsTo
    {
        return $this->belongsTo(TransportPost::class);
    }

    public function transportTripStop(): BelongsTo
    {
        return $this->belongsTo(TransportTripStop::class);
    }
}
