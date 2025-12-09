@extends('admin::layouts.app')

@section('title', 'Add Coupon')
@section('page-title', 'Add Coupon')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Breadcrumb -->
    <div class="text-sm breadcrumbs">
        <ul>
            <li><a href="{{ route('backoffice.plans.index', ['tab' => 'coupons']) }}">Plans & Coupons</a></li>
            <li>Add Coupon</li>
        </ul>
    </div>

    <!-- Header -->
    <div>
        <h1 class="text-2xl font-bold text-base-content">Add Coupon</h1>
        <p class="text-base-content/60">Create a new discount coupon</p>
    </div>

    <form action="{{ route('backoffice.coupons.store') }}" method="POST">
        @csrf

        <!-- Basic Info -->
        <div class="card bg-base-100 shadow mb-6">
            <div class="card-body">
                <h2 class="card-title text-lg mb-4">Coupon Information</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-control">
                        <label class="label" for="coupon-name">
                            <span class="label-text font-medium">Coupon Name</span>
                        </label>
                        <input type="text" name="name" id="coupon-name" value="{{ old('name') }}" class="input input-bordered @error('name') input-error @enderror" placeholder="e.g., Summer Sale, Black Friday" required />
                        @error('name')
                            <div class="label"><span class="label-text-alt text-error">{{ $message }}</span></div>
                        @enderror
                    </div>

                    <div class="form-control">
                        <label class="label" for="coupon-code">
                            <span class="label-text font-medium">Coupon Code</span>
                        </label>
                        <input type="text" name="code" id="coupon-code" value="{{ old('code') }}" class="input input-bordered font-mono uppercase @error('code') input-error @enderror" placeholder="e.g., SUMMER2024" required aria-describedby="coupon-code-hint" />
                        <div class="label" id="coupon-code-hint">
                            <span class="label-text-alt text-base-content/60">Code will be converted to uppercase</span>
                        </div>
                        @error('code')
                            <div class="label"><span class="label-text-alt text-error">{{ $message }}</span></div>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                    <div class="form-control">
                        <label class="label" for="discount-percent">
                            <span class="label-text font-medium">Discount Percentage</span>
                        </label>
                        <div class="join">
                            <input type="number" name="discount_percent" id="discount-percent" value="{{ old('discount_percent', 10) }}" class="input input-bordered join-item w-full @error('discount_percent') input-error @enderror" min="1" max="100" step="0.01" required />
                            <span class="btn btn-disabled join-item">%</span>
                        </div>
                        @error('discount_percent')
                            <div class="label"><span class="label-text-alt text-error">{{ $message }}</span></div>
                        @enderror
                    </div>

                    <div class="form-control">
                        <label class="label" for="usage-limit">
                            <span class="label-text font-medium">Usage Limit</span>
                        </label>
                        <input type="number" name="usage_limit" id="usage-limit" value="{{ old('usage_limit') }}" class="input input-bordered @error('usage_limit') input-error @enderror" min="1" placeholder="Leave empty for unlimited" aria-describedby="usage-limit-hint" />
                        <div class="label" id="usage-limit-hint">
                            <span class="label-text-alt text-base-content/60">Maximum number of times this coupon can be used</span>
                        </div>
                        @error('usage_limit')
                            <div class="label"><span class="label-text-alt text-error">{{ $message }}</span></div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Validity Period -->
        <div class="card bg-base-100 shadow mb-6">
            <div class="card-body">
                <h2 class="card-title text-lg mb-4">Validity Period</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-control">
                        <label class="label" for="start-date">
                            <span class="label-text font-medium">Start Date</span>
                        </label>
                        <input type="date" name="start_date" id="start-date" value="{{ old('start_date', date('Y-m-d')) }}" class="input input-bordered @error('start_date') input-error @enderror" required />
                        @error('start_date')
                            <div class="label"><span class="label-text-alt text-error">{{ $message }}</span></div>
                        @enderror
                    </div>

                    <div class="form-control">
                        <label class="label" for="end-date">
                            <span class="label-text font-medium">End Date</span>
                        </label>
                        <input type="date" name="end_date" id="end-date" value="{{ old('end_date') }}" class="input input-bordered @error('end_date') input-error @enderror" aria-describedby="end-date-hint" />
                        <div class="label" id="end-date-hint">
                            <span class="label-text-alt text-base-content/60">Leave empty for no expiration</span>
                        </div>
                        @error('end_date')
                            <div class="label"><span class="label-text-alt text-error">{{ $message }}</span></div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Options -->
        <div class="card bg-base-100 shadow mb-6">
            <div class="card-body">
                <h2 class="card-title text-lg mb-4">Options</h2>

                <div class="form-control">
                    <label class="label cursor-pointer justify-start gap-4">
                        <input type="checkbox" name="is_active" value="1" class="toggle toggle-primary" {{ old('is_active', true) ? 'checked' : '' }} />
                        <div>
                            <span class="label-text font-medium">Active</span>
                            <p class="text-xs text-base-content/60">Coupon can be applied to purchases</p>
                        </div>
                    </label>
                </div>
            </div>
        </div>

        <!-- Discount Preview -->
        @if($plans->count() > 0)
        <div class="card bg-base-100 shadow mb-6">
            <div class="card-body">
                <h2 class="card-title text-lg mb-4">
                    <span class="icon-[tabler--calculator] size-5"></span>
                    Discount Preview
                </h2>
                <p class="text-sm text-base-content/60 mb-4">Preview of how prices will look with a <strong id="preview-discount">10</strong>% discount applied</p>

                <div class="overflow-x-auto">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Plan</th>
                                <th>1 Month</th>
                                <th>3 Months</th>
                                <th>6 Months</th>
                                <th>12 Months</th>
                                <th>3 Years</th>
                                <th>5 Years</th>
                            </tr>
                        </thead>
                        <tbody id="discount-preview-body">
                            @foreach($plans as $plan)
                            <tr data-plan-id="{{ $plan->id }}"
                                data-price-1="{{ $plan->price_1_month }}"
                                data-price-3="{{ $plan->price_3_month }}"
                                data-price-6="{{ $plan->price_6_month }}"
                                data-price-12="{{ $plan->price_12_month }}"
                                data-price-36="{{ $plan->price_3_year }}"
                                data-price-60="{{ $plan->price_5_year }}">
                                <td class="font-medium">{{ $plan->name }}</td>
                                <td>
                                    <span class="text-base-content/40 line-through text-xs">${{ number_format($plan->price_1_month, 2) }}</span>
                                    <span class="text-success font-medium discounted-price-1">${{ number_format($plan->price_1_month * 0.9, 2) }}</span>
                                </td>
                                <td>
                                    <span class="text-base-content/40 line-through text-xs">${{ number_format($plan->price_3_month, 2) }}</span>
                                    <span class="text-success font-medium discounted-price-3">${{ number_format($plan->price_3_month * 0.9, 2) }}</span>
                                </td>
                                <td>
                                    <span class="text-base-content/40 line-through text-xs">${{ number_format($plan->price_6_month, 2) }}</span>
                                    <span class="text-success font-medium discounted-price-6">${{ number_format($plan->price_6_month * 0.9, 2) }}</span>
                                </td>
                                <td>
                                    <span class="text-base-content/40 line-through text-xs">${{ number_format($plan->price_12_month, 2) }}</span>
                                    <span class="text-success font-medium discounted-price-12">${{ number_format($plan->price_12_month * 0.9, 2) }}</span>
                                </td>
                                <td>
                                    <span class="text-base-content/40 line-through text-xs">${{ number_format($plan->price_3_year, 2) }}</span>
                                    <span class="text-success font-medium discounted-price-36">${{ number_format($plan->price_3_year * 0.9, 2) }}</span>
                                </td>
                                <td>
                                    <span class="text-base-content/40 line-through text-xs">${{ number_format($plan->price_5_year, 2) }}</span>
                                    <span class="text-success font-medium discounted-price-60">${{ number_format($plan->price_5_year * 0.9, 2) }}</span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

        <!-- Submit -->
        <div class="flex justify-end gap-2">
            <a href="{{ route('backoffice.plans.index', ['tab' => 'coupons']) }}" class="btn btn-ghost">Cancel</a>
            <button type="submit" class="btn btn-primary">
                <span class="icon-[tabler--plus] size-5"></span>
                Create Coupon
            </button>
        </div>
    </form>
</div>

@if($plans->count() > 0)
<script>
document.addEventListener('DOMContentLoaded', function() {
    const discountInput = document.querySelector('input[name="discount_percent"]');
    const previewDiscount = document.getElementById('preview-discount');
    const rows = document.querySelectorAll('#discount-preview-body tr');

    function updatePreview() {
        const discount = parseFloat(discountInput.value) || 0;
        const multiplier = 1 - (discount / 100);

        previewDiscount.textContent = discount;

        rows.forEach(row => {
            const prices = {
                1: parseFloat(row.dataset.price1) || 0,
                3: parseFloat(row.dataset.price3) || 0,
                6: parseFloat(row.dataset.price6) || 0,
                12: parseFloat(row.dataset.price12) || 0,
                36: parseFloat(row.dataset.price36) || 0,
                60: parseFloat(row.dataset.price60) || 0
            };

            Object.keys(prices).forEach(key => {
                const discountedPrice = prices[key] * multiplier;
                const element = row.querySelector(`.discounted-price-${key}`);
                if (element) {
                    element.textContent = '$' + discountedPrice.toFixed(2);
                }
            });
        });
    }

    discountInput.addEventListener('input', updatePreview);
});
</script>
@endif
@endsection
