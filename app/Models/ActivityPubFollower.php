<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityPubFollower extends Model
{
    use HasUuids;

    protected $fillable = [
        'follower_actor_id',
        'followed_user_id',
        'follower_shared_inbox_url',
        'follower_inbox_url',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
