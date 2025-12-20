<?php

declare(strict_types=1);

namespace App\Modules\Standup\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Standup\Models\MemberTracker;
use App\Modules\Workspace\Enums\WorkspaceRole;
use App\Modules\Workspace\Models\Workspace;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TrackerController extends Controller
{
    /**
     * Display the tracker tab with team members and their on-track status.
     */
    public function index(Request $request, Workspace $workspace): View
    {
        $this->authorizeAccess($workspace);

        // Get all workspace members with their tracker status
        $members = $workspace->members()->get();

        // Ensure each member has a tracker record
        $trackerData = [];
        foreach ($members as $member) {
            $tracker = MemberTracker::getOrCreateForMember($workspace, $member);
            $trackerData[] = [
                'user' => $member,
                'tracker' => $tracker,
            ];
        }

        // Get stats
        $stats = MemberTracker::getStats($workspace->id);

        // Check if current user can manage trackers (Owner/Admin)
        $userRole = $workspace->getMemberRole(auth()->user());
        $canManage = in_array($userRole, [WorkspaceRole::OWNER, WorkspaceRole::ADMIN]);

        return view('standup::tracker', [
            'workspace' => $workspace,
            'trackerData' => $trackerData,
            'stats' => $stats,
            'canManage' => $canManage,
            'tab' => 'tracker',
        ]);
    }

    /**
     * Update a member's on-track status.
     */
    public function update(Request $request, Workspace $workspace, User $user): RedirectResponse
    {
        $this->authorizeAccess($workspace);
        $this->authorizeManage($workspace);

        $validated = $request->validate([
            'is_on_track' => 'required|boolean',
            'off_track_reason' => 'nullable|string|max:500',
        ]);

        $tracker = MemberTracker::getOrCreateForMember($workspace, $user);

        if ($validated['is_on_track']) {
            $tracker->markOnTrack(auth()->user());
        } else {
            $tracker->markOffTrack(
                $validated['off_track_reason'] ?? '',
                auth()->user()
            );
        }

        return redirect()
            ->route('standups.tracker.index', $workspace)
            ->with('success', 'Member status updated.');
    }

    /**
     * Get tracker stats as JSON (for AJAX/widgets).
     */
    public function getStats(Request $request, Workspace $workspace): JsonResponse
    {
        $this->authorizeAccess($workspace);

        $stats = MemberTracker::getStats($workspace->id);

        return response()->json($stats);
    }

    /**
     * Authorize access to tracker features.
     */
    private function authorizeAccess(Workspace $workspace): void
    {
        if (!$workspace->hasMember(auth()->user())) {
            abort(403, 'You must be a member of this workspace.');
        }

        if (!$workspace->isStandupEnabled()) {
            abort(403, 'Daily Standup is not enabled for this workspace.');
        }
    }

    /**
     * Authorize management of tracker (Owner/Admin only).
     */
    private function authorizeManage(Workspace $workspace): void
    {
        $userRole = $workspace->getMemberRole(auth()->user());

        if (!in_array($userRole, [WorkspaceRole::OWNER, WorkspaceRole::ADMIN])) {
            abort(403, 'Only workspace owners and admins can manage tracker status.');
        }
    }
}
