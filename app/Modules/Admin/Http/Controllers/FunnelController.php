<?php

declare(strict_types=1);

namespace App\Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Admin\Models\Funnel;
use App\Modules\Admin\Models\FunnelStep;
use App\Modules\Admin\Models\FunnelTag;
use App\Modules\Admin\Services\FunnelService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FunnelController extends Controller
{
    public function __construct(
        protected FunnelService $funnelService
    ) {}

    /**
     * Display list of funnels
     */
    public function index(): View
    {
        $funnels = Funnel::with(['triggerTag', 'steps'])
            ->withCount(['subscribers', 'activeSubscribers'])
            ->latest()
            ->get();

        foreach ($funnels as $funnel) {
            $funnel->stats = $this->funnelService->getFunnelStats($funnel);
        }

        return view('admin::funnel.index', compact('funnels'));
    }

    /**
     * Show create funnel form
     */
    public function create(): View
    {
        $tags = FunnelTag::orderBy('display_name')->get();
        return view('admin::funnel.create', compact('tags'));
    }

    /**
     * Store a new funnel
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'trigger_tag_id' => 'required|exists:funnel_tags,id',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $funnel = Funnel::create($validated);

        return redirect()->route('backoffice.funnel.edit', $funnel)
            ->with('success', 'Funnel created successfully. Now add some steps.');
    }

    /**
     * Show edit funnel form
     */
    public function edit(Funnel $funnel): View
    {
        $funnel->load(['triggerTag', 'steps' => function ($query) {
            $query->with('conditionTag')->orderBy('step_order');
        }]);

        $tags = FunnelTag::orderBy('display_name')->get();
        $stats = $this->funnelService->getFunnelStats($funnel);

        // Get stats for each step
        foreach ($funnel->steps as $step) {
            $step->stats = $this->funnelService->getStepStats($step);
        }

        return view('admin::funnel.edit', compact('funnel', 'tags', 'stats'));
    }

    /**
     * Update a funnel
     */
    public function update(Request $request, Funnel $funnel): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'trigger_tag_id' => 'required|exists:funnel_tags,id',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $funnel->update($validated);

        return back()->with('success', 'Funnel updated successfully.');
    }

    /**
     * Delete a funnel
     */
    public function destroy(Funnel $funnel): RedirectResponse
    {
        $funnel->delete();

        return redirect()->route('backoffice.funnel.index')
            ->with('success', 'Funnel deleted successfully.');
    }

    /**
     * Toggle funnel active status
     */
    public function toggle(Funnel $funnel): RedirectResponse
    {
        $funnel->update(['is_active' => !$funnel->is_active]);

        $status = $funnel->is_active ? 'activated' : 'deactivated';
        return back()->with('success', "Funnel {$status} successfully.");
    }

    /**
     * Duplicate a funnel
     */
    public function duplicate(Funnel $funnel): RedirectResponse
    {
        $newFunnel = $this->funnelService->duplicateFunnel($funnel);

        return redirect()->route('backoffice.funnel.edit', $newFunnel)
            ->with('success', 'Funnel duplicated successfully.');
    }

    /**
     * Store a new step
     */
    public function storeStep(Request $request, Funnel $funnel): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'delay_days' => 'required|integer|min:0',
            'delay_hours' => 'required|integer|min:0|max:23',
            'condition_type' => 'required|in:none,has_tag,missing_tag',
            'condition_tag_id' => 'nullable|required_if:condition_type,has_tag,missing_tag|exists:funnel_tags,id',
            'from_email' => 'required|email|max:255',
            'from_name' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'body_html' => 'required|string',
            'body_text' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['funnel_id'] = $funnel->id;
        $validated['step_order'] = $funnel->steps()->max('step_order') + 1;

        // Clear condition_tag_id if condition_type is none
        if ($validated['condition_type'] === 'none') {
            $validated['condition_tag_id'] = null;
        }

        FunnelStep::create($validated);

        return back()->with('success', 'Step added successfully.');
    }

    /**
     * Update a step
     */
    public function updateStep(Request $request, Funnel $funnel, FunnelStep $step): RedirectResponse
    {
        if ($step->funnel_id !== $funnel->id) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'delay_days' => 'required|integer|min:0',
            'delay_hours' => 'required|integer|min:0|max:23',
            'condition_type' => 'required|in:none,has_tag,missing_tag',
            'condition_tag_id' => 'nullable|required_if:condition_type,has_tag,missing_tag|exists:funnel_tags,id',
            'from_email' => 'required|email|max:255',
            'from_name' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'body_html' => 'required|string',
            'body_text' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        // Clear condition_tag_id if condition_type is none
        if ($validated['condition_type'] === 'none') {
            $validated['condition_tag_id'] = null;
        }

        $step->update($validated);

        return back()->with('success', 'Step updated successfully.');
    }

    /**
     * Delete a step
     */
    public function destroyStep(Funnel $funnel, FunnelStep $step): RedirectResponse
    {
        if ($step->funnel_id !== $funnel->id) {
            abort(404);
        }

        $step->delete();

        // Reorder remaining steps
        $funnel->steps()->orderBy('step_order')->get()
            ->each(function ($s, $index) {
                $s->update(['step_order' => $index + 1]);
            });

        return back()->with('success', 'Step deleted successfully.');
    }

    /**
     * Reorder steps
     */
    public function reorderSteps(Request $request, Funnel $funnel): RedirectResponse
    {
        $validated = $request->validate([
            'order' => 'required|array',
            'order.*' => 'integer|exists:funnel_steps,id',
        ]);

        foreach ($validated['order'] as $position => $stepId) {
            FunnelStep::where('id', $stepId)
                ->where('funnel_id', $funnel->id)
                ->update(['step_order' => $position + 1]);
        }

        return back()->with('success', 'Steps reordered successfully.');
    }
}
