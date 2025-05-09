<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LocationPostRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'body' => 'nullable|string|max:255',
            'location' => 'required|exists:locations,id',
        ];
    }
}
