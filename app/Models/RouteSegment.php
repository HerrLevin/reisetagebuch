<?php

namespace App\Models;

use Clickbar\Magellan\Data\Geometries\LineString;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RouteSegment extends Model
{
    use HasUuids;

    protected $fillable = [
        'from_location_id',
        'to_location_id',
        'distance',
        'duration',
        'path_type',
        'geometry',
    ];

    protected $casts = [
        'path_type' => 'string',
        'geometry' => LineString::class,
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function fromLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'from_location_id');
    }

    public function toLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'to_location_id');
    }
}
