<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @property float|null $latitude
 * @property float|null $longitude
 * @property string|null $modes
 * @property string|null $when
 * @property string|null $identifier
 */
class DeparturesRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule', 'array', 'string>
     */
    public function rules(): array
    {
        return [
            'latitude' => 'required_if:locationId,null', 'numeric',
            'longitude' => 'required_if:locationId,null', 'numeric',
            'identifier' => 'nullable', 'string',
            'modes' => 'nullable', 'string',
            'when' => 'nullable', 'date',
        ];
    }
}
