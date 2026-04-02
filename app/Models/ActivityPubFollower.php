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
        'follower_inbox',
        'follower_shared_inbox',
        'follower_username',
        'follower_display_name',
        'follower_avatar',
        'followed_user_id',
    ];

    public function followedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'followed_user_id');
    }
}
