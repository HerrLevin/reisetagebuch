<?php

namespace App\Models;

use Clickbar\Magellan\Data\Geometries\Point;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * 
 *
 * @property string $id
 * @property string|null $last_requested_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property Point $location
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequestLocation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequestLocation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequestLocation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequestLocation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequestLocation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequestLocation whereLastRequestedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequestLocation whereLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RequestLocation whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class RequestLocation extends Model
{
    use HasUuids;

    protected $fillable = ['location', 'last_requested_at'];
    protected $casts    = [
        'location' => Point::class,
    ];
}
