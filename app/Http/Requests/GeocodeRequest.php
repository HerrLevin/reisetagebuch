<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @property string $query
 * @property float|null $latitude
 * @property float|null $longitude
 */
class GeocodeRequest extends FormRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule', 'array', 'string>
     */
    public function rules(): array
    {
        return [
            'query' => 'required', 'string', 'max:255',
            'latitude' => 'nullable', 'numeric',
            'longitude' => 'nullable', 'numeric',
        ];
    }
}
