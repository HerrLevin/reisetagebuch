<?php

namespace App\Models;

use Clickbar\Magellan\Data\Geometries\Point;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** @property-read float|null $distance only set by queries that select ST_DistanceSphere as distance */
class Location extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = ['name', 'location', 'type', 'country_code'];

    protected $relations = ['identifiers'];

    protected $casts = [
        'location' => Point::class,
    ];

    /** @return HasMany<LocationIdentifier, $this> */
    public function identifiers(): HasMany
    {
        return $this->hasMany(LocationIdentifier::class);
    }

    /** @return HasMany<LocationTag, $this> */
    public function tags(): HasMany
    {
        return $this->hasMany(LocationTag::class);
    }
}
