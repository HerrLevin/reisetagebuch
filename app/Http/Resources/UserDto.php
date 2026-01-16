<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Traits\JsonResponseObject;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'UserDto',
    description: 'User Data Object',
    required: ['id', 'name', 'username', 'avatar', 'header', 'bio', 'website', 'createdAt'],
    type: 'object'
)]
class UserDto
{
    use JsonResponseObject;

    #[OA\Property('id', description: 'User ID', type: 'string', format: 'uuid')]
    public string $id;

    #[OA\Property('name', description: 'Full name of the user', type: 'string')]
    public string $name;

    #[OA\Property('username', description: 'Username of the user', type: 'string')]
    public string $username;

    #[OA\Property('avatar', description: 'URL of the user avatar image', type: 'string', format: 'uri', nullable: true)]
    public ?string $avatar = null;

    #[OA\Property('header', description: 'URL of the user header image', type: 'string', format: 'uri', nullable: true)]
    public ?string $header = null;

    #[OA\Property('bio', description: 'Biography of the user', type: 'string', nullable: true)]
    public ?string $bio = null;

    #[OA\Property('website', description: 'Website URL of the user', type: 'string', format: 'uri', nullable: true)]
    public ?string $website = null;

    #[OA\Property('createdAt', description: 'Account creation timestamp', type: 'string', format: 'date-time')]
    public string $createdAt;
}
