@extends('layouts.app')

@section('content')
<div class="min-h-screen p-4 md:p-6">
    <div class="max-w-4xl mx-auto">
        <!-- Breadcrumb -->
        <div class="text-sm breadcrumbs mb-4">
            <ul>
                <li><a href="{{ route('settings.index') }}">Settings</a></li>
                <li>Billing & Plans</li>
            </ul>
        </div>

        <!-- Page Header -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-base-content">Billing & Plans</h1>
            <p class="text-base-content/60">Manage your subscription and billing information</p>
        </div>

        <div class="mb-6">
            @include('partials.alerts')
        </div>

        <!-- Current Plan Card -->
        <div class="card bg-base-100 shadow mb-6">
            <div class="card-body">
                <div class="flex items-start justify-between mb-4">
                    <h2 class="card-title text-lg">
                        <span class="icon-[tabler--credit-card] size-5"></span>
                        Current Plan
                    </h2>
                    @if($company->isOnTrial())
                        <span class="badge badge-warning">Trial</span>
                    @elseif($company->hasActiveSubscription())
                        <span class="badge badge-success">Active</span>
                    @elseif($company->isSubscriptionExpired())
                        <span class="badge badge-error">Expired</span>
                    @else
                        <span class="badge badge-ghost">No Plan</span>
                    @endif
                </div>

                @if($company->plan)
                    <!-- Has a Plan -->
                    <div class="bg-base-200 rounded-lg p-4 mb-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-xl font-bold text-base-content">{{ $company->plan->name }}</h3>
                                <p class="text-sm text-base-content/60">
                                    {{ $company->plan->type->label() }} Plan
                                    @if($company->billing_cycle)
                                        &bull; {{ $company->getBillingCycleLabel() }}
                                    @endif
                                </p>
                            </div>
                            <div class="text-right">
                                @if($company->plan->isFree())
                                    <span class="text-2xl font-bold text-success">Free</span>
                                @else
                                    <span class="text-2xl font-bold">${{ number_format($company->getCurrentPlanPrice(), 2) }}</span>
                                    <p class="text-sm text-base-content/60">per {{ $company->getBillingCycleLabel() }}</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Plan Details -->
                    <div class="grid grid-cols-3 gap-4 mb-4">
                        <div class="text-center p-3 bg-base-200 rounded-lg">
                            <div class="text-2xl font-bold text-primary">
                                @if($company->plan->workspace_limit == 0)
                                    <span class="icon-[tabler--infinity] size-8"></span>
                                @else
                                    {{ $company->plan->workspace_limit }}
                                @endif
                            </div>
                            <div class="text-xs text-base-content/60">Workspaces</div>
                        </div>
                        <div class="text-center p-3 bg-base-200 rounded-lg">
                            <div class="text-2xl font-bold text-primary">
                                @if($company->plan->team_member_limit == 0)
                                    <span class="icon-[tabler--infinity] size-8"></span>
                                @else
                                    {{ $company->plan->team_member_limit }}
                                @endif
                            </div>
                            <div class="text-xs text-base-content/60">Team Members</div>
                        </div>
                        <div class="text-center p-3 bg-base-200 rounded-lg">
                            <div class="text-2xl font-bold text-primary">
                                @if($company->plan->storage_limit_gb == 0)
                                    <span class="icon-[tabler--infinity] size-8"></span>
                                @else
                                    {{ $company->plan->storage_limit_gb }} GB
                                @endif
                            </div>
                            <div class="text-xs text-base-content/60">Storage</div>
                        </div>
                    </div>

                    <!-- Subscription Dates -->
                    @if($company->subscription_ends_at)
                        <div class="flex items-center justify-between text-sm border-t border-base-200 pt-4">
                            <div>
                                <span class="text-base-content/60">Started:</span>
                                <span class="font-medium ml-1">{{ $company->subscription_starts_at?->format('M d, Y') ?? 'N/A' }}</span>
                            </div>
                            <div>
                                <span class="text-base-content/60">Renews:</span>
                                <span class="font-medium ml-1">{{ $company->subscription_ends_at->format('M d, Y') }}</span>
                                @if($company->subscriptionDaysRemaining() <= 7 && $company->subscriptionDaysRemaining() > 0)
                                    <span class="badge badge-warning badge-sm ml-2">{{ $company->subscriptionDaysRemaining() }} days left</span>
                                @endif
                            </div>
                        </div>
                    @endif

                    <!-- Coupon Applied -->
                    @if($company->applied_coupon_code)
                        <div class="flex items-center gap-2 text-sm border-t border-base-200 pt-4 mt-4">
                            <span class="icon-[tabler--discount] size-4 text-success"></span>
                            <span class="text-base-content/60">Coupon Applied:</span>
                            <code class="bg-success/10 text-success px-2 py-0.5 rounded font-mono">{{ $company->applied_coupon_code }}</code>
                            <span class="badge badge-success badge-sm">{{ $company->discount_percent }}% off</span>
                        </div>
                    @endif

                @elseif($company->isOnTrial())
                    <!-- On Trial -->
                    <div class="bg-warning/10 border border-warning/30 rounded-lg p-4 mb-4">
                        <div class="flex items-center gap-3">
                            <span class="icon-[tabler--clock] size-10 text-warning"></span>
                            <div>
                                <h3 class="font-bold text-base-content">Free Trial</h3>
                                <p class="text-sm text-base-content/70">
                                    Your trial ends on {{ $company->trial_ends_at->format('M d, Y') }}
                                    <span class="badge badge-warning badge-sm ml-2">{{ $company->trialDaysRemaining() }} days remaining</span>
                                </p>
                            </div>
                        </div>
                    </div>
                    <p class="text-sm text-base-content/60 mb-4">
                        Upgrade to a paid plan before your trial ends to keep using all features.
                    </p>
                @else
                    <!-- No Plan -->
                    <div class="bg-base-200 rounded-lg p-6 text-center mb-4">
                        <span class="icon-[tabler--credit-card-off] size-12 text-base-content/30 mb-2"></span>
                        <h3 class="font-bold text-base-content">No Active Plan</h3>
                        <p class="text-sm text-base-content/60">Choose a plan to unlock all features</p>
                    </div>
                @endif

                <!-- Actions -->
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('settings.billing.plans') }}" class="btn btn-primary">
                        <span class="icon-[tabler--arrow-up-circle] size-5"></span>
                        @if($company->plan)
                            Change Plan
                        @else
                            Choose a Plan
                        @endif
                    </a>
                    @if($company->plan && !$company->plan->isFree())
                        <button type="button" class="btn btn-outline" disabled>
                            <span class="icon-[tabler--file-invoice] size-5"></span>
                            View Invoices
                        </button>
                    @endif
                </div>
            </div>
        </div>

        <!-- Apply Coupon Card -->
        <div class="card bg-base-100 shadow mb-6">
            <div class="card-body">
                <h2 class="card-title text-lg mb-4">
                    <span class="icon-[tabler--discount] size-5"></span>
                    Apply Coupon
                </h2>
                <form action="{{ route('settings.billing.apply-coupon') }}" method="POST" class="flex gap-3">
                    @csrf
                    <div class="form-control flex-1">
                        <input type="text" name="coupon_code" placeholder="Enter coupon code" class="input input-bordered @error('coupon_code') input-error @enderror" value="{{ old('coupon_code') }}" />
                        @error('coupon_code')
                            <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                        @enderror
                    </div>
                    <button type="submit" class="btn btn-outline">
                        <span class="icon-[tabler--check] size-5"></span>
                        Apply
                    </button>
                </form>
            </div>
        </div>

        <!-- Usage Overview Card -->
        <div class="card bg-base-100 shadow">
            <div class="card-body">
                <h2 class="card-title text-lg mb-4">
                    <span class="icon-[tabler--chart-bar] size-5"></span>
                    Usage Overview
                </h2>
                <div class="space-y-4">
                    <!-- Workspaces Usage -->
                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span>Workspaces</span>
                            <span>
                                {{ $usage['workspaces'] }}
                                @if($company->plan && $company->plan->workspace_limit > 0)
                                    / {{ $company->plan->workspace_limit }}
                                @else
                                    / Unlimited
                                @endif
                            </span>
                        </div>
                        @if($company->plan && $company->plan->workspace_limit > 0)
                            <progress class="progress progress-primary w-full" value="{{ $usage['workspaces'] }}" max="{{ $company->plan->workspace_limit }}"></progress>
                        @else
                            <progress class="progress progress-primary w-full" value="0" max="100"></progress>
                        @endif
                    </div>

                    <!-- Team Members Usage -->
                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span>Team Members</span>
                            <span>
                                {{ $usage['team_members'] }}
                                @if($company->plan && $company->plan->team_member_limit > 0)
                                    / {{ $company->plan->team_member_limit }}
                                @else
                                    / Unlimited
                                @endif
                            </span>
                        </div>
                        @if($company->plan && $company->plan->team_member_limit > 0)
                            <progress class="progress progress-primary w-full" value="{{ $usage['team_members'] }}" max="{{ $company->plan->team_member_limit }}"></progress>
                        @else
                            <progress class="progress progress-primary w-full" value="0" max="100"></progress>
                        @endif
                    </div>

                    <!-- Storage Usage -->
                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span>Storage</span>
                            <span>
                                {{ number_format($usage['storage_gb'], 2) }} GB
                                @if($company->plan && $company->plan->storage_limit_gb > 0)
                                    / {{ $company->plan->storage_limit_gb }} GB
                                @else
                                    / Unlimited
                                @endif
                            </span>
                        </div>
                        @if($company->plan && $company->plan->storage_limit_gb > 0)
                            <progress class="progress progress-primary w-full" value="{{ $usage['storage_gb'] }}" max="{{ $company->plan->storage_limit_gb }}"></progress>
                        @else
                            <progress class="progress progress-primary w-full" value="0" max="100"></progress>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
