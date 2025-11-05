<?php

namespace App\Models;

use App\Enums\PostMetaInfo\MetaInfoKey;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostMetaInfo extends Model
{
    use HasUuids;

    protected $fillable = [
        'post_id',
        'key',
        'value',
    ];

    protected $casts = [
        'post_id' => 'integer',
        'key' => MetaInfoKey::class,
    ];

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }
}
