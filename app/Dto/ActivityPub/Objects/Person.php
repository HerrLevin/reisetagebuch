<?php

namespace App\Dto\ActivityPub\Objects;

use App\Dto\ActivityPub\Image;
use App\Dto\ActivityPub\PublicKey;

class Person extends BaseObject
{
    public readonly string $type;

    public string $following;

    public string $followers;

    public string $inbox;

    public string $outbox;

    public string $preferredUsername;

    public string $name;

    /** @var string Biography of the user */
    public string $summary;

    public string $url;

    public bool $manuallyApprovesFollowers;

    public bool $discoverable;

    public string $published;

    public array $endpoints;

    /** @var Image Avatar of the user */
    public Image $icon;

    /** @var Image header of the user */
    public Image $image;

    public PublicKey $publicKey;

    public function __construct()
    {
        $this->type = 'Person';
    }
}
