<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LocationIdentifier extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = ['location_id', 'identifier', 'type', 'origin', 'name'];

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }
}
