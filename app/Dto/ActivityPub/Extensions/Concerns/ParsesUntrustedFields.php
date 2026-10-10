<?php

declare(strict_types=1);

namespace App\Dto\ActivityPub\Extensions\Concerns;

use App\Services\ActivityPubContentSanitizer;
use BackedEnum;

/**
 * Shared defensive coercion helpers for building Rtb*Data DTOs from untrusted, federated
 * JSON. Every read degrades to null/empty rather than throwing — the caller decides
 * whether a missing/invalid field means "reject this whole object" (return null from its
 * own fromArray()) or "just leave this one field empty".
 */
trait ParsesUntrustedFields
{
    private static function parseString(mixed $value, int $maxLength = 500): ?string
    {
        if (! is_string($value) || $value === '') {
            return null;
        }

        return mb_substr($value, 0, $maxLength);
    }

    private static function parseSanitizedBody(mixed $value, int $maxLength = 500): ?string
    {
        $value = self::parseString($value, $maxLength);

        return $value !== null ? app(ActivityPubContentSanitizer::class)->sanitize($value) : null;
    }

    private static function parseFloat(mixed $value): ?float
    {
        return is_numeric($value) ? (float) $value : null;
    }

    private static function parseInt(mixed $value): ?int
    {
        return is_numeric($value) ? (int) $value : null;
    }

    /**
     * @template T of BackedEnum
     *
     * @param  class-string<T>  $enumClass
     * @return T|null
     */
    private static function parseEnum(mixed $value, string $enumClass): ?BackedEnum
    {
        if (! is_string($value)) {
            return null;
        }

        return $enumClass::tryFrom($value);
    }

    private static function parseGeometry(mixed $value, int $maxBytes = 200_000): ?array
    {
        if (! is_array($value)) {
            return null;
        }

        if (strlen(json_encode($value) ?: '') > $maxBytes) {
            return null;
        }

        return $value;
    }

    /**
     * @return array<int, mixed>
     */
    private static function parseBoundedList(mixed $value, callable $itemFactory, int $maxItems = 50): array
    {
        if (! is_array($value)) {
            return [];
        }

        $items = [];
        foreach (array_slice($value, 0, $maxItems) as $raw) {
            $item = $itemFactory($raw);
            if ($item !== null) {
                $items[] = $item;
            }
        }

        return $items;
    }
}
