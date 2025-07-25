<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invite extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'used_by',
        'used_at',
        'expires_at',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'used_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function usedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'used_by', 'id');
    }
}
