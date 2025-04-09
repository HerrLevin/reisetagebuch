<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Venue extends Model
{
    use HasUuids;

    protected $fillable = ['name', 'osm_type', 'osm_id', 'latitude', 'longitude'];

}
