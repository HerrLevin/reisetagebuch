<?php

namespace App\Http\Resources\PostTypes;

use App\Http\Resources\LocationDto;
use App\Http\Resources\UserDto;
use App\Models\Post;

class LocationPost extends BasePost
{
    public LocationDto $location;

    public function __construct(Post $post, UserDto $userDto)
    {
        parent::__construct($post, $userDto);
        $this->location = new LocationDto($post->locationPost->location);
    }

}
