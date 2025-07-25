<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 
 *
 * @property string $id
 * @property string $post_id
 * @property string $location_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Location|null $location
 * @property-read \App\Models\Post $post
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LocationPost newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LocationPost newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LocationPost query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LocationPost whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LocationPost whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LocationPost whereLocationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LocationPost wherePostId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LocationPost whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class LocationPost extends Model
{
    use HasUuids;

    protected $fillable = ['post_id', 'location_id'];

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }
}
