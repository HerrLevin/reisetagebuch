<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'UpdateProfileRequest',
    description: 'Request to update user profile',
    required: ['name'],
    properties: [
        new OA\Property(
            property: 'name',
            description: 'Full name of the user',
            type: 'string',
            maxLength: 255
        ),
        new OA\Property(
            property: 'bio',
            description: 'Biography of the user',
            type: 'string',
            maxLength: 500,
            nullable: true
        ),
        new OA\Property(
            property: 'website',
            description: 'Website URL of the user',
            type: 'string',
            format: 'uri',
            maxLength: 255,
            nullable: true
        ),
        new OA\Property(
            property: 'avatar',
            description: 'Avatar image file upload',
            type: 'string',
            format: 'binary',
            nullable: true
        ),
        new OA\Property(
            property: 'header',
            description: 'Header image file upload',
            type: 'string',
            format: 'binary',
            nullable: true
        ),
        new OA\Property(
            property: 'deleteAvatar',
            description: 'Flag to delete existing avatar',
            type: 'boolean',
            nullable: true
        ),
        new OA\Property(
            property: 'deleteHeader',
            description: 'Flag to delete existing header',
            type: 'boolean',
            nullable: true
        ),
    ],
    type: 'object'
)]

/**
 * @property string $name
 * @property string|null $bio
 * @property string|null $website
 * @property string|null $avatar
 * @property string|null $header
 * @property bool|null $deleteAvatar
 * @property bool|null $deleteHeader
 */
class UpdateProfileRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'avatar' => ['nullable', File::image()->max('2mb')],
            'deleteAvatar' => 'nullable|boolean',
            'header' => ['nullable', File::image()->max('2mb')],
            'deleteHeader' => 'nullable|boolean',
            'name' => 'required|string|max:255',
            'bio' => 'nullable|string|max:500',
            'website' => 'nullable|url|max:255',
        ];
    }
}
