<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityPubServer extends Model
{
    protected $primaryKey = 'host';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = ['host', 'first_seen_at', 'last_seen_at'];

    protected function casts(): array
    {
        return [
            'first_seen_at' => 'datetime',
            'last_seen_at' => 'datetime',
        ];
    }

    public static function recordSeen(string $actorUri): void
    {
        $host = strtolower((string) parse_url($actorUri, PHP_URL_HOST));
        if ($host === '') {
            return;
        }

        $now = now();
        static::upsert([['host' => $host, 'first_seen_at' => $now, 'last_seen_at' => $now]], ['host'], ['last_seen_at']);
    }
}
