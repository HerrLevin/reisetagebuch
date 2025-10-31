<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostsHashTagsMap extends Model
{
    /** @use HasFactory<\Database\Factories\PostsHashTagsMapFactory> */
    use HasFactory, HasUuids;

    protected $table = 'posts_hash_tags_maps';

    protected $fillable = [
        'post_id',
        'hash_tag_id',
        'relevance',
    ];

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function hashTag(): BelongsTo
    {
        return $this->belongsTo(HashTag::class);
    }
}
