<?php

declare(strict_types=1);

namespace App\Modules\Task\Http\Requests;

use App\Modules\Task\Enums\TaskPriority;
use App\Modules\Task\Enums\TaskType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:65535'],
            'type' => ['nullable', 'array'],
            'type.*' => ['nullable', Rule::enum(TaskType::class)],
            'priority' => ['nullable', Rule::enum(TaskPriority::class)],
            'status_id' => ['nullable', 'exists:workflow_statuses,id'],
            'assignee_id' => ['nullable', 'exists:users,id'],
            'due_date' => ['nullable', 'date'],
            'start_date' => ['nullable', 'date'],
            'parent_task_id' => ['nullable', 'exists:tasks,id'],
            'parent_link_notes' => ['nullable', 'string', 'max:255'],
            'estimated_time' => ['nullable', 'integer', 'min:0'],
            'actual_time' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
