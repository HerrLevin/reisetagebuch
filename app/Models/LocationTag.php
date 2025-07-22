<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $id
 * @property string $location_id
 * @property string $key
 * @property string|null $value
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Location $location
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LocationTag newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LocationTag newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LocationTag query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LocationTag whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LocationTag whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LocationTag whereKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LocationTag whereLocationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LocationTag whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LocationTag whereValue($value)
 *
 * @mixin \Eloquent
 */
class LocationTag extends Model
{
    use HasUuids;

    protected $fillable = ['location_id', 'key', 'value'];

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }
}
