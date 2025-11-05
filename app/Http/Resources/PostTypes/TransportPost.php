<?php

namespace App\Http\Resources\PostTypes;

use App\Enums\PostMetaInfo\MetaInfoKey;
use App\Enums\PostMetaInfo\TravelReason;
use App\Http\Resources\StopDto;
use App\Http\Resources\TripDto;
use App\Http\Resources\UserDto;
use App\Models\Post;

class TransportPost extends BasePost
{
    public StopDto $originStop;

    public StopDto $destinationStop;

    public TripDto $trip;

    public ?string $manualDepartureTime = null;

    public ?string $manualArrivalTime = null;

    public ?TravelReason $travelReason;

    public function __construct(Post $post, UserDto $userDto)
    {
        parent::__construct($post, $userDto);
        $asdf = $post->transportPost->originStop;
        $this->originStop = new StopDto($asdf);
        $this->destinationStop = new StopDto($post->transportPost->destinationStop);
        $this->trip = new TripDto($post->transportPost->transportTrip);
        $this->manualDepartureTime = $post->transportPost->manual_departure?->toIso8601String();
        $this->manualArrivalTime = $post->transportPost->manual_arrival?->toIso8601String();
        $this->travelReason = TravelReason::tryFrom($post->metaInfos->where('key', MetaInfoKey::TRAVEL_REASON)->first()?->value);
    }
}
