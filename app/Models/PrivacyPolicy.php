<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PrivacyPolicy extends Model
{
    use HasUuids;

    protected $fillable = [
        'content',
        'valid_from',
    ];

    protected function casts(): array
    {
        return [
            'valid_from' => 'datetime',
        ];
    }

    public function acceptances(): HasMany
    {
        return $this->hasMany(PrivacyPolicyAcceptance::class);
    }
}
