<?php

namespace App\Hydrators\ActivityPub;

use App\Dto\ActivityPub\Activities\Delete;
use App\Dto\ActivityPub\Objects\BaseObject;

class DeleteHydrator
{
    public function hydrate(string $actor, BaseObject $object, bool $context = false): Delete
    {
        $delete = new Delete;
        $delete->id = $object->id.'/activity';
        $delete->actor = $actor;
        $delete->to = $object->to ?? ['https://www.w3.org/ns/activitystreams#Public'];
        $delete->object = $object;

        if ($context) {
            $delete->setContext();
        }

        return $delete;
    }
}
