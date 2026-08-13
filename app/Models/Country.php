<?php

namespace App\Models;

use Clickbar\Magellan\Data\Geometries\MultiPolygon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    use HasFactory;

    protected $fillable = ['iso_a2', 'name', 'geometry'];

    protected $casts = [
        'geometry' => MultiPolygon::class,
    ];
}
