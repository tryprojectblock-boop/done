<?php

declare(strict_types=1);

namespace App\Modules\Calendar\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Task\Models\Task;
use App\Modules\Workspace\Models\Workspace;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CalendarController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $companyId = $user->company_id;

        // Check if user can view all assignees (owner/admin only)
        $canViewAllAssignees = $user->isAdminOrHigher();

        $view = $request->get('view', 'list'); // list or calendar
        if (!in_array($view, ['list', 'calendar'])) {
            $view = 'list';
        }

        // Get filter parameters
        $tab = $request->get('tab', 'all'); // all or overdue

        // Determine assignee filter:
        // - For regular members: always show their own tasks only
        // - For owner/admin: show ALL tasks by default, can filter by assignee
        $assigneeId = $request->get('assignee_id');
        if (!$canViewAllAssignees) {
            // Regular members can only see their own tasks
            $assigneeId = $user->id;
        }
        // Owner/admin: use whatever filter they set (including empty = all tasks)

        $filters = [
            'tab' => $tab,
            'workspace_id' => $request->get('workspace_id'),
            'assignee_id' => $assigneeId,
            'month' => $request->get('month', now()->month),
            'year' => $request->get('year', now()->year),
        ];

        // Build date range - from today onwards for 30 days
        $today = Carbon::today();
        $tomorrow = Carbon::tomorrow();
        $endDate = $today->copy()->addDays(30);

        // Query base for tasks
        $baseQuery = Task::query()
            ->with(['workspace', 'assignee', 'creator', 'status', 'tags'])
            ->where('company_id', $companyId)
            ->visibleTo($user)
            ->whereNull('closed_at')
            ->whereDoesntHave('status', fn($q) => $q->where('type', 'closed'));

        // Apply workspace filter
        if ($filters['workspace_id']) {
            $baseQuery->where('workspace_id', $filters['workspace_id']);
        }

        // Apply assignee filter (always applied - either user's choice or enforced for regular members)
        if ($filters['assignee_id']) {
            $baseQuery->where('assignee_id', $filters['assignee_id']);
        }

        // Get overdue tasks (for both tabs - stats and overdue tab)
        $overdueTasks = (clone $baseQuery)
            ->whereNotNull('due_date')
            ->where('due_date', '<', $today)
            ->orderBy('due_date', 'asc')
            ->get();

        // Get today's tasks
        $todayTasks = (clone $baseQuery)
            ->whereNotNull('due_date')
            ->whereDate('due_date', $today)
            ->orderBy('due_date', 'asc')
            ->get();

        // Get tomorrow's tasks
        $tomorrowTasks = (clone $baseQuery)
            ->whereNotNull('due_date')
            ->whereDate('due_date', $tomorrow)
            ->orderBy('due_date', 'asc')
            ->get();

        // Get upcoming tasks (after tomorrow, within 30 days)
        $upcomingTasks = (clone $baseQuery)
            ->whereNotNull('due_date')
            ->where('due_date', '>', $tomorrow->copy()->endOfDay())
            ->where('due_date', '<=', $endDate)
            ->orderBy('due_date', 'asc')
            ->get();

        // Group upcoming tasks by date
        $upcomingByDate = $upcomingTasks->groupBy(function ($task) {
            return $task->due_date->format('Y-m-d');
        })->sortKeys();

        // Get workspaces for filter
        $workspaces = Workspace::forUser($user)->get();

        // Get team members for filter
        $teamMembers = \App\Models\User::where('company_id', $companyId)
            ->where('role', '!=', \App\Models\User::ROLE_GUEST)
            ->orderBy('first_name')
            ->get();

        // Calculate stats
        $stats = [
            'all' => $todayTasks->count() + $tomorrowTasks->count() + $upcomingTasks->count(),
            'overdue' => $overdueTasks->count(),
            'today' => $todayTasks->count(),
            'tomorrow' => $tomorrowTasks->count(),
            'upcoming' => $upcomingTasks->count(),
        ];

        // For calendar view compatibility
        $startOfMonth = Carbon::create($filters['year'], $filters['month'], 1)->startOfMonth();
        $endOfMonth = $startOfMonth->copy()->endOfMonth();

        return view('calendar::index', compact(
            'view',
            'tab',
            'overdueTasks',
            'todayTasks',
            'tomorrowTasks',
            'upcomingTasks',
            'upcomingByDate',
            'workspaces',
            'teamMembers',
            'filters',
            'stats',
            'startOfMonth',
            'endOfMonth',
            'canViewAllAssignees'
        ));
    }

    /**
     * Get tasks for calendar view (AJAX endpoint)
     */
    public function events(Request $request): JsonResponse
    {
        $user = $request->user();
        $companyId = $user->company_id;
        $canViewAllAssignees = $user->isAdminOrHigher();

        $start = $request->get('start') ? Carbon::parse($request->get('start')) : now()->startOfMonth();
        $end = $request->get('end') ? Carbon::parse($request->get('end')) : now()->endOfMonth();

        $query = Task::query()
            ->with(['workspace', 'assignee', 'status'])
            ->where('company_id', $companyId)
            ->visibleTo($user)
            ->whereNotNull('due_date')
            ->whereBetween('due_date', [$start, $end]);

        // Apply filters
        if ($request->get('workspace_id')) {
            $query->where('workspace_id', $request->get('workspace_id'));
        }

        // Apply assignee filter with same restrictions as index
        $assigneeId = $request->get('assignee_id');
        if (!$canViewAllAssignees) {
            // Regular members can only see their own tasks
            $assigneeId = $user->id;
        }
        // Owner/admin: use whatever filter they set (including empty = all tasks)

        if ($assigneeId) {
            $query->where('assignee_id', $assigneeId);
        }

        $tasks = $query->get();

        $events = $tasks->map(function ($task) {
            $color = $this->getTaskColor($task);

            return [
                'id' => $task->id,
                'uuid' => $task->uuid,
                'title' => $task->title,
                'start' => $task->due_date->format('Y-m-d'),
                'allDay' => true,
                'backgroundColor' => $color,
                'borderColor' => $color,
                'textColor' => '#ffffff',
                'extendedProps' => [
                    'uuid' => $task->uuid,
                    'task_number' => $task->task_number,
                    'workspace' => $task->workspace?->name,
                    'assignee' => $task->assignee?->name,
                    'assignee_avatar' => $task->assignee?->avatar_url,
                    'status' => $task->status?->name,
                    'status_color' => $task->status?->color,
                    'priority' => $task->priority?->value,
                    'is_overdue' => $task->isOverdue(),
                    'is_closed' => $task->isClosed(),
                ],
            ];
        });

        return response()->json($events);
    }

    /**
     * Get task details for drawer (AJAX endpoint)
     */
    public function taskDetails(Request $request, string $uuid): JsonResponse
    {
        $user = $request->user();

        $task = Task::where('uuid', $uuid)
            ->where('company_id', $user->company_id)
            ->with(['workspace', 'assignee', 'creator', 'status', 'tags', 'watchers'])
            ->first();

        if (!$task) {
            return response()->json(['error' => 'Task not found'], 404);
        }

        return response()->json([
            'id' => $task->id,
            'uuid' => $task->uuid,
            'task_number' => $task->task_number,
            'title' => $task->title,
            'description' => $task->description,
            'due_date' => $task->due_date?->format('M d, Y'),
            'due_date_raw' => $task->due_date?->format('Y-m-d'),
            'start_date' => $task->start_date?->format('M d, Y'),
            'created_at' => $task->created_at->format('M d, Y'),
            'priority' => $task->priority?->value,
            'priority_label' => $task->priority?->label(),
            'priority_color' => $task->priority?->color(),
            'estimated_time' => $task->estimated_time,
            'actual_time' => $task->actual_time,
            'is_overdue' => $task->isOverdue(),
            'is_closed' => $task->isClosed(),
            'closed_at' => $task->closed_at?->format('M d, Y'),
            'workspace' => $task->workspace ? [
                'id' => $task->workspace->id,
                'name' => $task->workspace->name,
            ] : null,
            'assignee' => $task->assignee ? [
                'id' => $task->assignee->id,
                'name' => $task->assignee->name,
                'avatar_url' => $task->assignee->avatar_url,
            ] : null,
            'creator' => [
                'id' => $task->creator->id,
                'name' => $task->creator->name,
                'avatar_url' => $task->creator->avatar_url,
            ],
            'status' => $task->status ? [
                'id' => $task->status->id,
                'name' => $task->status->name,
                'color' => $task->status->background_color,
            ] : null,
            'tags' => $task->tags->map(fn($tag) => [
                'id' => $tag->id,
                'name' => $tag->name,
                'color' => $tag->color,
            ]),
            'url' => route('tasks.show', $task->uuid),
        ]);
    }

    private function getTaskColor(Task $task): string
    {
        if ($task->isClosed()) {
            return '#6b7280'; // Gray for closed
        }

        if ($task->isOverdue()) {
            return '#ef4444'; // Red for overdue
        }

        // Use priority color
        return match ($task->priority?->value) {
            'critical' => '#dc2626',
            'high' => '#f97316',
            'medium' => '#eab308',
            'low' => '#22c55e',
            default => '#3b82f6', // Blue default
        };
    }
}
