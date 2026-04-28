<?php

namespace App\Hydrators\ActivityPub;

use App\Dto\ActivityPub\Activities\Create;
use App\Dto\ActivityPub\Objects\BaseObject;

class CreateHydrator
{
    public function hydrate(string $actor, BaseObject $object, bool $context = false): Create
    {
        $create = new Create;
        $create->id = $object->id.'/activity';
        $create->actor = $actor;
        $create->published = $object->published;
        $create->to = $object->to ?? ['https://www.w3.org/ns/activitystreams#Public'];
        $create->cc = $object->cc ?? [];
        $create->object = $object;

        if ($context) {
            $create->setContext();
        }

        return $create;
    }
}
