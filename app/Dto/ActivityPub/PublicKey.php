<?php

declare(strict_types=1);

namespace App\Dto\ActivityPub;

use App\Traits\JsonResponseObject;

class PublicKey
{
    use JsonResponseObject;

    public string $id;

    public string $owner;

    public string $publicKeyPem;
}
