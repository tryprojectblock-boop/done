<?php

declare(strict_types=1);

namespace App\Modules\Task\Http\Requests;

use App\Modules\Task\Enums\TaskPriority;
use App\Modules\Task\Enums\TaskType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTaskRequest extends FormRequest
{
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
            'tags' => ['nullable', 'string'], // JSON from Tagify
            'tag_ids' => ['nullable', 'array'],
            'tag_ids.*' => ['exists:tags,id'],
            'watcher_ids' => ['nullable', 'array'],
            'watcher_ids.*' => ['exists:users,id'],
            'files' => ['nullable', 'array'],
            'files.*' => ['file', 'max:10240'], // 10MB max per file
            'action' => ['nullable', 'string', 'in:create,create_and_add_more,create_and_copy'],
        ];
    }

    public function messages(): array
    {
        return [
            'workspace_id.required' => 'Please select a workspace.',
            'workspace_id.exists' => 'The selected workspace is invalid.',
            'title.required' => 'Please enter a task title.',
            'title.max' => 'The task title cannot exceed 255 characters.',
        ];
    }
}
