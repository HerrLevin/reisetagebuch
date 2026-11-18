<?php

namespace App\Models;

use Database\Factories\FollowFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Follow extends Model
{
    /** @use HasFactory<FollowFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'origin_user_id',
        'target_user_id',
    ];

    /** @return BelongsTo<User, $this> */
    public function originUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'origin_user_id');
    }

    /** @return BelongsTo<User, $this> */
    public function targetUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'target_user_id');
    }
}
