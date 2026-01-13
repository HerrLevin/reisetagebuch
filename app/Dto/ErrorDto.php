<?php

namespace App\Dto;

use App\Traits\JsonResponseObject;

class ErrorDto
{
    use JsonResponseObject;

    public function __construct(
        public string $message,
        public ?array $errors = null,
    ) {}
}
