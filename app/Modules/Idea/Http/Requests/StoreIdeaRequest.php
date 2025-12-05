<?php

declare(strict_types=1);

namespace App\Modules\Idea\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreIdeaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'workspace_id' => ['nullable', 'exists:workspaces,id'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string', 'max:50000'],
            'priority' => ['nullable', 'in:low,medium,high'],
            'member_ids' => ['nullable', 'array'],
            'member_ids.*' => ['integer', 'exists:users,id'],
            'guest_ids' => ['nullable', 'array'],
            'guest_ids.*' => ['integer', 'exists:users,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Please provide a title for your idea.',
            'title.max' => 'The title cannot exceed 255 characters.',
            'short_description.max' => 'The short description cannot exceed 500 characters.',
        ];
    }
}
