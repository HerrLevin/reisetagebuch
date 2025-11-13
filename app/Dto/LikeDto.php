<?php

namespace App\Dto;

readonly class LikeDto
{
    public bool $likedByUser;

    public int $likeCount;

    public function __construct(bool $likedByUser, int $likeCount)
    {
        $this->likedByUser = $likedByUser;
        $this->likeCount = $likeCount;
    }
}
