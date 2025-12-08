@extends('layouts.app')

@section('content')
<div class="min-h-screen p-4 md:p-6">
    <div class="max-w-6xl mx-auto">
        <!-- Breadcrumb -->
        <div class="text-sm breadcrumbs mb-4">
            <ul>
                <li><a href="{{ route('settings.index') }}">Settings</a></li>
                <li><a href="{{ route('settings.billing') }}">Billing & Plans</a></li>
                <li>Choose Plan</li>
            </ul>
        </div>

        <!-- Page Header -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-base-content">Choose Your Plan</h1>
            <p class="text-base-content/60 mt-2">Select the plan that best fits your needs</p>
        </div>

        <div class="mb-6">
            @include('partials.alerts')
        </div>

        <!-- Billing Cycle Toggle -->
        <div class="flex justify-center mb-8">
            <div class="join">
                <button type="button" class="btn join-item billing-cycle-btn {{ $billingCycle === '1_month' ? 'btn-primary' : 'btn-ghost' }}" data-cycle="1_month">Monthly</button>
                <button type="button" class="btn join-item billing-cycle-btn {{ $billingCycle === '12_month' ? 'btn-primary' : 'btn-ghost' }}" data-cycle="12_month">
                    Annual
                    <span class="badge badge-success badge-sm ml-1">Save 20%</span>
                </button>
                <button type="button" class="btn join-item billing-cycle-btn {{ $billingCycle === '3_year' ? 'btn-primary' : 'btn-ghost' }}" data-cycle="3_year">3 Years</button>
            </div>
        </div>

        <!-- Plans Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            @foreach($plans as $plan)
                <div class="card bg-base-100 shadow-xl {{ $plan->is_popular ? 'border-2 border-primary' : '' }} {{ $company->plan_id === $plan->id ? 'ring-2 ring-success' : '' }}">
                    @if($plan->is_popular)
                        <div class="absolute -top-3 left-1/2 -translate-x-1/2">
                            <span class="badge badge-primary">Most Popular</span>
                        </div>
                    @endif
                    @if($company->plan_id === $plan->id)
                        <div class="absolute -top-3 right-4">
                            <span class="badge badge-success">Current Plan</span>
                        </div>
                    @endif
                    <div class="card-body">
                        <h2 class="card-title justify-center text-xl">{{ $plan->name }}</h2>
                        <div class="text-center my-4">
                            @if($plan->isFree())
                                <span class="text-4xl font-bold">Free</span>
                            @else
                                @php
                                    $prices = [
                                        '1_month' => $plan->price_1_month,
                                        '3_month' => $plan->price_3_month,
                                        '6_month' => $plan->price_6_month,
                                        '12_month' => $plan->price_12_month,
                                        '3_year' => $plan->price_3_year,
                                        '5_year' => $plan->price_5_year,
                                    ];
                                    $currentPrice = $plan->{'price_' . $billingCycle} ?? $plan->price_1_month;
                                @endphp
                                <span class="text-4xl font-bold price-display" data-prices="{{ json_encode($prices) }}">${{ number_format($currentPrice, 2) }}</span>
                                <span class="text-base-content/60 cycle-label">
                                    /{{ $billingCycle === '1_month' ? 'mo' : ($billingCycle === '12_month' ? 'yr' : '3yr') }}
                                </span>
                            @endif
                        </div>

                        <div class="divider my-2"></div>

                        <!-- Features -->
                        <ul class="space-y-2 mb-6">
                            <li class="flex items-center gap-2">
                                <span class="icon-[tabler--check] size-5 text-success"></span>
                                <span>
                                    @if($plan->workspace_limit == 0)
                                        <strong>Unlimited</strong> Workspaces
                                    @else
                                        <strong>{{ $plan->workspace_limit }}</strong> Workspace{{ $plan->workspace_limit > 1 ? 's' : '' }}
                                    @endif
                                </span>
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="icon-[tabler--check] size-5 text-success"></span>
                                <span>
                                    @if($plan->team_member_limit == 0)
                                        <strong>Unlimited</strong> Team Members
                                    @else
                                        <strong>{{ $plan->team_member_limit }}</strong> Team Member{{ $plan->team_member_limit > 1 ? 's' : '' }}
                                    @endif
                                </span>
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="icon-[tabler--check] size-5 text-success"></span>
                                <span>
                                    @if($plan->storage_limit_gb == 0)
                                        <strong>Unlimited</strong> Storage
                                    @else
                                        <strong>{{ $plan->storage_limit_gb }} GB</strong> Storage
                                    @endif
                                </span>
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="icon-[tabler--check] size-5 text-success"></span>
                                <span>Task Management</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="icon-[tabler--check] size-5 text-success"></span>
                                <span>Discussions & Ideas</span>
                            </li>
                            @if(!$plan->isFree())
                                <li class="flex items-center gap-2">
                                    <span class="icon-[tabler--check] size-5 text-success"></span>
                                    <span>Priority Support</span>
                                </li>
                            @endif
                        </ul>

                        <div class="card-actions justify-center mt-auto">
                            @if($company->plan_id === $plan->id)
                                <button class="btn btn-success btn-block" disabled>
                                    <span class="icon-[tabler--check] size-5"></span>
                                    Current Plan
                                </button>
                            @else
                                <form action="{{ route('settings.billing.subscribe', $plan) }}" method="POST" class="w-full">
                                    @csrf
                                    <input type="hidden" name="billing_cycle" class="billing-cycle-input" value="{{ $billingCycle }}" />
                                    @php
                                        $priceField = 'price_' . $billingCycle;
                                        $currentPlanPrice = $company->plan ? ($company->plan->{$priceField} ?? 0) : 0;
                                        $newPlanPrice = $plan->{$priceField} ?? 0;
                                        $isDowngrade = $company->plan && $currentPlanPrice > $newPlanPrice;
                                    @endphp
                                    <button type="submit" class="btn {{ $plan->is_popular ? 'btn-primary' : 'btn-outline' }} btn-block">
                                        @if($plan->isFree())
                                            Switch to Free
                                        @elseif($isDowngrade)
                                            Downgrade
                                        @elseif($company->plan)
                                            Upgrade
                                        @else
                                            Get Started
                                        @endif
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- FAQ Section -->
        <div class="card bg-base-100 shadow">
            <div class="card-body">
                <h2 class="card-title text-lg mb-4">
                    <span class="icon-[tabler--help-circle] size-5"></span>
                    Frequently Asked Questions
                </h2>
                <div class="space-y-4">
                    <div class="collapse collapse-arrow bg-base-200">
                        <input type="radio" name="faq-accordion" checked="checked" />
                        <div class="collapse-title font-medium">Can I change my plan later?</div>
                        <div class="collapse-content">
                            <p class="text-base-content/70">Yes! You can upgrade or downgrade your plan at any time. Changes will take effect immediately.</p>
                        </div>
                    </div>
                    <div class="collapse collapse-arrow bg-base-200">
                        <input type="radio" name="faq-accordion" />
                        <div class="collapse-title font-medium">What happens when I upgrade?</div>
                        <div class="collapse-content">
                            <p class="text-base-content/70">When you upgrade, you'll immediately get access to all the new features. We'll prorate your billing so you only pay the difference.</p>
                        </div>
                    </div>
                    <div class="collapse collapse-arrow bg-base-200">
                        <input type="radio" name="faq-accordion" />
                        <div class="collapse-title font-medium">Do you offer refunds?</div>
                        <div class="collapse-content">
                            <p class="text-base-content/70">Yes, we offer a 30-day money-back guarantee. If you're not satisfied, contact our support team for a full refund.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Back Link -->
        <div class="text-center mt-6">
            <a href="{{ route('settings.billing') }}" class="btn btn-ghost">
                <span class="icon-[tabler--arrow-left] size-5"></span>
                Back to Billing
            </a>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const cycleButtons = document.querySelectorAll('.billing-cycle-btn');
    const cycleInputs = document.querySelectorAll('.billing-cycle-input');
    const priceDisplays = document.querySelectorAll('.price-display');
    const cycleLabels = document.querySelectorAll('.cycle-label');

    const cycleLabelMap = {
        '1_month': '/mo',
        '3_month': '/3mo',
        '6_month': '/6mo',
        '12_month': '/yr',
        '3_year': '/3yr',
        '5_year': '/5yr'
    };

    cycleButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const cycle = this.dataset.cycle;

            // Update button states
            cycleButtons.forEach(b => {
                b.classList.remove('btn-primary');
                b.classList.add('btn-ghost');
            });
            this.classList.remove('btn-ghost');
            this.classList.add('btn-primary');

            // Update hidden inputs
            cycleInputs.forEach(input => {
                input.value = cycle;
            });

            // Update prices
            priceDisplays.forEach(display => {
                const prices = JSON.parse(display.dataset.prices);
                display.textContent = '$' + parseFloat(prices[cycle]).toFixed(2);
            });

            // Update labels
            cycleLabels.forEach(label => {
                label.textContent = cycleLabelMap[cycle];
            });
        });
    });
});
</script>
@endpush
@endsection
