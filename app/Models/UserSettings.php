<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSettings extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'motis_radius',
        'requires_follow_request',
        'hide_posts_after',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
