<?php

namespace App\Models;

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
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
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
