<?php

namespace App\Http\Requests;

use App\Enums\PostMetaInfo\TravelReason;
use App\Enums\Visibility;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MassEditPostRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'postIds' => ['required', 'array', 'min:1', 'max:100'],
            'postIds.*' => ['required', 'uuid', 'exists:posts,id'],
            'visibility' => ['nullable', Rule::enum(Visibility::class)],
            'travelReason' => ['nullable', Rule::enum(TravelReason::class)],
            'tags' => ['nullable', 'array', 'max:5'],
            'tags.*' => ['string', 'regex:/^[\w\_\- ]+$/'],
            'addTags' => ['boolean'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
