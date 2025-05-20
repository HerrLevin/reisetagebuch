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
        $this->start = new LocationDto($post->transportPost->origin);
        $this->stop = new LocationDto($post->transportPost->destination);
        $this->start_time = $post->transportPost->departure->toIso8601String();
        $this->stop_time = $post->transportPost->arrival->toIso8601String();
        $this->mode = $post->transportPost->mode;
        $this->line = $post->transportPost->line;
    }
}
