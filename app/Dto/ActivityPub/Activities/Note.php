<?php

namespace App\Dto\ActivityPub\Activities;

use App\Dto\ActivityPub\Objects\BaseObject;

class Note extends BaseObject
{
    public readonly string $type;

    public string $attributedTo;

    public string $content;

    public array $to;

    public array $cc;

    public string $published;

    public function __construct()
    {
        $this->type = 'Note';
    }
}
