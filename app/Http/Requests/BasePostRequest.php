<?php

namespace App\Http\Requests;

use App\Enums\PostMetaInfo\TravelReason;
use App\Enums\Visibility;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BasePostRequest extends FormRequest
{
    protected array $extraBodyRules = ['required_without:id'];

    public function rules(): array
    {
        return [
            'id' => ['sometimes', 'uuid', ' exists:posts,id'],
            'body' => $this->bodyRule(),
            'visibility' => ['required_without:id', Rule::enum(Visibility::class)],
            'tags' => ['sometimes', 'max:5', 'array'],
            'tags.*' => ['string', 'regex:/^[\w\_\- ]+$/'],
            'travelReason' => Rule::enum(TravelReason::class),
            'vehicleIds' => ['sometimes', 'array'],
            'vehicleIds.*' => ['string'],
        ];
    }

    protected function bodyRule(): array
    {
        $baseRules = ['string', 'max:255'];

        return array_merge($baseRules, $this->extraBodyRules);
    }
}
