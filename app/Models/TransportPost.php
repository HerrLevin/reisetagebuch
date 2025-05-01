<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $id
 * @property string $origin_id
 * @property string $destination_id
 * @property Carbon $departure
 * @property Carbon $arrival
 * @property int|null $departure_delay
 * @property int|null $arrival_delay
 * @property string $mode
 * @property string|null $line
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property-read string $post_id
 * @property-read Location $origin
 * @property-read Location $destination
 */
class TransportPost extends Model
{
    use HasUuids;

    protected $fillable = [
        'id',
        'post_id',
        'origin_id',
        'destination_id',
        'departure',
        'arrival',
        'mode',
        'line',
        'departure_delay',
        'arrival_delay'
    ];

    protected $casts = [
        'departure' => 'datetime',
        'arrival' => 'datetime',
        'departure_delay' => 'integer',
        'arrival_delay' => 'integer',
    ];

    public function origin(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'origin_id');
    }

    public function destination(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'destination_id');
    }
}
