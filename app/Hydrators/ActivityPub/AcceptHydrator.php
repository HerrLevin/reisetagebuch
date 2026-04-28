<?php

namespace App\Hydrators\ActivityPub;

use App\Dto\ActivityPub\Activities\Accept;
use App\Dto\ActivityPub\Objects\BaseObject;
use App\Http\Resources\UserDto;

class AcceptHydrator
{
    public function hydrate(string $acceptId, UserDto $user, BaseObject|array $object): object
    {
        $accept = new Accept;

        $accept->id = $acceptId;
        $accept->actor = route('ap.actor', ['username' => $user->username]);
        $accept->object = $object;

        return $accept;
    }
}
