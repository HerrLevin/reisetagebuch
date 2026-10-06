<?php

declare(strict_types=1);

namespace App\Dto\ActivityPub\Extensions;

use App\Dto\ActivityPub\Extensions\Concerns\ParsesUntrustedFields;
use App\Http\Resources\LocationTagDto;

final class RtbTagData
{
    use ParsesUntrustedFields;

    public function __construct(
        public readonly string $key,
        public readonly string $value,
    ) {}

    public function toArray(): array
    {
        return ['key' => $this->key, 'value' => $this->value];
    }

    public static function fromDto(LocationTagDto $tag): self
    {
        return new self($tag->key, $tag->value);
    }

    public static function fromArray(mixed $data): ?self
    {
        if (! is_array($data)) {
            return null;
        }

        $key = self::parseString($data['key'] ?? null);
        $value = self::parseString($data['value'] ?? null);
        if ($key === null || $value === null) {
            return null;
        }

        return new self($key, $value);
    }
}
