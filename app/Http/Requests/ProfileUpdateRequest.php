<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ProfileUpdateRequest',
    title: 'Profile Update Request',
    description: 'Request schema for updating user profile information',
    required: ['name', 'username', 'email'],
    properties: [
        new OA\Property(
            property: 'name',
            description: 'The full name of the user',
            type: 'string',
            maxLength: 255,
            example: 'John Doe'
        ),
        new OA\Property(
            property: 'username',
            description: 'The unique username for the user (lowercase, alphanumeric, dashes, and underscores only)',
            type: 'string',
            maxLength: 30,
            example: 'john_doe'
        ),
        new OA\Property(
            property: 'email',
            description: 'The email address of the user (lowercase)',
            type: 'string',
            format: 'email',
            maxLength: 255,
            example: 'mail@example.com'
        ),
    ]
)]
class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'username' => [
                'required',
                'string',
                'lowercase',
                'alpha_dash',
                'max:30',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
        ];
    }
}
