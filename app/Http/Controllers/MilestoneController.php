<?php

namespace App\Http\Controllers;

use App\Models\Milestone;
use App\Models\MilestoneAttachment;
use App\Models\MilestoneComment;
use App\Models\User;
use App\Modules\Task\Models\Tag;
use App\Modules\Task\Models\Task;
use App\Modules\Workspace\Models\Workspace;
use App\Services\MilestoneNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class MilestoneController extends Controller
{
    protected MilestoneNotificationService $notificationService;

    public function __construct(MilestoneNotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }
    /**
     * Display a listing of milestones for a workspace.
     */
    public function index(Request $request, Workspace $workspace): View
    {
        $user = $request->user();

        // Check access
        if (!$workspace->hasAccess($user)) {
            abort(403, 'You do not have access to this workspace.');
        }

        // Apply filters
        $query = Milestone::forWorkspace($workspace->id)
            ->with(['owner', 'tasks', 'tags'])
            ->withCount('tasks');

        // Status filter
        if ($request->filled('status')) {
            $query->withStatus($request->status);
        }

        // Owner filter
        if ($request->filled('owner')) {
            $query->ownedBy($request->owner);
        }

        // Date filter
        if ($request->filled('due_from')) {
            $query->where('due_date', '>=', $request->due_from);
        }
        if ($request->filled('due_to')) {
            $query->where('due_date', '<=', $request->due_to);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $milestones = $query->ordered()->paginate(12);

        // Get workspace members for owner filter
        $members = $workspace->members()->get();

        // Get milestone statistics
        $stats = [
            'total' => Milestone::forWorkspace($workspace->id)->count(),
            'not_started' => Milestone::forWorkspace($workspace->id)->notStarted()->count(),
            'in_progress' => Milestone::forWorkspace($workspace->id)->inProgress()->count(),
            'blocked' => Milestone::forWorkspace($workspace->id)->blocked()->count(),
            'completed' => Milestone::forWorkspace($workspace->id)->completed()->count(),
            'overdue' => Milestone::forWorkspace($workspace->id)->overdue()->count(),
        ];

        return view('milestones.index', [
            'workspace' => $workspace,
            'milestones' => $milestones,
            'members' => $members,
            'stats' => $stats,
            'statuses' => Milestone::statuses(),
        ]);
    }

    /**
     * Show the form for creating a new milestone.
     */
    public function create(Request $request, Workspace $workspace): View
    {
        $user = $request->user();

        // Check access
        if (!$workspace->hasAccess($user)) {
            abort(403, 'You do not have access to this workspace.');
        }

        // Only Owners and Admins can create milestones
        if (!$user->isAdminOrHigher() && !$workspace->isOwner($user)) {
            abort(403, 'Only workspace owners and admins can create milestones.');
        }

        // Get workspace members for owner selection
        $members = $workspace->members()->get();

        // Get tags for this company
        $tags = Tag::where('company_id', $user->company_id)->orderBy('name')->get();

        return view('milestones.create', [
            'workspace' => $workspace,
            'members' => $members,
            'tags' => $tags,
            'priorities' => Milestone::priorities(),
        ]);
    }

    /**
     * Store a newly created milestone.
     */
    public function store(Request $request, Workspace $workspace): RedirectResponse
    {
        $user = $request->user();

        // Check access
        if (!$workspace->hasAccess($user)) {
            abort(403, 'You do not have access to this workspace.');
        }

        // Only Owners and Admins can create milestones
        if (!$user->isAdminOrHigher() && !$workspace->isOwner($user)) {
            abort(403, 'Only workspace owners and admins can create milestones.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'nullable|date',
            'due_date' => 'nullable|date|after_or_equal:start_date',
            'owner_id' => 'nullable|exists:users,id',
            'priority' => 'required|in:low,medium,high',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',
            'color' => 'nullable|string|max:7',
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
            'owner_id' => $validated['owner_id'] ?? null,
            'created_by' => $user->id,
            'priority' => $validated['priority'],
            'status' => $status,
            'color' => $validated['color'] ?? null,
            'progress' => 0,
        ]);

        // Attach tags
        if (!empty($validated['tags'])) {
            $milestone->tags()->sync($validated['tags']);
        }

        // Log activity
        $milestone->logActivity($user, 'created', 'created this milestone');

        // Send notification if owner is assigned
        if ($milestone->owner_id) {
            $owner = User::find($milestone->owner_id);
            if ($owner) {
                $this->notificationService->notifyMilestoneAssigned($milestone, $owner, $user);
            }
        }

        return redirect()->route('milestones.show', [$workspace->uuid, $milestone->uuid])
            ->with('success', 'Milestone created successfully.');
    }

    /**
     * Display the specified milestone.
     */
    public function show(Request $request, Workspace $workspace, Milestone $milestone): View
    {
        $user = $request->user();

        // Check access
        if (!$workspace->hasAccess($user)) {
            abort(403, 'You do not have access to this workspace.');
        }

        // Load relationships
        $milestone->load([
            'owner',
            'creator',
            'tags',
            'tasks' => function ($query) {
                $query->with(['assignee', 'status'])->orderBy('created_at', 'desc');
            },
            'comments.user',
            'attachments.uploader',
            'activities.user',
        ]);

        // Group tasks by status
        $tasksByStatus = $milestone->tasks->groupBy(function ($task) {
            return $task->status?->name ?? 'No Status';
        });

        // Get workspace tasks not assigned to any milestone (for adding to this milestone)
        $availableTasks = Task::forWorkspace($workspace->id)
            ->whereNull('milestone_id')
            ->with(['assignee', 'status'])
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        return view('milestones.show', [
            'workspace' => $workspace,
            'milestone' => $milestone,
            'tasksByStatus' => $tasksByStatus,
            'availableTasks' => $availableTasks,
        ]);
    }

    /**
     * Show the form for editing the specified milestone.
     */
    public function edit(Request $request, Workspace $workspace, Milestone $milestone): View
    {
        $user = $request->user();

        // Check access
        if (!$workspace->hasAccess($user)) {
            abort(403, 'You do not have access to this workspace.');
        }

        // Check edit permission
        if (!$milestone->canEdit($user)) {
            abort(403, 'You do not have permission to edit this milestone.');
        }

        // Get workspace members for owner selection
        $members = $workspace->members()->get();

        // Get tags for this company
        $tags = Tag::where('company_id', $user->company_id)->orderBy('name')->get();

        $milestone->load('tags');

        return view('milestones.edit', [
            'workspace' => $workspace,
            'milestone' => $milestone,
            'members' => $members,
            'tags' => $tags,
            'priorities' => Milestone::priorities(),
            'statuses' => Milestone::statuses(),
        ]);
    }

    /**
     * Update the specified milestone.
     */
    public function update(Request $request, Workspace $workspace, Milestone $milestone): RedirectResponse
    {
        $user = $request->user();

        // Check access
        if (!$workspace->hasAccess($user)) {
            abort(403, 'You do not have access to this workspace.');
        }

        // Check edit permission
        if (!$milestone->canEdit($user)) {
            abort(403, 'You do not have permission to edit this milestone.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'nullable|date',
            'due_date' => 'nullable|date|after_or_equal:start_date',
            'owner_id' => 'nullable|exists:users,id',
            'priority' => 'required|in:low,medium,high',
            'status' => 'required|in:not_started,in_progress,blocked,completed',
            'progress' => 'required|integer|min:0|max:100',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',
            'color' => 'nullable|string|max:7',
        ]);

        // Track changes for activity log
        $changes = [];
        foreach (['title', 'status', 'priority', 'owner_id', 'progress'] as $field) {
            if (isset($validated[$field]) && $milestone->$field != $validated[$field]) {
                $changes[$field] = [
                    'from' => $milestone->$field,
                    'to' => $validated[$field],
                ];
            }
        }

        $milestone->update([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'start_date' => $validated['start_date'] ?? null,
            'due_date' => $validated['due_date'] ?? null,
            'owner_id' => $validated['owner_id'] ?? null,
            'priority' => $validated['priority'],
            'status' => $validated['status'],
            'progress' => $validated['progress'],
            'color' => $validated['color'] ?? null,
        ]);

        // Sync tags
        $milestone->tags()->sync($validated['tags'] ?? []);

        // Log activity if there were changes
        if (!empty($changes)) {
            $milestone->logActivity($user, 'updated', 'updated this milestone', $changes);
        }

        // Send notification if owner changed
        if (isset($changes['owner_id']) && $validated['owner_id']) {
            $newOwner = User::find($validated['owner_id']);
            if ($newOwner) {
                $this->notificationService->notifyMilestoneAssigned($milestone->fresh(), $newOwner, $user);
            }
        }

        // Send notification if milestone was completed
        if (isset($changes['status']) && $changes['status']['to'] === 'completed') {
            $this->notificationService->notifyMilestoneCompleted($milestone->fresh(), $user);
        }

        return redirect()->route('milestones.show', [$workspace->uuid, $milestone->uuid])
            ->with('success', 'Milestone updated successfully.');
    }

    /**
     * Remove the specified milestone.
     */
    public function destroy(Request $request, Workspace $workspace, Milestone $milestone): RedirectResponse
    {
        $user = $request->user();

        // Check access
        if (!$workspace->hasAccess($user)) {
            abort(403, 'You do not have access to this workspace.');
        }

        // Check delete permission
        if (!$milestone->canDelete($user)) {
            abort(403, 'You do not have permission to delete this milestone.');
        }

        // Unassign all tasks from this milestone
        Task::where('milestone_id', $milestone->id)->update(['milestone_id' => null]);

        $milestone->delete();

        return redirect()->route('milestones.index', $workspace->uuid)
            ->with('success', 'Milestone deleted successfully.');
    }

    /**
     * Add a task to the milestone.
     */
    public function addTask(Request $request, Workspace $workspace, Milestone $milestone): RedirectResponse
    {
        $user = $request->user();

        // Check access
        if (!$workspace->hasAccess($user)) {
            abort(403, 'You do not have access to this workspace.');
        }

        // Guests cannot add tasks to milestones
        if ($user->isGuestOf($workspace)) {
            abort(403, 'Guests have view-only access to milestones.');
        }

        $validated = $request->validate([
            'task_id' => 'required|exists:tasks,id',
        ]);

        $task = Task::findOrFail($validated['task_id']);

        // Verify task belongs to same workspace
        if ($task->workspace_id !== $workspace->id) {
            return back()->with('error', 'Task does not belong to this workspace.');
        }

        $task->update(['milestone_id' => $milestone->id]);

        // Recalculate milestone progress
        $milestone->recalculateProgress();

        // Log activity
        $milestone->logActivity($user, 'task_added', "added task \"{$task->title}\" to this milestone");

        return back()->with('success', 'Task added to milestone.');
    }

    /**
     * Remove a task from the milestone.
     */
    public function removeTask(Request $request, Workspace $workspace, Milestone $milestone, Task $task): RedirectResponse
    {
        $user = $request->user();

        // Check access
        if (!$workspace->hasAccess($user)) {
            abort(403, 'You do not have access to this workspace.');
        }

        // Guests cannot remove tasks from milestones
        if ($user->isGuestOf($workspace)) {
            abort(403, 'Guests have view-only access to milestones.');
        }

        // Verify task belongs to this milestone
        if ($task->milestone_id !== $milestone->id) {
            return back()->with('error', 'Task does not belong to this milestone.');
        }

        $task->update(['milestone_id' => null]);

        // Recalculate milestone progress
        $milestone->recalculateProgress();

        // Log activity
        $milestone->logActivity($user, 'task_removed', "removed task \"{$task->title}\" from this milestone");

        return back()->with('success', 'Task removed from milestone.');
    }

    /**
     * Add a comment to the milestone.
     */
    public function addComment(Request $request, Workspace $workspace, Milestone $milestone): RedirectResponse
    {
        $user = $request->user();

        // Check access
        if (!$workspace->hasAccess($user)) {
            abort(403, 'You do not have access to this workspace.');
        }

        $validated = $request->validate([
            'content' => 'required|string|max:5000',
        ]);

        $milestone->comments()->create([
            'user_id' => $user->id,
            'content' => $validated['content'],
        ]);

        // Log activity
        $milestone->logActivity($user, 'comment_added', 'added a comment');

        // Send notification
        $this->notificationService->notifyMilestoneComment($milestone, $user);

        return back()->with('success', 'Comment added.');
    }

    /**
     * Delete a comment from the milestone.
     */
    public function deleteComment(Request $request, Workspace $workspace, Milestone $milestone, MilestoneComment $comment): RedirectResponse
    {
        $user = $request->user();

        // Check access
        if (!$workspace->hasAccess($user)) {
            abort(403, 'You do not have access to this workspace.');
        }

        // Check if user can delete (owner of comment or admin)
        if ($comment->user_id !== $user->id && !$user->isAdminOrHigher()) {
            abort(403, 'You do not have permission to delete this comment.');
        }

        $comment->delete();

        return back()->with('success', 'Comment deleted.');
    }

    /**
     * Upload an attachment to the milestone.
     */
    public function uploadAttachment(Request $request, Workspace $workspace, Milestone $milestone): RedirectResponse
    {
        $user = $request->user();

        // Check access
        if (!$workspace->hasAccess($user)) {
            abort(403, 'You do not have access to this workspace.');
        }

        $validated = $request->validate([
            'file' => 'required|file|max:10240', // 10MB max
        ]);

        $file = $request->file('file');
        $filename = time() . '_' . $file->getClientOriginalName();
        $path = $file->store('milestone-attachments/' . $milestone->id, 'public');

        $milestone->attachments()->create([
            'uploaded_by' => $user->id,
            'filename' => $filename,
            'original_filename' => $file->getClientOriginalName(),
            'path' => $path,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
        ]);

        // Log activity
        $milestone->logActivity($user, 'attachment_added', "uploaded file \"{$file->getClientOriginalName()}\"");

        return back()->with('success', 'File uploaded successfully.');
    }

    /**
     * Delete an attachment from the milestone.
     */
    public function deleteAttachment(Request $request, Workspace $workspace, Milestone $milestone, MilestoneAttachment $attachment): RedirectResponse
    {
        $user = $request->user();

        // Check access
        if (!$workspace->hasAccess($user)) {
            abort(403, 'You do not have access to this workspace.');
        }

        // Check if user can delete (uploader or admin)
        if ($attachment->uploaded_by !== $user->id && !$user->isAdminOrHigher()) {
            abort(403, 'You do not have permission to delete this attachment.');
        }

        // Delete file from storage
        Storage::disk('public')->delete($attachment->path);

        $attachment->delete();

        return back()->with('success', 'Attachment deleted.');
    }

    /**
     * Update milestone status via AJAX.
     */
    public function updateStatus(Request $request, Workspace $workspace, Milestone $milestone): RedirectResponse
    {
        $user = $request->user();

        // Check access
        if (!$workspace->hasAccess($user)) {
            abort(403, 'You do not have access to this workspace.');
        }

        // Check edit permission
        if (!$milestone->canEdit($user)) {
            abort(403, 'You do not have permission to edit this milestone.');
        }

        $validated = $request->validate([
            'status' => 'required|in:not_started,in_progress,blocked,completed',
        ]);

        $oldStatus = $milestone->status;
        $newStatus = $validated['status'];

        // If completing, also update progress to 100
        $updateData = ['status' => $newStatus];
        if ($newStatus === 'completed') {
            $updateData['progress'] = 100;
            $updateData['completed_at'] = now();
        }

        $milestone->update($updateData);

        // Log activity
        $milestone->logActivity($user, 'status_changed', "changed status from \"{$oldStatus}\" to \"{$newStatus}\"", [
            'from' => $oldStatus,
            'to' => $newStatus,
        ]);

        // Send notification if completed
        if ($newStatus === 'completed' && $oldStatus !== 'completed') {
            $this->notificationService->notifyMilestoneCompleted($milestone->fresh(), $user);
        }

        return back()->with('success', 'Status updated.');
    }
}
