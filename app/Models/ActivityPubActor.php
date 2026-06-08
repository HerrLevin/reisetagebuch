<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityPubActor extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'actor_uri',
        'preferred_username',
        'display_name',
        'profile_url',
        'inbox_url',
        'shared_inbox_url',
        'remote_icon_url',
        'local_icon_path',
        'icon_mime_type',
        'icon_etag',
        'icon_fetched_at',
        'profile_fetched_at',
    ];

    protected function casts(): array
    {
        return [
            'icon_fetched_at' => 'datetime',
            'profile_fetched_at' => 'datetime',
        ];
    }

    protected function localIconUrl(): Attribute
    {
        return Attribute::get(function (): ?string {
            if ($this->local_icon_path) {
                return url('/files/'.$this->local_icon_path);
            }

            return null;
        });
    }
}
