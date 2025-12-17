<?php

declare(strict_types=1);

namespace App\Modules\Admin\Http\Controllers;

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

        return view('admin::scheduled-tasks.index', [
            'tasks' => $tasks,
            'frequencyOptions' => ScheduledTask::frequencyOptions(),
            'dayOfWeekOptions' => ScheduledTask::dayOfWeekOptions(),
        ]);
    }

    /**
     * Create a new scheduled task.
     */
    public function create(): View
    {
        return view('admin::scheduled-tasks.create', [
            'frequencyOptions' => ScheduledTask::frequencyOptions(),
            'dayOfWeekOptions' => ScheduledTask::dayOfWeekOptions(),
            'availableCommands' => $this->getAvailableCommands(),
        ]);
    }

    /**
     * Store a new scheduled task.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:scheduled_tasks,name'],
            'display_name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'command' => ['required', 'string', 'max:255'],
            'frequency' => ['required', 'in:hourly,daily,weekly,monthly'],
            'time' => ['nullable', 'date_format:H:i'],
            'day_of_week' => ['nullable', 'integer', 'min:0', 'max:6'],
            'day_of_month' => ['nullable', 'integer', 'min:1', 'max:31'],
            'is_active' => ['boolean'],
        ]);

        ScheduledTask::create([
            'name' => $validated['name'],
            'display_name' => $validated['display_name'],
            'description' => $validated['description'] ?? null,
            'command' => $validated['command'],
            'frequency' => $validated['frequency'],
            'time' => $validated['time'] ?? '02:00',
            'day_of_week' => $validated['day_of_week'] ?? null,
            'day_of_month' => $validated['day_of_month'] ?? null,
            'is_active' => $request->boolean('is_active'),
            'options' => [],
        ]);

        return redirect()->route('backoffice.scheduled-tasks.index')
            ->with('success', 'Scheduled task created successfully.');
    }

    /**
     * Update a scheduled task.
     */
    public function update(Request $request, ScheduledTask $task): RedirectResponse
    {
        $validated = $request->validate([
            'display_name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'frequency' => ['required', 'in:hourly,daily,weekly,monthly'],
            'time' => ['nullable', 'date_format:H:i'],
            'day_of_week' => ['nullable', 'integer', 'min:0', 'max:6'],
            'day_of_month' => ['nullable', 'integer', 'min:1', 'max:31'],
            'is_active' => ['boolean'],
            'options' => ['nullable', 'array'],
        ]);

        $task->update([
            'display_name' => $validated['display_name'],
            'description' => $validated['description'] ?? $task->description,
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
            return back()->with('success', "Task '{$task->display_name}' completed successfully in {$result['duration']}s.");
        }

        return back()->with('error', "Task '{$task->display_name}' failed: {$result['output']}");
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
            'last_run_at' => $task->fresh()->last_run_at?->diffForHumans() ?? 'Never',
        ]);
    }

    /**
     * Delete a scheduled task.
     */
    public function destroy(ScheduledTask $task): RedirectResponse
    {
        $name = $task->display_name;
        $task->delete();

        return redirect()->route('backoffice.scheduled-tasks.index')
            ->with('success', "Task '{$name}' deleted successfully.");
    }

    /**
     * Get available artisan commands for scheduling.
     */
    protected function getAvailableCommands(): array
    {
        return [
            'notifications:prune' => 'Prune Old Notifications',
            'cache:clear' => 'Clear Application Cache',
            'queue:work --stop-when-empty' => 'Process Queue Jobs',
            'backup:run' => 'Run Database Backup',
            'telescope:prune' => 'Prune Telescope Entries',
            'horizon:snapshot' => 'Horizon Snapshot',
        ];
    }
}
