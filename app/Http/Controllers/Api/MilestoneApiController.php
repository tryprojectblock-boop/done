<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Milestone;
use App\Modules\Workspace\Models\Workspace;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MilestoneApiController extends Controller
{
    /**
     * Get milestones for a workspace.
     */
    public function index(Request $request, int $workspaceId): JsonResponse
    {
        $user = $request->user();
        $workspace = Workspace::findOrFail($workspaceId);

        // Check access
        if (!$workspace->hasAccess($user)) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $milestones = Milestone::forWorkspace($workspace->id)
            ->where('status', '!=', 'completed') // Only show non-completed milestones in dropdown
            ->ordered()
            ->get(['id', 'uuid', 'title', 'status', 'progress', 'due_date', 'priority']);

        return response()->json([
            'milestones' => $milestones,
        ]);
    }

    /**
     * Store a new milestone via API.
     */
    public function store(Request $request, int $workspaceId): JsonResponse
    {
        $user = $request->user();
        $workspace = Workspace::findOrFail($workspaceId);

        // Check access
        if (!$workspace->hasAccess($user)) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'nullable|date',
            'due_date' => 'nullable|date|after_or_equal:start_date',
            'priority' => 'nullable|in:low,medium,high',
        ]);

        // Determine initial status
        $status = 'not_started';
        if (!empty($validated['start_date']) && now()->gte($validated['start_date'])) {
            $status = 'in_progress';
        }

        $milestone = Milestone::create([
            'workspace_id' => $workspace->id,
            'company_id' => $user->company_id,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'start_date' => $validated['start_date'] ?? null,
            'due_date' => $validated['due_date'] ?? null,
            'owner_id' => $user->id,
            'created_by' => $user->id,
            'priority' => $validated['priority'] ?? 'medium',
            'status' => $status,
            'progress' => 0,
        ]);

        // Log activity
        $milestone->logActivity($user, 'created', 'created this milestone');

        return response()->json([
            'milestone' => $milestone,
            'message' => 'Milestone created successfully',
        ], 201);
    }
}
