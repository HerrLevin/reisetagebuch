<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ImageUploadRequest',
    description: 'Request to upload an image',
    required: ['name'],
    properties: [
        new OA\Property(
            property: 'image',
            description: 'Image file to upload (max 2MB)',
            type: 'string',
            format: 'binary',
            nullable: false
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
class ImageUploadRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'image' => ['required', File::image()->max('2mb')],
        ];
    }
}
