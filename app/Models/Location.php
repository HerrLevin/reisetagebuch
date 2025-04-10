<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $id
 * @property string $name
 * @property float $latitude
 * @property float $longitude
 *
 * relations
 * @property-read Collection|LocationIdentifier[] $identifiers
 * @property-read Collection|LocationTag[] $tags
 */
class Location extends Model
{
    use HasUuids;

    protected $fillable = ['name', 'latitude', 'longitude'];
    protected $relations = ['identifiers'];

    public function identifiers(): HasMany
    {
        return $this->hasMany(LocationIdentifier::class);
    }

    public function tags(): HasMany
    {
        return $this->hasMany(LocationTag::class);
    }
}
