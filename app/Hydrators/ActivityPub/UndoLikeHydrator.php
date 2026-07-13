<?php

declare(strict_types=1);

namespace App\Hydrators\ActivityPub;

use App\Dto\ActivityPub\Activities\Undo;

class UndoLikeHydrator
{
    public function hydrate(string $undoActivityId, string $actorUrl, string $likeActivityId, string $objectId): Undo
    {
        $undo = new Undo;
        $undo->id = $undoActivityId;
        $undo->actor = $actorUrl;
        $undo->object = [
            'id' => $likeActivityId,
            'type' => 'Like',
            'actor' => $actorUrl,
            'object' => $objectId,
        ];
        $undo->setContext();

        return $undo;
    }
}
