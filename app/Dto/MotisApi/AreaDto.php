<?php

namespace App\Dto\MotisApi;

use App\Models\LocationIdentifier;

class AreaDto
{
    public string $name;

    public int $adminLevel;

    public bool $matched;

    public ?bool $unique = null;

    public ?bool $default = null;

    public static function fromIdentifier(LocationIdentifier $identifier, bool $matched = false, bool $default = false): self
    {
        $dto = new self;
        $dto->setName($identifier->identifier)
            ->setAdminLevel(0)
            ->setMatched($matched)
            ->setDefault($default);

        return $dto;
    }

    public function setName(string $name): AreaDto
    {
        $this->name = $name;

        return $this;
    }

    public function setAdminLevel(int $adminLevel): AreaDto
    {
        $this->adminLevel = $adminLevel;

        return $this;
    }

    public function setMatched(bool $matched): AreaDto
    {
        $this->matched = $matched;

        return $this;
    }

    public function setUnique(?bool $unique): AreaDto
    {
        $this->unique = $unique;

        return $this;
    }

    public function setDefault(?bool $default): AreaDto
    {
        $this->default = $default;

        return $this;
    }
}
