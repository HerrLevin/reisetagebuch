<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 *
 *
 * @property string $id
 * @property string $user_id
 * @property string|null $used_by
 * @property \Illuminate\Support\Carbon|null $expires_at
 * @property \Illuminate\Support\Carbon|null $used_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $createdBy
 * @property-read \App\Models\User|null $usedBy
 * @method static Builder<static>|Invite newModelQuery()
 * @method static Builder<static>|Invite newQuery()
 * @method static Builder<static>|Invite query()
 * @method static Builder<static>|Invite whereCreatedAt($value)
 * @method static Builder<static>|Invite whereExpiresAt($value)
 * @method static Builder<static>|Invite whereId($value)
 * @method static Builder<static>|Invite whereUpdatedAt($value)
 * @method static Builder<static>|Invite whereUsedAt($value)
 * @method static Builder<static>|Invite whereUsedBy($value)
 * @method static Builder<static>|Invite whereUserId($value)
 * @mixin \Eloquent
 */
class Invite extends Model
{
    use HasUuids, HasFactory;

    protected $fillable = [
        'user_id',
        'used_by',
        'used_at',
        'expires_at',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'used_at'    => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function usedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'used_by', 'id');
    }
}
