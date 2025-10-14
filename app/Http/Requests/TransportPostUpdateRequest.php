<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @property string $postId
 * @property string $stopId
 */
class TransportPostUpdateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'postId' => 'required|exists:posts,id',
            'stopId' => 'required|exists:transport_trip_stops,id',
        ];
    }
}
