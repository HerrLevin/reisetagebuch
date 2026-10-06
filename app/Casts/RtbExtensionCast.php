<?php

declare(strict_types=1);

namespace App\Casts;

use App\Dto\ActivityPub\Extensions\RtbExtension;
use App\Dto\ActivityPub\Extensions\RtbExtensionFactory;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use Throwable;

/**
 * Transparently casts ActivityPubPost::extension_data between the JSON column and a
 * typed RtbExtension DTO, so application code only ever hands the DTO around and the
 * array/JSON representation only exists at this storage boundary.
 *
 * @implements CastsAttributes<RtbExtension|null, RtbExtension|array|null>
 */
class RtbExtensionCast implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): ?RtbExtension
    {
        if ($value === null) {
            return null;
        }

        $data = is_string($value) ? json_decode($value, true) : $value;

        try {
            return RtbExtensionFactory::fromArray($data);
        } catch (Throwable) {
            return null;
        }
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof RtbExtension) {
            return json_encode($value->toArray());
        }

        if (is_array($value)) {
            return json_encode($value);
        }

        throw new InvalidArgumentException('extension_data must be an RtbExtension DTO, an array, or null.');
    }
}
