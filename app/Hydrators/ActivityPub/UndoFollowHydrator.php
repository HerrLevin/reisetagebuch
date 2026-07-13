<?php

declare(strict_types=1);

namespace App\Hydrators\ActivityPub;

use App\Dto\ActivityPub\Activities\Undo;

class UndoFollowHydrator
{
    public function hydrate(string $undoActivityId, string $actorUrl, string $followActivityId, string $remoteActorId): Undo
    {
        $undo = new Undo;
        $undo->id = $undoActivityId;
        $undo->actor = $actorUrl;
        $undo->object = [
            'id' => $followActivityId,
            'type' => 'Follow',
            'actor' => $actorUrl,
            'object' => $remoteActorId,
        ];
        $undo->setContext();

        return $undo;
    }
}
