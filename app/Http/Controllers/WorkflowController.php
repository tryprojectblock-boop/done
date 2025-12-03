<?php

namespace App\Http\Controllers;

use App\Models\Workflow;
use App\Models\WorkflowStatus;
use App\Modules\Workspace\Models\Workspace;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class WorkflowController extends Controller
{
    /**
     * Display all workflows for a workspace (card layout).
     */
    public function index(Request $request, Workspace $workspace): View
    {
        $this->authorizeWorkspaceAccess($request, $workspace);

        $workflows = Workflow::where('workspace_id', $workspace->id)
            ->with(['statuses', 'creator'])
            ->active()
            ->latest()
            ->get();

        $archivedWorkflows = Workflow::where('workspace_id', $workspace->id)
            ->with(['statuses', 'creator'])
            ->archived()
            ->latest()
            ->get();

        return view('workflow.index', [
            'workspace' => $workspace,
            'workflows' => $workflows,
            'archivedWorkflows' => $archivedWorkflows,
            'colors' => Workflow::COLORS,
            'canManage' => $this->canManageWorkflows($request),
        ]);
    }

    /**
     * Show the form for creating a new workflow.
     */
    public function create(Request $request, Workspace $workspace): View|RedirectResponse
    {
        $this->authorizeWorkspaceAccess($request, $workspace);

        if (!$this->canManageWorkflows($request)) {
            return redirect()->route('workflow.index', $workspace)
                ->with('error', 'You do not have permission to create workflows.');
        }

        return view('workflow.create', [
            'workspace' => $workspace,
            'colors' => Workflow::COLORS,
        ]);
    }

    /**
     * Store a new workflow with statuses.
     */
    public function store(Request $request, Workspace $workspace): RedirectResponse
    {
        $this->authorizeWorkspaceAccess($request, $workspace);

        if (!$this->canManageWorkflows($request)) {
            return redirect()->route('workflow.index', $workspace)
                ->with('error', 'You do not have permission to create workflows.');
        }

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('workflows')->where(fn ($query) => $query->where('workspace_id', $workspace->id)),
            ],
            'description' => ['nullable', 'string', 'max:500'],
            'statuses' => ['required', 'array', 'min:1'],
            'statuses.*.name' => ['required', 'string', 'max:40'],
            'statuses.*.color' => ['required', Rule::in(array_keys(Workflow::COLORS))],
            'statuses.*.is_active' => ['required', 'boolean'],
        ]);

        // Validate at least one active status
        $hasActiveStatus = collect($validated['statuses'])->contains('is_active', true);
        if (!$hasActiveStatus) {
            return back()->withErrors(['statuses' => 'Workflow must have at least one active status.'])->withInput();
        }

        // Validate unique status names within workflow
        $statusNames = collect($validated['statuses'])->pluck('name')->map(fn ($n) => strtolower(trim($n)));
        if ($statusNames->count() !== $statusNames->unique()->count()) {
            return back()->withErrors(['statuses' => 'Status names must be unique within the workflow.'])->withInput();
        }

        // Create workflow
        $workflow = Workflow::create([
            'workspace_id' => $workspace->id,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'is_default' => false,
            'created_by' => $request->user()->id,
        ]);

        // Create statuses
        foreach ($validated['statuses'] as $index => $statusData) {
            WorkflowStatus::create([
                'workflow_id' => $workflow->id,
                'workspace_id' => $workspace->id,
                'name' => $statusData['name'],
                'color' => $statusData['color'],
                'is_active' => $statusData['is_active'],
                'type' => WorkflowStatus::TYPE_ACTIVE,
                'sort_order' => $index,
                'created_by' => $request->user()->id,
            ]);
        }

        return redirect()->route('workflow.index', $workspace)
            ->with('success', 'Workflow created successfully.');
    }

    /**
     * Show the form for editing a workflow.
     */
    public function edit(Request $request, Workspace $workspace, Workflow $workflow): View|RedirectResponse
    {
        $this->authorizeWorkspaceAccess($request, $workspace);

        if ($workflow->workspace_id !== $workspace->id) {
            abort(404);
        }

        if (!$this->canManageWorkflows($request)) {
            return redirect()->route('workflow.index', $workspace)
                ->with('error', 'You do not have permission to edit workflows.');
        }

        $workflow->load('statuses');

        return view('workflow.edit', [
            'workspace' => $workspace,
            'workflow' => $workflow,
            'colors' => Workflow::COLORS,
        ]);
    }

    /**
     * Update a workflow with its statuses.
     */
    public function update(Request $request, Workspace $workspace, Workflow $workflow): RedirectResponse
    {
        $this->authorizeWorkspaceAccess($request, $workspace);

        if ($workflow->workspace_id !== $workspace->id) {
            abort(404);
        }

        if (!$this->canManageWorkflows($request)) {
            return redirect()->route('workflow.index', $workspace)
                ->with('error', 'You do not have permission to edit workflows.');
        }

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('workflows')->where(fn ($query) => $query->where('workspace_id', $workspace->id))->ignore($workflow->id),
            ],
            'description' => ['nullable', 'string', 'max:500'],
            'statuses' => ['required', 'array', 'min:1'],
            'statuses.*.id' => ['nullable', 'integer'],
            'statuses.*.name' => ['required', 'string', 'max:40'],
            'statuses.*.color' => ['required', Rule::in(array_keys(Workflow::COLORS))],
            'statuses.*.is_active' => ['required', 'boolean'],
        ]);

        // Validate at least one active status
        $hasActiveStatus = collect($validated['statuses'])->contains('is_active', true);
        if (!$hasActiveStatus) {
            return back()->withErrors(['statuses' => 'Workflow must have at least one active status.'])->withInput();
        }

        // Validate unique status names
        $statusNames = collect($validated['statuses'])->pluck('name')->map(fn ($n) => strtolower(trim($n)));
        if ($statusNames->count() !== $statusNames->unique()->count()) {
            return back()->withErrors(['statuses' => 'Status names must be unique within the workflow.'])->withInput();
        }

        // Update workflow
        $workflow->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        // Get existing status IDs
        $existingIds = $workflow->statuses->pluck('id')->toArray();
        $submittedIds = collect($validated['statuses'])->pluck('id')->filter()->toArray();

        // Delete removed statuses
        $toDelete = array_diff($existingIds, $submittedIds);
        WorkflowStatus::whereIn('id', $toDelete)->delete();

        // Update or create statuses
        foreach ($validated['statuses'] as $index => $statusData) {
            if (!empty($statusData['id'])) {
                // Update existing
                WorkflowStatus::where('id', $statusData['id'])
                    ->where('workflow_id', $workflow->id)
                    ->update([
                        'name' => $statusData['name'],
                        'color' => $statusData['color'],
                        'is_active' => $statusData['is_active'],
                        'sort_order' => $index,
                    ]);
            } else {
                // Create new
                WorkflowStatus::create([
                    'workflow_id' => $workflow->id,
                    'workspace_id' => $workspace->id,
                    'name' => $statusData['name'],
                    'color' => $statusData['color'],
                    'is_active' => $statusData['is_active'],
                    'type' => WorkflowStatus::TYPE_ACTIVE,
                    'sort_order' => $index,
                    'created_by' => $request->user()->id,
                ]);
            }
        }

        return redirect()->route('workflow.index', $workspace)
            ->with('success', 'Workflow updated successfully.');
    }

    /**
     * Duplicate a workflow.
     */
    public function duplicate(Request $request, Workspace $workspace, Workflow $workflow): RedirectResponse
    {
        $this->authorizeWorkspaceAccess($request, $workspace);

        if ($workflow->workspace_id !== $workspace->id) {
            abort(404);
        }

        $newWorkflow = $workflow->duplicate($request->user()->id);

        return redirect()->route('workflow.index', $workspace)
            ->with('success', 'Workflow duplicated successfully.');
    }

    /**
     * Archive a workflow.
     */
    public function archive(Request $request, Workspace $workspace, Workflow $workflow): RedirectResponse
    {
        $this->authorizeWorkspaceAccess($request, $workspace);

        if ($workflow->workspace_id !== $workspace->id) {
            abort(404);
        }

        if (!$this->canManageWorkflows($request)) {
            return redirect()->route('workflow.index', $workspace)
                ->with('error', 'You do not have permission to archive workflows.');
        }

        $workflow->archive();

        return redirect()->route('workflow.index', $workspace)
            ->with('success', 'Workflow archived successfully.');
    }

    /**
     * Restore a workflow from archive.
     */
    public function restore(Request $request, Workspace $workspace, Workflow $workflow): RedirectResponse
    {
        $this->authorizeWorkspaceAccess($request, $workspace);

        if ($workflow->workspace_id !== $workspace->id) {
            abort(404);
        }

        if (!$this->canManageWorkflows($request)) {
            return redirect()->route('workflow.index', $workspace)
                ->with('error', 'You do not have permission to restore workflows.');
        }

        $workflow->restore();

        return redirect()->route('workflow.index', $workspace)
            ->with('success', 'Workflow restored successfully.');
    }

    /**
     * Delete a workflow.
     */
    public function destroy(Request $request, Workspace $workspace, Workflow $workflow): RedirectResponse
    {
        $this->authorizeWorkspaceAccess($request, $workspace);

        if ($workflow->workspace_id !== $workspace->id) {
            abort(404);
        }

        if (!$this->canManageWorkflows($request)) {
            return redirect()->route('workflow.index', $workspace)
                ->with('error', 'You do not have permission to delete workflows.');
        }

        if ($workflow->isBuiltIn()) {
            return redirect()->route('workflow.index', $workspace)
                ->with('error', 'Built-in workflows cannot be deleted.');
        }

        // TODO: Check if workflow is in use by tasks

        $workflow->statuses()->delete();
        $workflow->delete();

        return redirect()->route('workflow.index', $workspace)
            ->with('success', 'Workflow deleted successfully.');
    }

    /**
     * Reorder statuses within a workflow (AJAX).
     */
    public function reorderStatuses(Request $request, Workspace $workspace, Workflow $workflow): JsonResponse
    {
        $this->authorizeWorkspaceAccess($request, $workspace);

        if ($workflow->workspace_id !== $workspace->id) {
            return response()->json(['error' => 'Not found'], 404);
        }

        $validated = $request->validate([
            'order' => ['required', 'array'],
            'order.*' => ['required', 'integer'],
        ]);

        foreach ($validated['order'] as $index => $statusId) {
            WorkflowStatus::where('id', $statusId)
                ->where('workflow_id', $workflow->id)
                ->update(['sort_order' => $index]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Check if user has workspace access.
     */
    protected function authorizeWorkspaceAccess(Request $request, Workspace $workspace): void
    {
        if (!$workspace->hasMember($request->user())) {
            abort(403, 'Unauthorized access to workspace.');
        }
    }

    /**
     * Check if user can manage workflows (Owner or Admin).
     */
    protected function canManageWorkflows(Request $request): bool
    {
        return $request->user()->isAdminOrHigher();
    }
}
