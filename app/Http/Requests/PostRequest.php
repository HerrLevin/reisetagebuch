<?php

namespace App\Http\Requests;

use App\Enums\Visibility;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PostRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'id' => ['sometimes', 'uuid', ' exists:posts,id'],
            'body' => ['required_without:id', 'string', 'max:255'],
            'visibility' => ['required_without:id', Rule::enum(Visibility::class)],
        ];
    }
}
