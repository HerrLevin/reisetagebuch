<?php

namespace App\Models;

use App\Enums\Visibility;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Post extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'body',
        'published_at',
        'visibility',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'visibility' => Visibility::class,
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

    public function metaInfos(): HasMany
    {
        return $this->hasMany(PostMetaInfo::class);
    }

    public function hashTags(): BelongsToMany
    {
        return $this->belongsToMany(HashTag::class, 'posts_hash_tags_maps')
            ->withPivot('hash_tag_id', 'post_id')
            ->using(PostsHashTagsMap::class);
    }
}
