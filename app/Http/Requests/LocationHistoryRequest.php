<?php

namespace App\Http\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @property ?Carbon $when
 */
class LocationHistoryRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'when' => ['sometimes', Rule::date()->format('Y-m-d')],
        ];
    }
}
