<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $id
 * @property string $post_id
 * @property string $location_id
 * @property-read Carbon $created_at
 * @property-read Carbon $updated_at
 *
 * relations
 * @property-read Post $post
 * @property-read Location $location
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
