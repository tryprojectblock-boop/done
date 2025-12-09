<?php

declare(strict_types=1);

namespace App\Modules\Document\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'content' => ['required', 'string', 'max:10000'],
            'selection_start' => ['nullable', 'integer', 'min:0'],
            'selection_end' => ['nullable', 'integer', 'min:0', 'gte:selection_start'],
            'selection_text' => ['nullable', 'string', 'max:1000'],
            'selection_id' => ['nullable', 'string', 'max:50'],
        ];
    }

    public function messages(): array
    {
        return [
            'content.required' => 'Comment content is required.',
            'content.max' => 'Comment cannot exceed 10,000 characters.',
            'selection_end.gte' => 'Selection end must be greater than or equal to selection start.',
        ];
    }
}
