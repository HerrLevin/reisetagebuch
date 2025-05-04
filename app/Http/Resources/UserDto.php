<?php

namespace App\Http\Resources;

class UserDto
{
    public string $id;
    public string $name;
    public string $username;
    public string $createdAt;

    public function setId(string $id): UserDto
    {
        $this->id = $id;
        return $this;
    }

    public function setName(string $name): UserDto
    {
        $this->name = $name;
        return $this;
    }

    public function setUsername(string $username): UserDto
    {
        $this->username = $username;
        return $this;
    }

    public function setCreatedAt(string $created_at): UserDto
    {
        $this->createdAt = $created_at;
        return $this;
    }
}
