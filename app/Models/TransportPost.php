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
 * @property int $departure_delay
 * @property int $arrival_delay
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property-read Location $origin
 * @property-read Location $destination
 */
class TransportPost extends Model
{
    use HasUuids;

    protected $fillable = [
        'id',
        'origin_id',
        'destination_id',
        'departure',
        'arrival',
        'departure_delay',
        'arrival_delay'
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
