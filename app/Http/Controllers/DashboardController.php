<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Modules\Idea\Models\Idea;
use App\Modules\Task\Models\Task;
use App\Modules\Task\Models\TaskActivity;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        // If user is a guest-only (no company, guest role), show guest dashboard
        if ($user->role === User::ROLE_GUEST && !$user->company_id) {
            return view('guest.dashboard');
        }

        // Check if this is the user's first login (no last_login_at or created recently)
        $isFirstLogin = is_null($user->last_login_at) ||
            ($user->created_at->diffInMinutes(now()) < 5 && is_null($user->last_login_at));

        // Get time-based greeting
        $hour = now()->hour;
        if ($hour < 12) {
            $greeting = 'morning';
        } elseif ($hour < 17) {
            $greeting = 'afternoon';
        } else {
            $greeting = 'evening';
        }

        // Get task statistics for the user
        $taskQuery = Task::where(function ($q) use ($user) {
            $q->where('created_by', $user->id)
              ->orWhere('assignee_id', $user->id);
        });

        $stats = [
            'total_tasks' => (clone $taskQuery)->count(),
            'pending_tasks' => (clone $taskQuery)->whereNull('closed_at')->whereNull('assignee_id')->count(),
            'in_progress_tasks' => (clone $taskQuery)->whereNull('closed_at')->whereNotNull('assignee_id')->count(),
            'completed_tasks' => (clone $taskQuery)->whereNotNull('closed_at')->count(),
        ];

        // Get user's recent tasks (assigned to them or created by them, not closed)
        $tasks = Task::where(function ($q) use ($user) {
                $q->where('assignee_id', $user->id)
                  ->orWhere('created_by', $user->id);
            })
            ->whereNull('closed_at')
            ->with(['workspace', 'status'])
            ->orderByRaw('due_date IS NULL, due_date ASC')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($task) {
                return [
                    'id' => $task->id,
                    'uuid' => $task->uuid,
                    'title' => $task->title,
                    'workspace' => $task->workspace?->name,
                    'completed' => !is_null($task->closed_at),
                    'due_date' => $task->due_date?->format('M d'),
                    'overdue' => $task->due_date && $task->due_date->isPast(),
                    'priority' => $task->priority?->value,
                    'status' => $task->status?->name,
                ];
            })
            ->toArray();

        // Get upcoming due tasks (next 7 days)
        $upcoming = Task::where(function ($q) use ($user) {
                $q->where('assignee_id', $user->id)
                  ->orWhere('created_by', $user->id);
            })
            ->whereNull('closed_at')
            ->whereNotNull('due_date')
            ->whereBetween('due_date', [now(), now()->addDays(7)])
            ->orderBy('due_date', 'asc')
            ->limit(5)
            ->get()
            ->map(function ($task) {
                return [
                    'title' => $task->title,
                    'date' => $task->due_date->format('M d, Y'),
                    'type' => 'task',
                ];
            })
            ->toArray();

        // Get recent activity
        $activities = TaskActivity::whereHas('task', function ($q) use ($user) {
                $q->where('created_by', $user->id)
                  ->orWhere('assignee_id', $user->id);
            })
            ->with(['user', 'task'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($activity) {
                return [
                    'user' => $activity->user?->name ?? 'Unknown',
                    'initials' => $activity->user ? strtoupper(substr($activity->user->name, 0, 1)) : '?',
                    'action' => $activity->getFormattedDescription(),
                    'time' => $activity->created_at->diffForHumans(),
                ];
            })
            ->toArray();

        // Get ideas count for stats (optional enhancement)
        $ideasCount = Idea::where('company_id', $user->company_id)->count();
        $stats['ideas_count'] = $ideasCount;

        // Update last login (for subsequent visits)
        if (!$isFirstLogin) {
            $user->update([
                'last_login_at' => now(),
                'last_login_ip' => $request->ip(),
            ]);
        }

        return view('dashboard', compact(
            'isFirstLogin',
            'greeting',
            'stats',
            'tasks',
            'upcoming',
            'activities'
        ));
    }

    /**
     * Mark onboarding as complete.
     */
    public function completeOnboarding(Request $request)
    {
        $user = $request->user();

        // Update last_login_at to mark that user has seen onboarding
        $user->update([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ]);

        return response()->json(['success' => true]);
    }
}
