<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserStatistics extends Model
{
    /** @use HasFactory<\Database\Factories\UserStatisticsFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'posts_count',
        'transport_posts_count',
        'location_posts_count',
        'followers_count',
        'following_count',
        'travelled_distance',
        'travelled_duration',
        'visited_countries_count',
        'visited_locations_count',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
