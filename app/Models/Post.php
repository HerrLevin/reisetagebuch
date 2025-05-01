<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $user_id
 * @property string $body
 * @property Carbon $published_at
 * @property-read Carbon $created_at
 * @property-read Carbon $updated_at
 *
 * relations
 * @property-read User $user
 * @property-read LocationPost $locationPost
 * @property-read TransportPost $transportPost
 */
class Post extends Model
{
    use HasUuids;

    protected $fillable = ['user_id', 'body', 'published_at'];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function locationPost(): HasOne
    {
        return $this->hasOne(LocationPost::class);
    }

    public function transportPost(): HasOne
    {
        return $this->hasOne(TransportPost::class);
    }
}
