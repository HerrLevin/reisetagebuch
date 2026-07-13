<?php

declare(strict_types=1);

namespace App\Hydrators\ActivityPub;

use App\Dto\ActivityPub\Activities\Like;

class LikeHydrator
{
    public function hydrate(string $likeActivityId, string $actorUrl, string $objectId): Like
    {
        $like = new Like;
        $like->id = $likeActivityId;
        $like->actor = $actorUrl;
        $like->object = $objectId;
        $like->setContext();

        return $like;
    }
}
