<?php

declare(strict_types=1);

namespace App\Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Admin\Models\Plan;
use App\Modules\Admin\Models\Coupon;
use App\Modules\Admin\Enums\PlanType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Validation\Rule;

class PlansController extends Controller
{
    /**
     * Display plans and coupons with tabs
     */
    public function index(Request $request): View
    {
        $tab = $request->get('tab', 'plans');

        $plans = Plan::ordered()->get();
        $coupons = Coupon::latest()->get();

        return view('admin::plans.index', compact('plans', 'coupons', 'tab'));
    }

    /**
     * Show create plan form
     */
    public function create(): View
    {
        $planTypes = PlanType::cases();
        return view('admin::plans.create', compact('planTypes'));
    }

    /**
     * Store a new plan
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => ['required', Rule::enum(PlanType::class)],
            'workspace_limit' => 'required|integer|min:0',
            'team_member_limit' => 'required|integer|min:0',
            'storage_limit_gb' => 'required|integer|min:0',
            'price_1_month' => 'required|numeric|min:0',
            'price_3_month' => 'required|numeric|min:0',
            'price_6_month' => 'required|numeric|min:0',
            'price_12_month' => 'required|numeric|min:0',
            'price_3_year' => 'required|numeric|min:0',
            'price_5_year' => 'required|numeric|min:0',
            'is_active' => 'boolean',
            'is_popular' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $validated['is_popular'] = $request->boolean('is_popular');

        Plan::create($validated);

        return redirect()->route('backoffice.plans.index', ['tab' => 'plans'])
            ->with('success', 'Plan created successfully.');
    }

    /**
     * Show edit plan form
     */
    public function edit(Plan $plan): View
    {
        $planTypes = PlanType::cases();
        return view('admin::plans.edit', compact('plan', 'planTypes'));
    }

    /**
     * Update a plan
     */
    public function update(Request $request, Plan $plan): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => ['required', Rule::enum(PlanType::class)],
            'workspace_limit' => 'required|integer|min:0',
            'team_member_limit' => 'required|integer|min:0',
            'storage_limit_gb' => 'required|integer|min:0',
            'price_1_month' => 'required|numeric|min:0',
            'price_3_month' => 'required|numeric|min:0',
            'price_6_month' => 'required|numeric|min:0',
            'price_12_month' => 'required|numeric|min:0',
            'price_3_year' => 'required|numeric|min:0',
            'price_5_year' => 'required|numeric|min:0',
            'is_active' => 'boolean',
            'is_popular' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $validated['is_popular'] = $request->boolean('is_popular');

        $plan->update($validated);

        return redirect()->route('backoffice.plans.index', ['tab' => 'plans'])
            ->with('success', 'Plan updated successfully.');
    }

    /**
     * Delete a plan
     */
    public function destroy(Plan $plan): RedirectResponse
    {
        $plan->delete();

        return redirect()->route('backoffice.plans.index', ['tab' => 'plans'])
            ->with('success', 'Plan deleted successfully.');
    }

    /**
     * Toggle plan status
     */
    public function toggleStatus(Plan $plan): RedirectResponse
    {
        $plan->update(['is_active' => !$plan->is_active]);

        return back()->with('success', 'Plan status updated successfully.');
    }
}
