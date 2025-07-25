<?php

namespace App\Models;

use Clickbar\Magellan\Data\Geometries\Point;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class RequestLocation extends Model
{
    use HasUuids;

    protected $fillable = ['location', 'last_requested_at'];

    protected $casts = [
        'location' => Point::class,
    ];
}
