<?php

namespace App\Hydrators\ActivityPub;

use App\Dto\ActivityPub\Activities\Delete;
use App\Dto\ActivityPub\Objects\BaseObject;

class DeleteHydrator
{
    public function hydrate(string $actor, BaseObject $object, bool $context = false): Delete
    {
        $delete = new Delete;
        // Must differ from the object's Create (and Update) activity id — a receiver's
        // activity-id dedup is keyed on activity_id + actor_id, not activity_type, so
        // reusing the bare object id here would make this Delete collide with (and get
        // silently dropped alongside) that object's earlier Create.
        $delete->id = $object->id.'/activity/delete';
        $delete->actor = $actor;
        $delete->to = $object->to ?? ['https://www.w3.org/ns/activitystreams#Public'];
        $delete->object = $object;

        if ($context) {
            $delete->setContext();
        }

        return $delete;
    }
}
