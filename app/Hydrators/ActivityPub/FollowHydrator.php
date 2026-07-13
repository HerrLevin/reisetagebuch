<?php

declare(strict_types=1);

namespace App\Hydrators\ActivityPub;

use App\Dto\ActivityPub\Activities\Follow;

class FollowHydrator
{
    public function hydrate(string $followActivityId, string $actorUrl, string $remoteActorId): Follow
    {
        $follow = new Follow;
        $follow->id = $followActivityId;
        $follow->actor = $actorUrl;
        $follow->object = $remoteActorId;
        $follow->setContext();

        return $follow;
    }
}
