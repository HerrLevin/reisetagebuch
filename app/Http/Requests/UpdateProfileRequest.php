<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

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
