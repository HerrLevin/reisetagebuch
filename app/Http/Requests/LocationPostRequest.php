<?php

namespace App\Http\Requests;

use App\Enums\Visibility;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LocationPostRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'body' => 'nullable|string|max:255',
            'visibility' => 'required', Rule::enum(Visibility::class),
            'location' => 'required|exists:locations,id',
        ];
    }
}
