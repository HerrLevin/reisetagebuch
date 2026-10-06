<?php

declare(strict_types=1);

namespace App\Dto\ActivityPub\Extensions;

/**
 * Single dispatch point for turning a raw rtbExtension envelope (untrusted JSON from a
 * federated Note, or our own stored JSON column) into the right typed RtbExtension DTO.
 * Shared by ActivityPubExtensionParser (incoming Notes) and RtbExtensionCast (DB reads)
 * so envelope-level version/postType validation lives in exactly one place.
 */
final class RtbExtensionFactory
{
    public const int CURRENT_VERSION = 1;

    public static function fromArray(mixed $data): ?RtbExtension
    {
        if (! is_array($data)) {
            return null;
        }

        if (($data['rtbVersion'] ?? null) !== self::CURRENT_VERSION) {
            return null;
        }

        return match ($data['postType'] ?? null) {
            RtbLocationExtension::POST_TYPE => RtbLocationExtension::fromArray($data),
            RtbTransportExtension::POST_TYPE => RtbTransportExtension::fromArray($data),
            default => null,
        };
    }
}
