<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property ?string $id
 * @property string $user_id
 * @property ?string $avatar
 * @property ?string $bio
 * @property ?string $website
 *
 * @property-read User $user
 * @property-read Carbon $created_at
 * @property-read Carbon $updated_at
 */
class Profile extends Model
{
    protected $fillable = [
        'user_id',
        'avatar',
        'bio',
        'website',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
