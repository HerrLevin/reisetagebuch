<?php

namespace App\Http\Resources\PostTypes;

use App\Http\Resources\LocationDto;
use App\Models\Post;

class LocationPost extends BasePost
{
    public LocationDto $location;

    public function __construct(Post $post)
    {
        parent::__construct($post);
        $this->location = new LocationDto($post->locationPost->location);
    }

}
