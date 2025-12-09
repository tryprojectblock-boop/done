<?php

declare(strict_types=1);

namespace App\Modules\Discussion\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class UpdateDiscussionRequest extends FormRequest
{
    private const MAX_ATTACHMENT_SIZE_KB = 10240; // 10MB
    private const MAX_ATTACHMENTS_COUNT = 10;

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
            'attachments' => ['nullable', 'array', 'max:' . self::MAX_ATTACHMENTS_COUNT],
            'attachments.*' => ['file', 'max:' . self::MAX_ATTACHMENT_SIZE_KB],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_public' => $this->boolean('is_public'),
        ]);
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            // Pre-check Content-Length header (defense in depth)
            $contentLength = $this->header('Content-Length');
            $maxContentLength = self::MAX_ATTACHMENT_SIZE_KB * 1024 * self::MAX_ATTACHMENTS_COUNT;
            if ($contentLength !== null && (int) $contentLength > $maxContentLength) {
                $validator->errors()->add('attachments', 'Request size exceeds the maximum allowed size.');
            }

            // Post-check: Verify actual file sizes (defense in depth)
            if ($this->hasFile('attachments')) {
                foreach ($this->file('attachments') as $file) {
                    if ($file->getSize() > self::MAX_ATTACHMENT_SIZE_KB * 1024) {
                        $validator->errors()->add('attachments', 'One or more attachments exceed the maximum allowed size of 10MB.');
                        break;
                    }
                }
            }
        });
    }
}
