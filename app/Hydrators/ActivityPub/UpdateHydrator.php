<?php

namespace App\Hydrators\ActivityPub;

use App\Dto\ActivityPub\Activities\Update;
use App\Dto\ActivityPub\Objects\BaseObject;
use Illuminate\Support\Str;

class UpdateHydrator
{
    public function hydrate(string $actor, BaseObject $object, bool $context = false): Update
    {
        $update = new Update;
        // Must differ both from the object's Create activity id and from every other
        // Update of the same object — otherwise a receiver's activity-id dedup (keyed
        // on activity_id + actor_id, not activity_type) silently drops every Update
        // after the first one it ever sees for that object. A fresh id per call (rather
        // than deriving one from e.g. Note::$updated) also avoids same-second edits
        // colliding, since that timestamp only has second-level precision.
        $update->id = $object->id.'/activity/updates#'.(string) Str::uuid();
        $update->actor = $actor;
        $update->published = $object->published;
        $update->to = $object->to ?? ['https://www.w3.org/ns/activitystreams#Public'];
        $update->cc = $object->cc ?? [];
        $update->object = $object;

        if ($context) {
            $update->setContext();
        }

        return $update;
    }
}
