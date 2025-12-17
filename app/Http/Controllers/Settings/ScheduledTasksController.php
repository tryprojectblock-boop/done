<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\ScheduledTask;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ScheduledTasksController extends Controller
{
    /**
     * Display all scheduled tasks.
     */
    public function index(): View
    {
        $tasks = ScheduledTask::orderBy('display_name')->get();

        return view('settings.scheduled-tasks.index', [
            'tasks' => $tasks,
            'frequencyOptions' => ScheduledTask::frequencyOptions(),
            'dayOfWeekOptions' => ScheduledTask::dayOfWeekOptions(),
        ]);
    }

    /**
     * Update a scheduled task.
     */
    public function update(Request $request, ScheduledTask $task): RedirectResponse
    {
        $validated = $request->validate([
            'frequency' => ['required', 'in:hourly,daily,weekly,monthly'],
            'time' => ['required_unless:frequency,hourly', 'nullable', 'date_format:H:i'],
            'day_of_week' => ['required_if:frequency,weekly', 'nullable', 'integer', 'min:0', 'max:6'],
            'day_of_month' => ['required_if:frequency,monthly', 'nullable', 'integer', 'min:1', 'max:31'],
            'is_active' => ['boolean'],
            'options' => ['nullable', 'array'],
        ]);

        $task->update([
            'frequency' => $validated['frequency'],
            'time' => $validated['time'] ?? '02:00',
            'day_of_week' => $validated['day_of_week'] ?? null,
            'day_of_month' => $validated['day_of_month'] ?? null,
            'is_active' => $request->boolean('is_active'),
            'options' => $validated['options'] ?? $task->options,
        ]);

        return back()->with('success', "Task '{$task->display_name}' updated successfully.");
    }

    /**
     * Toggle task active status.
     */
    public function toggle(ScheduledTask $task): RedirectResponse
    {
        $task->update(['is_active' => !$task->is_active]);

        $status = $task->is_active ? 'enabled' : 'disabled';
        return back()->with('success', "Task '{$task->display_name}' has been {$status}.");
    }

    /**
     * Run a task immediately.
     */
    public function run(ScheduledTask $task): RedirectResponse
    {
        $result = $task->runNow();

        if ($result['status'] === 'success') {
            return back()->with('success', "Task '{$task->display_name}' completed successfully.\n\nOutput:\n{$result['output']}");
        }

        return back()->with('error', "Task '{$task->display_name}' failed.\n\nError:\n{$result['output']}");
    }

    /**
     * Run a task via AJAX.
     */
    public function runAjax(ScheduledTask $task): JsonResponse
    {
        $result = $task->runNow();

        return response()->json([
            'success' => $result['status'] === 'success',
            'status' => $result['status'],
            'output' => $result['output'],
            'duration' => $result['duration'],
            'last_run_at' => $task->fresh()->last_run_at->diffForHumans(),
        ]);
    }

    /**
     * Update task options (like days for notification pruning).
     */
    public function updateOptions(Request $request, ScheduledTask $task): RedirectResponse
    {
        $options = $request->input('options', []);

        // Clean up options - remove empty values
        $cleanOptions = [];
        foreach ($options as $key => $value) {
            if ($value !== '' && $value !== null) {
                // Convert numeric strings to integers
                $cleanOptions[$key] = is_numeric($value) ? (int) $value : $value;
            }
        }

        $task->update(['options' => $cleanOptions]);

        return back()->with('success', "Options for '{$task->display_name}' updated successfully.");
    }
}
