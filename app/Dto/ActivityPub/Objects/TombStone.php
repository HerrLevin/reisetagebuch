<?php

namespace App\Dto\ActivityPub\Objects;

class TombStone extends BaseObject
{
    public readonly string $type;

    public function __construct(string $id)
    {
        $this->id = $id;
        $this->type = 'Tombstone';
    }
}
