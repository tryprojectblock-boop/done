<?php

declare(strict_types=1);

namespace App\Modules\Task\Http\Requests;

use App\Modules\Task\Enums\TaskPriority;
use App\Modules\Task\Enums\TaskType;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTaskRequest extends FormRequest
{
    private const MAX_FILE_SIZE_KB = 10240; // 10MB
    private const MAX_FILES_COUNT = 10;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'workspace_id' => ['required', 'exists:workspaces,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:65535'],
            'type' => ['nullable', 'array'],
            'type.*' => ['nullable', Rule::enum(TaskType::class)],
            'priority' => ['nullable', Rule::enum(TaskPriority::class)],
            'status_id' => ['nullable', 'exists:workflow_statuses,id'],
            'assignee_id' => ['nullable', 'exists:users,id'],
            'assignee_ids' => ['nullable', 'array'],
            'assignee_ids.*' => ['exists:users,id'],
            'due_date' => ['nullable', 'date'],
            'start_date' => ['nullable', 'date'],
            'parent_task_id' => ['nullable', 'exists:tasks,id'],
            'parent_link_notes' => ['nullable', 'string', 'max:255'],
            'estimated_time' => ['nullable', 'integer', 'min:0'],
            'milestone_id' => ['nullable', 'exists:milestones,id'],
            'tags' => ['nullable', 'string'], // JSON from Tagify
            'tag_ids' => ['nullable', 'array'],
            'tag_ids.*' => ['exists:tags,id'],
            'watcher_ids' => ['nullable', 'array'],
            'watcher_ids.*' => ['exists:users,id'],
            'is_private' => ['nullable', 'boolean'],
            'files' => ['nullable', 'array', 'max:' . self::MAX_FILES_COUNT],
            'files.*' => ['file', 'max:' . self::MAX_FILE_SIZE_KB], // 10MB max per file
            'action' => ['nullable', 'string', 'in:create,create_and_add_more,create_and_copy'],
            'notify_option' => ['nullable', 'string', 'in:all,selected,none'],
            'notify_users' => ['nullable', 'array'],
            'notify_users.*' => ['exists:users,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'workspace_id.required' => 'Please select a workspace.',
            'workspace_id.exists' => 'The selected workspace is invalid.',
            'title.required' => 'Please enter a task title.',
            'title.max' => 'The task title cannot exceed 255 characters.',
            'files.*.max' => 'Each file must be less than 10MB.',
            'files.max' => 'You can upload a maximum of ' . self::MAX_FILES_COUNT . ' files.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            // Pre-check Content-Length header (defense in depth)
            $contentLength = $this->header('Content-Length');
            $maxContentLength = self::MAX_FILE_SIZE_KB * 1024 * self::MAX_FILES_COUNT;
            if ($contentLength !== null && (int) $contentLength > $maxContentLength) {
                $validator->errors()->add('files', 'Request size exceeds the maximum allowed size.');
            }

            // Post-check: Verify actual file sizes (defense in depth)
            if ($this->hasFile('files')) {
                foreach ($this->file('files') as $file) {
                    if ($file->getSize() > self::MAX_FILE_SIZE_KB * 1024) {
                        $validator->errors()->add('files', 'One or more files exceed the maximum allowed size of 10MB.');
                        break;
                    }
                }
            }
        });
    }
}
