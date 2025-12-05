<?php

declare(strict_types=1);

namespace App\Modules\Discussion\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDiscussionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'details' => ['nullable', 'string', 'max:50000'],
            'type' => ['nullable', 'in:general,announcement,question,feedback,brainstorm'],
            'workspace_id' => ['nullable', 'exists:workspaces,id'],
            'is_public' => ['nullable', 'boolean'],
            'member_ids' => ['nullable', 'array'],
            'member_ids.*' => ['integer', 'exists:users,id'],
            'guest_ids' => ['nullable', 'array'],
            'guest_ids.*' => ['integer', 'exists:users,id'],
            'attachments' => ['nullable', 'array'],
            'attachments.*' => ['file', 'max:10240'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_public' => $this->boolean('is_public'),
        ]);
    }
}
