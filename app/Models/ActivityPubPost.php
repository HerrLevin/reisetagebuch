<?php

namespace App\Models;

use App\Casts\RtbExtensionCast;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ActivityPubPost extends Model
{
    use HasUuids;

    protected $fillable = [
        'activity_pub_actor_id',
        'activity_id',
        'url',
        'content',
        'in_reply_to',
        'mentions',
        'extension_data',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'mentions' => 'array',
            'extension_data' => RtbExtensionCast::class,
        ];
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(ActivityPubActor::class, 'activity_pub_actor_id');
    }

    public function likes(): HasMany
    {
        return $this->hasMany(ActivityPubPostLike::class);
    }

    public function userLikes(): HasMany
    {
        return $this->hasMany(ActivityPubPostLike::class);
    }
}
