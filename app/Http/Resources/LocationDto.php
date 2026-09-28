<?php

namespace App\Http\Resources;

use App\Models\Location;
use App\Services\LocationEmojiService;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'LocationDto',
    description: 'Location Data Object',
    required: ['id', 'name', 'latitude', 'longitude', 'distance', 'tags', 'identifiers', 'emoji', 'timezone'],
    type: 'object'
)]
class LocationDto
{
    #[OA\Property('id', description: 'Location ID', type: 'string', format: 'uuid')]
    public string $id;

    #[OA\Property('name', description: 'Name of the location', type: 'string')]
    public string $name;

    #[OA\Property('latitude', description: 'Latitude of the location', type: 'number', format: 'float')]
    public float $latitude;

    #[OA\Property('longitude', description: 'Longitude of the location', type: 'number', format: 'float')]
    public float $longitude;

    #[OA\Property('distance', description: 'Distance to the location in meters', type: 'integer', nullable: true)]
    public ?int $distance;

    #[OA\Property('timezone', description: 'IANA timezone identifier of the location, if known', type: 'string', nullable: true)]
    public ?string $timezone;

    #[OA\Property(
        property: 'identifiers',
        description: 'List of location identifiers',
        type: 'array',
        items: new OA\Items(ref: LocationIdentifierDto::class)
    )]
    /**
     * @var LocationIdentifierDto[]
     */
    public array $identifiers = [];

    #[OA\Property(
        property: 'tags',
        description: 'List of location tags',
        type: 'array',
        items: new OA\Items(ref: LocationTagDto::class)
    )]
    /**
     * @var LocationTagDto[]
     */
    public array $tags;

    #[OA\Property('emoji', description: 'Emoji representing the location based on its tags', type: 'string')]
    public string $emoji;

    public function __construct(?Location $location = null)
    {
        if ($location === null) {
            return;
        }

        $this->id = $location->id;
        $this->name = $location->name;
        $this->latitude = $location->location->getLatitude();
        $this->longitude = $location->location->getLongitude();
        $this->distance = $location->distance ? round($location->distance) : null;
        $this->timezone = $location->timezone;
        $this->tags = $location->tags->map(fn ($tag) => new LocationTagDto($tag))->toArray();
        $this->identifiers = $location->identifiers->map(fn ($identifier) => new LocationIdentifierDto($identifier))->toArray();
        $this->emoji = (new LocationEmojiService)->getEmojiFromTags($location->tags);
    }

    /**
     * Reconstructs a LocationDto from a federated rtbExtension payload (see
     * NoteHydrator::buildLocationCore() for the sender-side shape). distance is never
     * present remotely — it's a viewer-relative "distance to here" value that doesn't
     * survive federation.
     */
    public static function fromRemote(array $data): self
    {
        $dto = new self;
        $dto->id = (string) ($data['id'] ?? '');
        $dto->name = (string) ($data['name'] ?? '');
        $dto->latitude = (float) ($data['latitude'] ?? 0);
        $dto->longitude = (float) ($data['longitude'] ?? 0);
        $dto->distance = null;
        $dto->timezone = $data['timezone'] ?? null;
        $dto->tags = array_map(fn ($tag) => LocationTagDto::fromRemote($tag), $data['tags'] ?? []);
        $dto->identifiers = array_map(fn ($identifier) => LocationIdentifierDto::fromRemote($identifier), $data['identifiers'] ?? []);
        $dto->emoji = $data['emoji'] ?? '❔';

        return $dto;
    }
}
