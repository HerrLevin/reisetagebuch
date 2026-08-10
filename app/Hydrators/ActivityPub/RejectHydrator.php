<?php

namespace App\Hydrators\ActivityPub;

use App\Dto\ActivityPub\Activities\Reject;
use App\Dto\ActivityPub\Objects\BaseObject;
use App\Http\Resources\UserDto;

class RejectHydrator
{
    public function hydrate(string $rejectId, UserDto $user, BaseObject|array $object): object
    {
        $reject = new Reject;

        $reject->id = $rejectId;
        $reject->actor = route('ap.actor', ['username' => $user->username]);
        $reject->object = $object;
        $reject->setContext();

        return $reject;
    }
}
