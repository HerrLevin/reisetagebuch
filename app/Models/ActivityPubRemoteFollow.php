<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityPubRemoteFollow extends Model
{
    use HasUuids;

    public function actor(): BelongsTo
    {
        return $this->belongsTo(ActivityPubActor::class, 'remote_actor_id', 'actor_uri');
    }

    protected $fillable = [
        'local_user_id',
        'remote_actor_id',
        'remote_actor_inbox_url',
        'remote_actor_shared_inbox_url',
        'follow_activity_id',
        'state',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'local_user_id');
    }
}
