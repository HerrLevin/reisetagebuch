<?php

namespace App\Models;

use Clickbar\Magellan\Data\Geometries\MultiPolygon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimezoneBoundary extends Model
{
    use HasFactory;

    protected $fillable = ['tzid', 'geometry'];

    protected $casts = [
        'geometry' => MultiPolygon::class,
    ];
}
