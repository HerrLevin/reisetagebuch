<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LocationTag extends Model
{
    use HasUuids;

    protected $fillable = ['location_id', 'key', 'value'];

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }
}
