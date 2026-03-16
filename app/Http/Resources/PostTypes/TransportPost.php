<?php

namespace App\Http\Resources\PostTypes;

use App\Enums\PostMetaInfo\MetaInfoKey;
use App\Enums\PostMetaInfo\TravelReason;
use App\Http\Resources\StopDto;
use App\Http\Resources\TripDto;
use App\Http\Resources\UserDto;
use App\Models\Post;
use Clickbar\Magellan\IO\Generator\Geojson\GeojsonGenerator;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'TransportPost',
    description: 'Transport Post Resource',
    required: ['originStop', 'destinationStop', 'trip', 'travelReason', 'manualDepartureTime', 'manualArrivalTime', 'distance', 'duration', 'userGeometry'],
    type: 'object'
)]
class TransportPost extends BasePost
{
    #[OA\Property(
        property: 'originStop',
        ref: StopDto::class,
        description: 'Origin stop details of the transport post'
    )]
    public StopDto $originStop;

    #[OA\Property(
        property: 'destinationStop',
        ref: StopDto::class,
        description: 'Destination stop details of the transport post'
    )]
    public StopDto $destinationStop;

    #[OA\Property(
        property: 'trip',
        ref: TripDto::class,
        description: 'Trip details associated with the transport post'
    )]
    public TripDto $trip;

    #[OA\Property(
        property: 'manualDepartureTime',
        description: 'Manually specified departure time in ISO 8601 format',
        type: 'string',
        format: 'date-time',
        nullable: true
    )]
    public ?string $manualDepartureTime = null;

    #[OA\Property(
        property: 'manualArrivalTime',
        description: 'Manually specified arrival time in ISO 8601 format',
        type: 'string',
        format: 'date-time',
        nullable: true
    )]
    public ?string $manualArrivalTime = null;

    #[OA\Property(
        property: 'travelReason',
        ref: TravelReason::class,
        description: 'Reason for travel associated with the transport post',
        nullable: true
    )]
    public ?TravelReason $travelReason;

    #[OA\Property(
        property: 'distance',
        description: 'Distance traveled in meters',
        type: 'integer'
    )]
    public int $distance;

    #[OA\Property(
        property: 'duration',
        description: 'Duration of the trip in seconds',
        type: 'integer'
    )]
    public int $duration;

    #[OA\Property(
        property: 'userGeometry',
        description: 'User-uploaded track geometry as GeoJSON',
        type: 'object',
        nullable: true
    )]
    public ?array $userGeometry = null;

    public function __construct(Post $post, UserDto $userDto, bool $withGeometry = false)
    {
        parent::__construct($post, $userDto);
        $asdf = $post->transportPost->originStop;
        $this->originStop = new StopDto($asdf);
        $this->destinationStop = new StopDto($post->transportPost->destinationStop);
        $this->trip = new TripDto($post->transportPost->transportTrip);
        $this->manualDepartureTime = $post->transportPost->manual_departure?->toIso8601String();
        $this->manualArrivalTime = $post->transportPost->manual_arrival?->toIso8601String();
        $this->travelReason = TravelReason::tryFrom($post->metaInfos->where('key', MetaInfoKey::TRAVEL_REASON)->first()?->value);
        $this->distance = $post->transportPost->distance;
        $this->duration = $post->transportPost->duration;

        if ($withGeometry && $post->transportPost->user_geometry !== null) {
            $this->userGeometry = (new GeojsonGenerator)->generate($post->transportPost->user_geometry);
        }
    }
}
