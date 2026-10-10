<?php

declare(strict_types=1);

namespace App\Dto\ActivityPub\Extensions;

use App\Dto\ActivityPub\Extensions\Concerns\ParsesUntrustedFields;
use App\Enums\PostMetaInfo\TravelReason;
use App\Http\Resources\PostTypes\LocationPost;

final class RtbLocationExtension implements RtbExtension
{
    use ParsesUntrustedFields;

    public const string POST_TYPE = 'location';

    public function __construct(
        public readonly int $rtbVersion,
        public readonly RtbLocationData $location,
        public readonly ?TravelReason $travelReason,
        public readonly ?string $visitedAt,
        public readonly ?string $body = null,
    ) {}

    public function toArray(): array
    {
        return [
            'rtbVersion' => $this->rtbVersion,
            'postType' => self::POST_TYPE,
            'body' => $this->body,
            // travelReason/visitedAt live alongside the location-core fields on the wire
            // (they describe the visit, not the place, but are post-level attributes with
            // nowhere else to sit in this envelope).
            'location' => [
                ...$this->location->toArray(),
                'travelReason' => $this->travelReason?->value,
                'visitedAt' => $this->visitedAt,
            ],
        ];
    }

    public static function fromPost(LocationPost $post): self
    {
        return new self(
            rtbVersion: RtbExtensionFactory::CURRENT_VERSION,
            location: RtbLocationData::fromDto($post->location),
            travelReason: $post->travelReason,
            visitedAt: $post->visitedAt,
            body: $post->body,
        );
    }

    /**
     * @param  array<string, mixed>  $data  the whole rtbExtension envelope
     */
    public static function fromArray(array $data): ?self
    {
        $locationRaw = $data['location'] ?? null;
        if (! is_array($locationRaw)) {
            return null;
        }

        $location = RtbLocationData::fromArray($locationRaw);
        if ($location === null) {
            return null;
        }

        return new self(
            rtbVersion: RtbExtensionFactory::CURRENT_VERSION,
            location: $location,
            travelReason: self::parseEnum($locationRaw['travelReason'] ?? null, TravelReason::class),
            visitedAt: self::parseString($locationRaw['visitedAt'] ?? null),
            body: self::parseSanitizedBody($data['body'] ?? null),
        );
    }
}
