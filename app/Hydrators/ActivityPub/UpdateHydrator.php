<?php

namespace App\Hydrators\ActivityPub;

use App\Dto\ActivityPub\Activities\Update;
use App\Dto\ActivityPub\Objects\BaseObject;

class UpdateHydrator
{
    public function hydrate(string $actor, BaseObject $object, bool $context = false): Update
    {
        $update = new Update;
        $update->id = $object->id.'/activity';
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
