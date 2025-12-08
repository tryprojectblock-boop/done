<?php

declare(strict_types=1);

namespace App\Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Admin\Models\Coupon;
use App\Modules\Admin\Models\Plan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CouponsController extends Controller
{
    /**
     * Show create coupon form
     */
    public function create(): View
    {
        $plans = Plan::active()->ordered()->get();
        return view('admin::coupons.create', compact('plans'));
    }

    /**
     * Store a new coupon
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:coupons,code',
            'discount_percent' => 'required|numeric|min:1|max:100',
            'is_active' => 'boolean',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'usage_limit' => 'nullable|integer|min:1',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $validated['code'] = strtoupper($validated['code']);

        Coupon::create($validated);

        return redirect()->route('backoffice.plans.index', ['tab' => 'coupons'])
            ->with('success', 'Coupon created successfully.');
    }

    /**
     * Show edit coupon form
     */
    public function edit(Coupon $coupon): View
    {
        $plans = Plan::active()->ordered()->get();
        return view('admin::coupons.edit', compact('coupon', 'plans'));
    }

    /**
     * Update a coupon
     */
    public function update(Request $request, Coupon $coupon): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:coupons,code,' . $coupon->id,
            'discount_percent' => 'required|numeric|min:1|max:100',
            'is_active' => 'boolean',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'usage_limit' => 'nullable|integer|min:1',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $validated['code'] = strtoupper($validated['code']);

        $coupon->update($validated);

        return redirect()->route('backoffice.plans.index', ['tab' => 'coupons'])
            ->with('success', 'Coupon updated successfully.');
    }

    /**
     * Delete a coupon
     */
    public function destroy(Coupon $coupon): RedirectResponse
    {
        $coupon->delete();

        return redirect()->route('backoffice.plans.index', ['tab' => 'coupons'])
            ->with('success', 'Coupon deleted successfully.');
    }

    /**
     * Toggle coupon status
     */
    public function toggleStatus(Coupon $coupon): RedirectResponse
    {
        $coupon->update(['is_active' => !$coupon->is_active]);

        return back()->with('success', 'Coupon status updated successfully.');
    }
}
