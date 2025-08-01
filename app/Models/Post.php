<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

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

    public function metaInfos(): HasMany
    {
        return $this->hasMany(PostMetaInfo::class);
    }
}
