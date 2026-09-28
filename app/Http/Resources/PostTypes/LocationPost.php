<?php

namespace App\Http\Resources\PostTypes;

use App\Enums\PostMetaInfo\MetaInfoKey;
use App\Enums\PostMetaInfo\TravelReason;
use App\Http\Resources\LocationDto;
use App\Http\Resources\UserDto;
use App\Models\ActivityPubPost;
use App\Models\Post;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'LocationPost',
    description: 'Location Post Resource',
    required: ['location', 'travelReason', 'visitedAt'],
    type: 'object'
)]
class LocationPost extends BasePost
{
    #[OA\Property(
        property: 'location',
        ref: LocationDto::class,
        description: 'Location associated with the location post',
    )]
    public LocationDto $location;

    #[OA\Property(
        property: 'travelReason',
        ref: TravelReason::class,
        description: 'Reason for travel associated with the location post',
        nullable: true
    )]
    public ?TravelReason $travelReason;

    #[OA\Property(
        property: 'visitedAt',
        type: 'string',
        format: 'date-time',
        nullable: true
    )]
    public ?string $visitedAt;

    public function __construct(?Post $post = null, ?UserDto $userDto = null)
    {
        parent::__construct($post, $userDto);

        if ($post === null) {
            return;
        }

        $this->location = new LocationDto($post->locationPost->location);
        $this->travelReason = TravelReason::tryFrom($post->metaInfos->where('key', MetaInfoKey::TRAVEL_REASON)->first()?->value);
        $this->visitedAt = $post->locationPost->visited_at?->toIso8601String();
        $this->updatedAt = $this->getUpdatedAt($post)?->toIso8601String();
    }

    public static function fromRemote(ActivityPubPost $post, UserDto $userDto, array $data = []): static
    {
        $dto = parent::fromRemote($post, $userDto);
        // $data is the flat 'location' sub-payload itself (see NoteHydrator::buildLocationPayload()
        // — travelReason/visitedAt are siblings of the location-core fields, not nested under
        // another 'location' key), so LocationDto reads straight from $data.
        $dto->location = LocationDto::fromRemote($data);
        $dto->travelReason = isset($data['travelReason']) ? TravelReason::tryFrom($data['travelReason']) : null;
        $dto->visitedAt = $data['visitedAt'] ?? null;

        return $dto;
    }

    private function getUpdatedAt(Post $post)
    {
        if ($post->locationPost->updated_at === null) {
            return $post->updated_at;
        }

        if ($post->locationPost->updated_at->gte($post->updated_at)) {
            return $post->locationPost->updated_at;
        }

        return $post->updated_at;
    }

    public function getHtmlBody(): ?string
    {
        $parentBody = parent::getHtmlBody();
        $name = e($this->location->name);
        $emoji = $this->location->emoji;
        $location = "$emoji $name";
        $city = array_find($this->location->tags, fn ($tag) => $tag->key === 'addr:city');
        if (! empty($city)) {
            $location .= ', '.$city->value;
        }

        if (empty($parentBody)) {
            $body = trans('activitypub.location.base', ['location' => $location]);
        } else {
            $body = trans('activitypub.location.short', ['location' => $location]);
        }

        $body .= nl2br("\n".$this->travelReason?->getEmoji());

        return $parentBody ? nl2br($parentBody."\n\n").$body : $body;
    }

    public function getSummary(): ?string
    {
        return sprintf(
            '%s@ %s',
            $this->formattedBody(),
            $this->location->name
        );
    }
}
