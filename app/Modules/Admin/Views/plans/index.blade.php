@extends('admin::layouts.app')

@section('title', 'Plans & Coupons')
@section('page-title', 'Plans & Coupons')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-base-content">Plans & Coupons</h1>
            <p class="text-base-content/60">Manage subscription plans and discount coupons</p>
        </div>
    </div>

    @include('admin::partials.alerts')

    <!-- Tabs -->
    <div class="card bg-base-100 shadow">
        <div class="card-body">
            <!-- Tab Navigation -->
            <div class="flex items-center justify-between border-b border-base-200 mb-6">
                <div class="flex gap-0">
                    <a href="{{ route('backoffice.plans.index', ['tab' => 'plans']) }}"
                       class="px-6 py-3 text-sm font-medium border-b-2 transition-colors {{ $tab === 'plans' ? 'border-primary text-primary' : 'border-transparent text-base-content/70 hover:text-base-content' }}">
                        <span class="icon-[tabler--credit-card] size-4 inline-block mr-1.5 align-middle"></span>
                        Plans
                    </a>
                    <a href="{{ route('backoffice.plans.index', ['tab' => 'coupons']) }}"
                       class="px-6 py-3 text-sm font-medium border-b-2 transition-colors {{ $tab === 'coupons' ? 'border-primary text-primary' : 'border-transparent text-base-content/70 hover:text-base-content' }}">
                        <span class="icon-[tabler--discount] size-4 inline-block mr-1.5 align-middle"></span>
                        Coupons
                    </a>
                </div>
                <div>
                    @if($tab === 'plans')
                        <a href="{{ route('backoffice.plans.create') }}" class="btn btn-primary btn-sm">
                            <span class="icon-[tabler--plus] size-4"></span>
                            Add Plan
                        </a>
                    @else
                        <a href="{{ route('backoffice.coupons.create') }}" class="btn btn-primary btn-sm">
                            <span class="icon-[tabler--plus] size-4"></span>
                            Add Coupon
                        </a>
                    @endif
                </div>
            </div>

            <!-- Plans Tab Content -->
            @if($tab === 'plans')
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Plan Name</th>
                                <th>Type</th>
                                <th>Workspaces</th>
                                <th>Team Members</th>
                                <th>Storage</th>
                                <th>1 Month</th>
                                <th>12 Month</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($plans as $plan)
                                <tr>
                                    <td>
                                        <div class="flex items-center gap-2">
                                            <span class="font-medium">{{ $plan->name }}</span>
                                            @if($plan->is_popular)
                                                <span class="badge badge-primary badge-xs">Popular</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $plan->type->color() }} badge-sm">
                                            {{ $plan->type->label() }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($plan->workspace_limit == 0)
                                            <span class="badge badge-info badge-sm">Unlimited</span>
                                        @else
                                            {{ $plan->workspace_limit }}
                                        @endif
                                    </td>
                                    <td>
                                        @if($plan->team_member_limit == 0)
                                            <span class="badge badge-info badge-sm">Unlimited</span>
                                        @else
                                            {{ $plan->team_member_limit }}
                                        @endif
                                    </td>
                                    <td>
                                        @if($plan->storage_limit_gb == 0)
                                            <span class="badge badge-info badge-sm">Unlimited</span>
                                        @else
                                            {{ $plan->storage_limit_gb }} GB
                                        @endif
                                    </td>
                                    <td>${{ number_format($plan->price_1_month, 2) }}</td>
                                    <td>${{ number_format($plan->price_12_month, 2) }}</td>
                                    <td>
                                        <span class="badge {{ $plan->is_active ? 'badge-success' : 'badge-ghost' }} badge-sm">
                                            {{ $plan->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="flex items-center gap-1">
                                            <a href="{{ route('backoffice.plans.edit', $plan) }}" class="btn btn-ghost btn-xs">
                                                <span class="icon-[tabler--edit] size-4"></span>
                                            </a>
                                            <form action="{{ route('backoffice.plans.toggle-status', $plan) }}" method="POST" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-ghost btn-xs" title="{{ $plan->is_active ? 'Deactivate' : 'Activate' }}">
                                                    <span class="icon-[tabler--{{ $plan->is_active ? 'eye-off' : 'eye' }}] size-4"></span>
                                                </button>
                                            </form>
                                            <form action="{{ route('backoffice.plans.destroy', $plan) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this plan?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-ghost btn-xs text-error">
                                                    <span class="icon-[tabler--trash] size-4"></span>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-8">
                                        <span class="icon-[tabler--credit-card-off] size-12 text-base-content/20 mb-2"></span>
                                        <p class="text-base-content/60">No plans found</p>
                                        <a href="{{ route('backoffice.plans.create') }}" class="btn btn-primary btn-sm mt-4">
                                            <span class="icon-[tabler--plus] size-4"></span>
                                            Create First Plan
                                        </a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endif

            <!-- Coupons Tab Content -->
            @if($tab === 'coupons')
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Coupon Name</th>
                                <th>Code</th>
                                <th>Discount</th>
                                <th>Status</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Usage</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($coupons as $coupon)
                                <tr>
                                    <td class="font-medium">{{ $coupon->name }}</td>
                                    <td>
                                        <code class="bg-base-200 px-2 py-1 rounded text-sm font-mono">{{ $coupon->code }}</code>
                                    </td>
                                    <td class="font-medium text-success">{{ number_format($coupon->discount_percent, 0) }}%</td>
                                    <td>
                                        <span class="badge {{ $coupon->is_active ? 'badge-success' : 'badge-ghost' }} badge-sm">
                                            {{ $coupon->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="text-sm">{{ $coupon->start_date?->format('M d, Y') ?? '-' }}</td>
                                    <td class="text-sm">{{ $coupon->end_date?->format('M d, Y') ?? 'No End' }}</td>
                                    <td>
                                        <span class="badge badge-ghost badge-sm">
                                            {{ $coupon->usage_count }} / {{ $coupon->usage_limit ?? '∞' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="flex items-center gap-1">
                                            <a href="{{ route('backoffice.coupons.edit', $coupon) }}" class="btn btn-ghost btn-xs">
                                                <span class="icon-[tabler--edit] size-4"></span>
                                            </a>
                                            <form action="{{ route('backoffice.coupons.toggle-status', $coupon) }}" method="POST" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-ghost btn-xs" title="{{ $coupon->is_active ? 'Deactivate' : 'Activate' }}">
                                                    <span class="icon-[tabler--{{ $coupon->is_active ? 'eye-off' : 'eye' }}] size-4"></span>
                                                </button>
                                            </form>
                                            <form action="{{ route('backoffice.coupons.destroy', $coupon) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this coupon?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-ghost btn-xs text-error">
                                                    <span class="icon-[tabler--trash] size-4"></span>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-8">
                                        <span class="icon-[tabler--discount-off] size-12 text-base-content/20 mb-2"></span>
                                        <p class="text-base-content/60">No coupons found</p>
                                        <a href="{{ route('backoffice.coupons.create') }}" class="btn btn-primary btn-sm mt-4">
                                            <span class="icon-[tabler--plus] size-4"></span>
                                            Create First Coupon
                                        </a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
