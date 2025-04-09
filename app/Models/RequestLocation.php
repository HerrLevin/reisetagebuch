<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class RequestLocation extends Model
{
    use HasUuids;

    protected $fillable = ['latitude', 'longitude', 'last_requested_at'];
}
