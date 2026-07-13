<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityPubPostLike extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'activity_pub_post_id',
        'activity_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(ActivityPubPost::class, 'activity_pub_post_id');
    }
}
