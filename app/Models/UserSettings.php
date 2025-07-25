<?php

namespace App\Models;

use App\Enums\DefaultNewPostView;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSettings extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'default_new_post_view',
    ];

    protected $casts = [
        'default_new_post_view' => DefaultNewPostView::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
