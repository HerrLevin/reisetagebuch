<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
