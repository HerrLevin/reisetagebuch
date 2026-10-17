<?php

declare(strict_types=1);

namespace App\Dto\ActivityPub\Extensions;

/**
 * A reisetagebuch-to-reisetagebuch structured extension payload attached to an
 * ActivityPub Note (see Note::$rtbExtension). Concrete implementations are versioned,
 * per-post-type DTOs (RtbLocationExtension, RtbTransportExtension) rather than raw
 * arrays, so the shape is enforced by the type system everywhere except at the actual
 * wire/storage boundary, where toArray() produces the JSON representation.
 */
interface RtbExtension
{
    public function toArray(): array;
}
