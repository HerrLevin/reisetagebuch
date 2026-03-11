<?php

namespace App\Models;

use Database\Factories\HashTagFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class HashTag extends Model
{
    /** @use HasFactory<HashTagFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'value',
        'relevance',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, 'posts_hash_tags_map')
            ->withPivot(['default'])
            ->using(PostsHashTagsMap::class);
    }
}
