<?php

namespace App\Models;

use Clickbar\Magellan\Data\Geometries\LineString;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $id
 * @property string $from_location_id
 * @property string $to_location_id
 * @property int $distance Distance in meters
 * @property int|null $duration Duration in seconds
 * @property string|null $path_type Type of path, e.g., rail, road, trail
 * @property LineString $geometry Geospatial data representing the route segment
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Location $fromLocation
 * @property-read \App\Models\Location $toLocation
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RouteSegment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RouteSegment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RouteSegment query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RouteSegment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RouteSegment whereDistance($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RouteSegment whereDuration($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RouteSegment whereFromLocationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RouteSegment whereGeometry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RouteSegment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RouteSegment wherePathType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RouteSegment whereToLocationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RouteSegment whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
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
