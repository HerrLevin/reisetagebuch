<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 
 *
 * @property string $id
 * @property string $location_id
 * @property string $type
 * @property string $identifier
 * @property string $origin
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Location $location
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LocationIdentifier newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LocationIdentifier newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LocationIdentifier query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LocationIdentifier whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LocationIdentifier whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LocationIdentifier whereIdentifier($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LocationIdentifier whereLocationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LocationIdentifier whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LocationIdentifier whereOrigin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LocationIdentifier whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LocationIdentifier whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class LocationIdentifier extends Model
{
    use HasUuids;
    
    protected $fillable = ['location_id', 'identifier', 'type', 'origin', 'name'];

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }
}
