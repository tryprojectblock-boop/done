<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

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

        // For now, return empty data - will be populated when Task module is created
        $stats = [
            'total_tasks' => 0,
            'pending_tasks' => 0,
            'in_progress_tasks' => 0,
            'completed_tasks' => 0,
        ];

        $tasks = [];
        $upcoming = [];
        $activities = [];

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
