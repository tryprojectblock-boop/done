<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Discussion\Models\Discussion;
use App\Modules\Task\Models\Task;
use App\Modules\Workspace\Models\Workspace;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Get dashboard statistics for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $companyId = $user->company_id;

        // Get workspaces the user is a member of
        $workspaceIds = $user->workspaces()->pluck('workspaces.id');

        // Workspace count
        $workspacesCount = $workspaceIds->count();

        // Tasks assigned to user (open tasks only)
        $tasksCount = Task::whereIn('workspace_id', $workspaceIds)
            ->where('assignee_id', $user->id)
            ->whereHas('status', function ($q) {
                $q->where('type', '!=', 'closed');
            })
            ->count();

        // Discussions created by user
        $discussionsCount = Discussion::whereIn('workspace_id', $workspaceIds)
            ->where('created_by', $user->id)
            ->count();

        // Team members count (in same company)
        $teamCount = User::where('company_id', $companyId)
            ->where('status', 'active')
            ->count();

        // Recent tasks (last 5 assigned to user)
        $recentTasks = Task::whereIn('workspace_id', $workspaceIds)
            ->where('assignee_id', $user->id)
            ->with(['status', 'workspace:id,name'])
            ->orderBy('updated_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($task) {
                return [
                    'id' => $task->id,
                    'title' => $task->title,
                    'status' => $task->status ? [
                        'name' => $task->status->name,
                        'color' => $task->status->color,
                        'type' => $task->status->type,
                    ] : null,
                    'workspace' => $task->workspace ? [
                        'id' => $task->workspace->id,
                        'name' => $task->workspace->name,
                    ] : null,
                    'due_date' => $task->due_date?->toISOString(),
                    'priority' => $task->priority,
                ];
            });

        // Recent workspaces (last 5 accessed)
        $recentWorkspaces = Workspace::whereIn('id', $workspaceIds)
            ->orderBy('updated_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($workspace) {
                return [
                    'id' => $workspace->id,
                    'name' => $workspace->name,
                    'type' => $workspace->type,
                    'icon' => $workspace->icon,
                    'color' => $workspace->color,
                ];
            });

        return response()->json([
            'success' => true,
            'stats' => [
                'workspaces' => $workspacesCount,
                'tasks' => $tasksCount,
                'discussions' => $discussionsCount,
                'team' => $teamCount,
            ],
            'recent_tasks' => $recentTasks,
            'recent_workspaces' => $recentWorkspaces,
        ]);
    }

    /**
     * Get tasks overview for dashboard.
     */
    public function tasks(Request $request): JsonResponse
    {
        $user = $request->user();
        $workspaceIds = $user->workspaces()->pluck('workspaces.id');

        // Get task counts by status type
        $openTasks = Task::whereIn('workspace_id', $workspaceIds)
            ->where('assignee_id', $user->id)
            ->whereHas('status', fn($q) => $q->where('type', 'open'))
            ->count();

        $activeTasks = Task::whereIn('workspace_id', $workspaceIds)
            ->where('assignee_id', $user->id)
            ->whereHas('status', fn($q) => $q->where('type', 'active'))
            ->count();

        $completedTasks = Task::whereIn('workspace_id', $workspaceIds)
            ->where('assignee_id', $user->id)
            ->whereHas('status', fn($q) => $q->where('type', 'closed'))
            ->count();

        // Overdue tasks
        $overdueTasks = Task::whereIn('workspace_id', $workspaceIds)
            ->where('assignee_id', $user->id)
            ->whereNotNull('due_date')
            ->where('due_date', '<', now())
            ->whereHas('status', fn($q) => $q->where('type', '!=', 'closed'))
            ->count();

        // Due today
        $dueTodayTasks = Task::whereIn('workspace_id', $workspaceIds)
            ->where('assignee_id', $user->id)
            ->whereDate('due_date', today())
            ->whereHas('status', fn($q) => $q->where('type', '!=', 'closed'))
            ->count();

        return response()->json([
            'success' => true,
            'overview' => [
                'open' => $openTasks,
                'active' => $activeTasks,
                'completed' => $completedTasks,
                'overdue' => $overdueTasks,
                'due_today' => $dueTodayTasks,
            ],
        ]);
    }
}
