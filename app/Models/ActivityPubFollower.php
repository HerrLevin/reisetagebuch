<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityPubFollower extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'follower_actor_id',
        'followed_user_id',
        'activity_pub_actor_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'followed_user_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(ActivityPubActor::class, 'activity_pub_actor_id');
    }

    protected function followerInboxUrl(): Attribute
    {
        return Attribute::get(fn () => $this->actor?->inbox_url);
    }

    protected function followerSharedInboxUrl(): Attribute
    {
        return Attribute::get(fn () => $this->actor?->shared_inbox_url);
    }
}
