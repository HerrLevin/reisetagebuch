<?php

namespace App\Dto\ActivityPub\Objects;

class TombStone extends BaseObject
{
    public function __construct(string $id)
    {
        $this->id = $id;
        parent::__construct('Tombstone');
    }
}
