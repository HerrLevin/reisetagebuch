<?php

namespace App\Http\Resources\PostTypes;

use App\Enums\PostMetaInfo\MetaInfoKey;
use App\Enums\PostMetaInfo\TravelReason;
use App\Http\Resources\LocationDto;
use App\Http\Resources\UserDto;
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

    public function __construct(Post $post, UserDto $userDto)
    {
        parent::__construct($post, $userDto);
        $this->location = new LocationDto($post->locationPost->location);
        $this->travelReason = TravelReason::tryFrom($post->metaInfos->where('key', MetaInfoKey::TRAVEL_REASON)->first()?->value);
        $this->visitedAt = $post->locationPost->visited_at?->toIso8601String();
        $this->updatedAt = $this->getUpdatedAt($post)?->toIso8601String();
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
        if (! empty($this->location->tags['addr:city'])) {
            $location .= ', '.$this->location->tags['addr:city'];
        }

        if ($parentBody !== null) {
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
