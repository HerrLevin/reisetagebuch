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
    required: ['location', 'travelReason'],
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

    public function __construct(Post $post, UserDto $userDto)
    {
        parent::__construct($post, $userDto);
        $this->location = new LocationDto($post->locationPost->location);
        $this->travelReason = TravelReason::tryFrom($post->metaInfos->where('key', MetaInfoKey::TRAVEL_REASON)->first()?->value);
    }
}
