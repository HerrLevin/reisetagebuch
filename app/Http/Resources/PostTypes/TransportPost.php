<?php

namespace App\Http\Resources\PostTypes;

use App\Http\Resources\StopDto;
use App\Http\Resources\TripDto;
use App\Http\Resources\UserDto;
use App\Models\Post;

class TransportPost extends BasePost
{
    public StopDto $originStop;

    public StopDto $destinationStop;

    public TripDto $trip;

    public function __construct(Post $post, UserDto $userDto)
    {
        parent::__construct($post, $userDto);
        $asdf = $post->transportPost->originStop;
        $this->originStop = new StopDto($asdf);
        $this->destinationStop = new StopDto($post->transportPost->destinationStop);
        $this->trip = new TripDto($post->transportPost->transportTrip);
    }
}
