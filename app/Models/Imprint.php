<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Imprint extends Model
{
    use HasUuids;

    protected $fillable = [
        'content',
    ];
}
