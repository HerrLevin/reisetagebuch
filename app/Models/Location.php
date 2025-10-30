<?php

namespace App\Models;

use Clickbar\Magellan\Data\Geometries\Point;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Location extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = ['name', 'location', 'type'];

    protected $relations = ['identifiers'];

    protected $casts = [
        'location' => Point::class,
    ];

    public function identifiers(): HasMany
    {
        return $this->hasMany(LocationIdentifier::class);
    }

    public function tags(): HasMany
    {
        return $this->hasMany(LocationTag::class);
    }
}
