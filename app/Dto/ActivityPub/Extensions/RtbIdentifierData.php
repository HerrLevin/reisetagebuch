<?php

declare(strict_types=1);

namespace App\Dto\ActivityPub\Extensions;

use App\Dto\ActivityPub\Extensions\Concerns\ParsesUntrustedFields;
use App\Http\Resources\LocationIdentifierDto;

final class RtbIdentifierData
{
    use ParsesUntrustedFields;

    public function __construct(
        public readonly string $type,
        public readonly string $origin,
        public readonly string $identifier,
    ) {}

    public function toArray(): array
    {
        return ['type' => $this->type, 'origin' => $this->origin, 'identifier' => $this->identifier];
    }

    public static function fromDto(LocationIdentifierDto $identifier): self
    {
        return new self($identifier->type, $identifier->origin, $identifier->identifier);
    }

    public static function fromArray(mixed $data): ?self
    {
        if (! is_array($data)) {
            return null;
        }

        $type = self::parseString($data['type'] ?? null);
        $origin = self::parseString($data['origin'] ?? null);
        $identifier = self::parseString($data['identifier'] ?? null);
        if ($type === null || $origin === null || $identifier === null) {
            return null;
        }

        return new self($type, $origin, $identifier);
    }
}
