<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @property string $from
 * @property string $to
 */
class FromToRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'from' => 'required|string',
            'to' => 'required|string',
        ];
    }
}
