<?php

namespace App\Http\Resources\PostTypes;

use App\Http\Resources\LocationDto;
use App\Http\Resources\UserDto;
use App\Models\Post;

class TransportPost extends BasePost
{
    public LocationDto $start;
    public LocationDto $stop;
    public string $start_time;
    public string $stop_time;
    public string $mode;
    public string $line;

    public function __construct(Post $post, UserDto $userDto) {
        parent::__construct($post, $userDto);
        $this->start = new LocationDto($post->transportPost->originStop->location);
        $this->stop = new LocationDto($post->transportPost->destinationStop->location);
        $this->start_time = $post->transportPost->originStop->departure_time->toIso8601String();
        $this->stop_time = $post->transportPost->destinationStop->arrival_time->toIso8601String();
        $this->mode = $post->transportPost->transportTrip->mode;
        $this->line = $post->transportPost->transportTrip->line_name;
    }
}
