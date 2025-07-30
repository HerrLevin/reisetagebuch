<?php

namespace App\Http\Resources;

use App\Models\LocationIdentifier;

class LocationIdentifierDto
{
    public string $type;

    public string $origin;

    public string $identifier;

    public function __construct(LocationIdentifier $locationTag)
    {
        $this->type = $locationTag->type;
        $this->origin = $locationTag->origin;
        $this->identifier = $locationTag->identifier;
    }
}
