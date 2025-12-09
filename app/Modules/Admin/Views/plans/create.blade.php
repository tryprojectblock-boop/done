@extends('admin::layouts.app')

@section('title', 'Add Plan')
@section('page-title', 'Add Plan')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Breadcrumb -->
    <div class="text-sm breadcrumbs">
        <ul>
            <li><a href="{{ route('backoffice.plans.index') }}">Plans & Coupons</a></li>
            <li>Add Plan</li>
        </ul>
    </div>

    <!-- Header -->
    <div>
        <h1 class="text-2xl font-bold text-base-content">Add Plan</h1>
        <p class="text-base-content/60">Create a new subscription plan</p>
    </div>

    <form action="{{ route('backoffice.plans.store') }}" method="POST">
        @csrf

        <!-- Basic Info -->
        <div class="card bg-base-100 shadow mb-6">
            <div class="card-body">
                <h2 class="card-title text-lg mb-4">Plan Information</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-control">
                        <label class="label" for="plan-name">
                            <span class="label-text font-medium">Plan Name</span>
                        </label>
                        <input type="text" name="name" id="plan-name" value="{{ old('name') }}" class="input input-bordered @error('name') input-error @enderror" placeholder="e.g., Pro, Business, Enterprise" required />
                        @error('name')
                            <div class="label"><span class="label-text-alt text-error">{{ $message }}</span></div>
                        @enderror
                    </div>

                    <div class="form-control">
                        <label class="label" for="plan-type">
                            <span class="label-text font-medium">Plan Type</span>
                        </label>
                        <select name="type" id="plan-type" class="select select-bordered @error('type') select-error @enderror" required>
                            @foreach($planTypes as $type)
                                <option value="{{ $type->value }}" {{ old('type') === $type->value ? 'selected' : '' }}>
                                    {{ $type->label() }}
                                </option>
                            @endforeach
                        </select>
                        @error('type')
                            <div class="label"><span class="label-text-alt text-error">{{ $message }}</span></div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Limits -->
        <div class="card bg-base-100 shadow mb-6">
            <div class="card-body">
                <h2 class="card-title text-lg mb-4">Plan Limits</h2>
                <p class="text-sm text-base-content/60 mb-4">
                    <span class="icon-[tabler--info-circle] size-4 inline-block align-middle mr-1"></span>
                    Enter <strong>0</strong> for unlimited
                </p>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="form-control">
                        <label class="label" for="workspace-limit">
                            <span class="label-text font-medium">Workspace Number</span>
                        </label>
                        <input type="number" name="workspace_limit" id="workspace-limit" value="{{ old('workspace_limit', 1) }}" class="input input-bordered @error('workspace_limit') input-error @enderror" min="0" required />
                        @error('workspace_limit')
                            <div class="label"><span class="label-text-alt text-error">{{ $message }}</span></div>
                        @enderror
                    </div>

                    <div class="form-control">
                        <label class="label" for="team-member-limit">
                            <span class="label-text font-medium">Team Members</span>
                        </label>
                        <input type="number" name="team_member_limit" id="team-member-limit" value="{{ old('team_member_limit', 5) }}" class="input input-bordered @error('team_member_limit') input-error @enderror" min="0" required />
                        @error('team_member_limit')
                            <div class="label"><span class="label-text-alt text-error">{{ $message }}</span></div>
                        @enderror
                    </div>

                    <div class="form-control">
                        <label class="label" for="storage-limit-gb">
                            <span class="label-text font-medium">Storage Limit (GB)</span>
                        </label>
                        <input type="number" name="storage_limit_gb" id="storage-limit-gb" value="{{ old('storage_limit_gb', 5) }}" class="input input-bordered @error('storage_limit_gb') input-error @enderror" min="0" required />
                        @error('storage_limit_gb')
                            <div class="label"><span class="label-text-alt text-error">{{ $message }}</span></div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Pricing -->
        <div class="card bg-base-100 shadow mb-6" id="pricing-card">
            <div class="card-body">
                <h2 class="card-title text-lg mb-4">Plan Cost</h2>
                <div id="free-plan-notice" class="alert alert-info mb-4 hidden">
                    <span class="icon-[tabler--info-circle] size-5"></span>
                    <span>Free plans don't require pricing. All prices will be set to $0.</span>
                </div>

                <div id="pricing-inputs" class="grid grid-cols-2 md:grid-cols-3 gap-4">
                    <div class="form-control">
                        <label class="label" for="price-1-month">
                            <span class="label-text font-medium">1 Month ($)</span>
                        </label>
                        <input type="number" name="price_1_month" id="price-1-month" value="{{ old('price_1_month', 0) }}" class="input input-bordered price-input @error('price_1_month') input-error @enderror" min="0" step="0.01" />
                        @error('price_1_month')
                            <div class="label"><span class="label-text-alt text-error">{{ $message }}</span></div>
                        @enderror
                    </div>

                    <div class="form-control">
                        <label class="label" for="price-3-month">
                            <span class="label-text font-medium">3 Month ($)</span>
                        </label>
                        <input type="number" name="price_3_month" id="price-3-month" value="{{ old('price_3_month', 0) }}" class="input input-bordered price-input @error('price_3_month') input-error @enderror" min="0" step="0.01" />
                        @error('price_3_month')
                            <div class="label"><span class="label-text-alt text-error">{{ $message }}</span></div>
                        @enderror
                    </div>

                    <div class="form-control">
                        <label class="label" for="price-6-month">
                            <span class="label-text font-medium">6 Month ($)</span>
                        </label>
                        <input type="number" name="price_6_month" id="price-6-month" value="{{ old('price_6_month', 0) }}" class="input input-bordered price-input @error('price_6_month') input-error @enderror" min="0" step="0.01" />
                        @error('price_6_month')
                            <div class="label"><span class="label-text-alt text-error">{{ $message }}</span></div>
                        @enderror
                    </div>

                    <div class="form-control">
                        <label class="label" for="price-12-month">
                            <span class="label-text font-medium">12 Month ($)</span>
                        </label>
                        <input type="number" name="price_12_month" id="price-12-month" value="{{ old('price_12_month', 0) }}" class="input input-bordered price-input @error('price_12_month') input-error @enderror" min="0" step="0.01" />
                        @error('price_12_month')
                            <div class="label"><span class="label-text-alt text-error">{{ $message }}</span></div>
                        @enderror
                    </div>

                    <div class="form-control">
                        <label class="label" for="price-3-year">
                            <span class="label-text font-medium">3 Year ($)</span>
                        </label>
                        <input type="number" name="price_3_year" id="price-3-year" value="{{ old('price_3_year', 0) }}" class="input input-bordered price-input @error('price_3_year') input-error @enderror" min="0" step="0.01" />
                        @error('price_3_year')
                            <div class="label"><span class="label-text-alt text-error">{{ $message }}</span></div>
                        @enderror
                    </div>

                    <div class="form-control">
                        <label class="label" for="price-5-year">
                            <span class="label-text font-medium">5 Years ($)</span>
                        </label>
                        <input type="number" name="price_5_year" id="price-5-year" value="{{ old('price_5_year', 0) }}" class="input input-bordered price-input @error('price_5_year') input-error @enderror" min="0" step="0.01" />
                        @error('price_5_year')
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

                <div class="flex flex-wrap gap-6 mb-4">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" class="checkbox checkbox-primary" {{ old('is_active', true) ? 'checked' : '' }} />
                        <span class="label-text font-medium">Active</span>
                    </label>

                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="is_popular" value="1" class="checkbox checkbox-primary" {{ old('is_popular') ? 'checked' : '' }} />
                        <span class="label-text font-medium">Popular</span>
                    </label>
                </div>

                <div class="form-control">
                    <label class="label" for="sort-order">
                        <span class="label-text font-medium">Sort Order</span>
                    </label>
                    <input type="number" name="sort_order" id="sort-order" value="{{ old('sort_order', 0) }}" class="input input-bordered w-32" min="0" aria-describedby="sort-order-hint" />
                    <div class="label" id="sort-order-hint">
                        <span class="label-text-alt text-base-content/60">Lower number = appears first</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Submit -->
        <div class="flex justify-end gap-2">
            <a href="{{ route('backoffice.plans.index') }}" class="btn btn-ghost">Cancel</a>
            <button type="submit" class="btn btn-primary">
                <span class="icon-[tabler--plus] size-5"></span>
                Create Plan
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const planTypeSelect = document.getElementById('plan-type');
    const pricingInputs = document.getElementById('pricing-inputs');
    const freePlanNotice = document.getElementById('free-plan-notice');
    const priceFields = document.querySelectorAll('.price-input');

    function handlePlanTypeChange() {
        const isFree = planTypeSelect.value === 'free';

        if (isFree) {
            // Show notice and make inputs readonly
            freePlanNotice.classList.remove('hidden');
            pricingInputs.classList.add('opacity-50');

            // Set all prices to 0 and make readonly
            priceFields.forEach(input => {
                input.value = 0;
                input.readOnly = true;
                input.classList.add('bg-base-200');
            });
        } else {
            // Hide notice and enable inputs
            freePlanNotice.classList.add('hidden');
            pricingInputs.classList.remove('opacity-50');

            // Enable all price fields
            priceFields.forEach(input => {
                input.readOnly = false;
                input.classList.remove('bg-base-200');
            });
        }
    }

    // Initial check
    handlePlanTypeChange();

    // Listen for changes
    planTypeSelect.addEventListener('change', handlePlanTypeChange);
});
</script>
@endpush
@endsection
