<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @property-read float $latitude
 * @property-read float $longitude
 * @property-read string|null $query
 */
class LocationQueryRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'query' => 'nullable|string|max:255',
        ];
    }
}
