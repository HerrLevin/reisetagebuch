<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityPubInboxItem extends Model
{
    protected $fillable = [
        'activity_id',
        'actor_id',
        'activity_type',
    ];
}
