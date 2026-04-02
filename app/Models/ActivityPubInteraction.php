<?php

namespace App\Models;

use App\Enums\ActivityPubInteractionType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityPubInteraction extends Model
{
    use HasUuids;

    protected $fillable = [
        'type',
        'activity_id',
        'actor_id',
        'actor_username',
        'actor_display_name',
        'actor_avatar',
        'actor_instance',
        'post_id',
        'content',
        'remote_url',
    ];

    protected $casts = [
        'type' => ActivityPubInteractionType::class,
    ];

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }
}
