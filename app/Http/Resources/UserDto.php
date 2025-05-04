<?php

declare(strict_types=1);

namespace App\Http\Resources;

class UserDto
{
    public string $id;
    public string $name;
    public string $username;
    public ?string $avatar = null;
    public ?string $bio = null;
    public ?string $website = null;
    public string $createdAt;
}
