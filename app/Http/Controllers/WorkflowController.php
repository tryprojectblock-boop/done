<?php

namespace App\Http\Controllers;

use App\Models\Workflow;
use App\Models\WorkflowStatus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class WorkflowController extends Controller
{
    /**
     * Display all workflows for the user's company.
     */
    public function index(Request $request): View
    {
        $companyId = $request->user()->company_id;

        $workflows = Workflow::with(['statuses'])
            ->forCompany($companyId)
            ->active()
            ->orderByRaw('is_default DESC, name ASC')
            ->get();

        $archivedWorkflows = Workflow::with(['statuses'])
            ->forCompany($companyId)
            ->archived()
            ->latest()
            ->get();

        return view('workflow.index', [
            'workflows' => $workflows,
            'archivedWorkflows' => $archivedWorkflows,
            'colors' => Workflow::COLORS,
            'canManage' => $this->canManageWorkflows($request),
        ]);
    }

    /**
     * Show form for creating a new workflow.
     */
    public function create(Request $request): View|RedirectResponse
    {
        if (!$this->canManageWorkflows($request)) {
            return redirect()->route('workflows.index')
                ->with('error', 'You do not have permission to create workflows.');
        }

        return view('workflow.create', [
            'colors' => Workflow::COLORS,
        ]);
    }

    /**
     * Store a new workflow.
     */
    public function store(Request $request): RedirectResponse
    {
        if (!$this->canManageWorkflows($request)) {
            return redirect()->route('workflows.index')
                ->with('error', 'You do not have permission to create workflows.');
        }

        $companyId = $request->user()->company_id;

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('workflows')->where(fn ($query) => $query->where('company_id', $companyId)),
            ],
            'description' => ['nullable', 'string', 'max:500'],
            'statuses' => ['required', 'array', 'min:1'],
            'statuses.*.name' => ['required', 'string', 'max:40'],
            'statuses.*.color' => ['required', Rule::in(array_keys(Workflow::COLORS))],
            'statuses.*.is_active' => ['required', 'boolean'],
            'statuses.*.responsibility' => ['nullable', Rule::in([WorkflowStatus::RESPONSIBILITY_CREATOR, WorkflowStatus::RESPONSIBILITY_ASSIGNEE])],
        ]);

        $hasActiveStatus = collect($validated['statuses'])->contains('is_active', true);
        if (!$hasActiveStatus) {
            return back()->withErrors(['statuses' => 'Workflow must have at least one active status.'])->withInput();
        }

        $statusNames = collect($validated['statuses'])->pluck('name')->map(fn ($n) => strtolower(trim($n)));
        if ($statusNames->count() !== $statusNames->unique()->count()) {
            return back()->withErrors(['statuses' => 'Status names must be unique within the workflow.'])->withInput();
        }

        $workflow = Workflow::create([
            'company_id' => $companyId,
            'workspace_id' => null,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'is_default' => false,
            'created_by' => $request->user()->id,
        ]);

        foreach ($validated['statuses'] as $index => $statusData) {
            WorkflowStatus::create([
                'workflow_id' => $workflow->id,
                'workspace_id' => null,
                'name' => $statusData['name'],
                'color' => $statusData['color'],
                'is_active' => $statusData['is_active'],
                'responsibility' => $statusData['responsibility'] ?? WorkflowStatus::RESPONSIBILITY_ASSIGNEE,
                'type' => WorkflowStatus::TYPE_ACTIVE,
                'sort_order' => $index,
                'created_by' => $request->user()->id,
            ]);
        }

        return redirect()->route('workflows.index')
            ->with('success', 'Workflow created successfully.');
    }

    /**
     * Show form for editing a workflow.
     */
    public function edit(Request $request, Workflow $workflow): View|RedirectResponse
    {
        $this->authorizeWorkflowAccess($request, $workflow);

        if (!$this->canManageWorkflows($request)) {
            return redirect()->route('workflows.index')
                ->with('error', 'You do not have permission to edit workflows.');
        }

        $workflow->load('statuses');

        return view('workflow.edit', [
            'workflow' => $workflow,
            'colors' => Workflow::COLORS,
        ]);
    }

    /**
     * Update a workflow.
     */
    public function update(Request $request, Workflow $workflow): RedirectResponse
    {
        $this->authorizeWorkflowAccess($request, $workflow);

        if (!$this->canManageWorkflows($request)) {
            return redirect()->route('workflows.index')
                ->with('error', 'You do not have permission to edit workflows.');
        }

        $companyId = $request->user()->company_id;

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('workflows')->where(fn ($query) => $query->where('company_id', $companyId))->ignore($workflow->id),
            ],
            'description' => ['nullable', 'string', 'max:500'],
            'statuses' => ['required', 'array', 'min:1'],
            'statuses.*.id' => ['nullable', 'integer'],
            'statuses.*.name' => ['required', 'string', 'max:40'],
            'statuses.*.color' => ['required', Rule::in(array_keys(Workflow::COLORS))],
            'statuses.*.is_active' => ['required', 'boolean'],
            'statuses.*.responsibility' => ['nullable', Rule::in([WorkflowStatus::RESPONSIBILITY_CREATOR, WorkflowStatus::RESPONSIBILITY_ASSIGNEE])],
        ]);

        $hasActiveStatus = collect($validated['statuses'])->contains('is_active', true);
        if (!$hasActiveStatus) {
            return back()->withErrors(['statuses' => 'Workflow must have at least one active status.'])->withInput();
        }

        $statusNames = collect($validated['statuses'])->pluck('name')->map(fn ($n) => strtolower(trim($n)));
        if ($statusNames->count() !== $statusNames->unique()->count()) {
            return back()->withErrors(['statuses' => 'Status names must be unique within the workflow.'])->withInput();
        }

        $workflow->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        $existingIds = $workflow->statuses->pluck('id')->toArray();
        $submittedIds = collect($validated['statuses'])->pluck('id')->filter()->toArray();
        $toDelete = array_diff($existingIds, $submittedIds);
        WorkflowStatus::whereIn('id', $toDelete)->delete();

        foreach ($validated['statuses'] as $index => $statusData) {
            if (!empty($statusData['id'])) {
                WorkflowStatus::where('id', $statusData['id'])
                    ->where('workflow_id', $workflow->id)
                    ->update([
                        'name' => $statusData['name'],
                        'color' => $statusData['color'],
                        'is_active' => $statusData['is_active'],
                        'responsibility' => $statusData['responsibility'] ?? WorkflowStatus::RESPONSIBILITY_ASSIGNEE,
                        'sort_order' => $index,
                    ]);
            } else {
                WorkflowStatus::create([
                    'workflow_id' => $workflow->id,
                    'workspace_id' => null,
                    'name' => $statusData['name'],
                    'color' => $statusData['color'],
                    'is_active' => $statusData['is_active'],
                    'responsibility' => $statusData['responsibility'] ?? WorkflowStatus::RESPONSIBILITY_ASSIGNEE,
                    'type' => WorkflowStatus::TYPE_ACTIVE,
                    'sort_order' => $index,
                    'created_by' => $request->user()->id,
                ]);
            }
        }

        return redirect()->route('workflows.index')
            ->with('success', 'Workflow updated successfully.');
    }

    /**
     * Duplicate a workflow.
     */
    public function duplicate(Request $request, Workflow $workflow): RedirectResponse
    {
        $this->authorizeWorkflowAccess($request, $workflow);

        $newWorkflow = $workflow->replicate();
        $newWorkflow->name = $workflow->name . ' (Copy)';
        $newWorkflow->is_default = false;
        $newWorkflow->created_by = $request->user()->id;
        $newWorkflow->save();

        foreach ($workflow->statuses as $status) {
            $newStatus = $status->replicate();
            $newStatus->workflow_id = $newWorkflow->id;
            $newStatus->created_by = $request->user()->id;
            $newStatus->save();
        }

        return redirect()->route('workflows.index')
            ->with('success', 'Workflow duplicated successfully.');
    }

    /**
     * Archive a workflow.
     */
    public function archive(Request $request, Workflow $workflow): RedirectResponse
    {
        $this->authorizeWorkflowAccess($request, $workflow);

        if (!$this->canManageWorkflows($request)) {
            return redirect()->route('workflows.index')
                ->with('error', 'You do not have permission to archive workflows.');
        }

        $workflow->archive();

        return redirect()->route('workflows.index')
            ->with('success', 'Workflow archived successfully.');
    }

    /**
     * Restore a workflow.
     */
    public function restore(Request $request, Workflow $workflow): RedirectResponse
    {
        $this->authorizeWorkflowAccess($request, $workflow);

        if (!$this->canManageWorkflows($request)) {
            return redirect()->route('workflows.index')
                ->with('error', 'You do not have permission to restore workflows.');
        }

        $workflow->restore();

        return redirect()->route('workflows.index')
            ->with('success', 'Workflow restored successfully.');
    }

    /**
     * Delete a workflow.
     */
    public function destroy(Request $request, Workflow $workflow): RedirectResponse
    {
        $this->authorizeWorkflowAccess($request, $workflow);

        if (!$this->canManageWorkflows($request)) {
            return redirect()->route('workflows.index')
                ->with('error', 'You do not have permission to delete workflows.');
        }

        if ($workflow->isBuiltIn()) {
            return redirect()->route('workflows.index')
                ->with('error', 'Built-in workflows cannot be deleted.');
        }

        $workflow->statuses()->delete();
        $workflow->delete();

        return redirect()->route('workflows.index')
            ->with('success', 'Workflow deleted successfully.');
    }

    /**
     * Check if user has access to the workflow (belongs to their company).
     */
    protected function authorizeWorkflowAccess(Request $request, Workflow $workflow): void
    {
        if ($workflow->company_id !== $request->user()->company_id) {
            abort(403, 'Unauthorized access to workflow.');
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
