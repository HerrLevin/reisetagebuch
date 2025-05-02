<?php

namespace App\Http\Resources\PostTypes;

class UserDto
{
    public string $id;
    public string $name;

    public function __construct(string $id, string $name)
    {
        $this->id = $id;
        $this->name = $name;
    }

    public static function fromUser(UserDto $user): self
    {
        return new self($user->id, $user->name);
    }
}
