<?php

namespace App\Http\Requests;

class LocationBasePostRequest extends BasePostRequest
{
    public function rules(): array
    {
        $this->extraBodyRules = ['nullable'];

        return array_merge([
            'location' => 'required|exists:locations,id',
        ], parent::rules());
    }
}
