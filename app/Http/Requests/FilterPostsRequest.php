<?php

namespace App\Http\Requests;

use App\Enums\PostMetaInfo\TravelReason;
use App\Enums\Visibility;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FilterPostsRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'dateFrom' => ['nullable', 'date'],
            'dateTo' => ['nullable', 'date', 'after_or_equal:dateFrom'],
            'visibility' => ['nullable', 'array'],
            'visibility.*' => [Rule::enum(Visibility::class)],
            'travelReason' => ['nullable', 'array'],
            'travelReason.*' => [Rule::enum(TravelReason::class)],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
