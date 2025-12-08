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
                        <label class="label">
                            <span class="label-text font-medium">Plan Name</span>
                        </label>
                        <input type="text" name="name" value="{{ old('name') }}" class="input input-bordered @error('name') input-error @enderror" placeholder="e.g., Pro, Business, Enterprise" required />
                        @error('name')
                            <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                        @enderror
                    </div>

                    <div class="form-control">
                        <label class="label">
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
                            <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
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
                        <label class="label">
                            <span class="label-text font-medium">Workspace Number</span>
                        </label>
                        <input type="number" name="workspace_limit" value="{{ old('workspace_limit', 1) }}" class="input input-bordered @error('workspace_limit') input-error @enderror" min="0" required />
                        @error('workspace_limit')
                            <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                        @enderror
                    </div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Team Members</span>
                        </label>
                        <input type="number" name="team_member_limit" value="{{ old('team_member_limit', 5) }}" class="input input-bordered @error('team_member_limit') input-error @enderror" min="0" required />
                        @error('team_member_limit')
                            <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                        @enderror
                    </div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Storage Limit (GB)</span>
                        </label>
                        <input type="number" name="storage_limit_gb" value="{{ old('storage_limit_gb', 5) }}" class="input input-bordered @error('storage_limit_gb') input-error @enderror" min="0" required />
                        @error('storage_limit_gb')
                            <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
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
                        <label class="label">
                            <span class="label-text font-medium">1 Month ($)</span>
                        </label>
                        <input type="number" name="price_1_month" value="{{ old('price_1_month', 0) }}" class="input input-bordered price-input @error('price_1_month') input-error @enderror" min="0" step="0.01" required />
                        @error('price_1_month')
                            <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                        @enderror
                    </div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">3 Month ($)</span>
                        </label>
                        <input type="number" name="price_3_month" value="{{ old('price_3_month', 0) }}" class="input input-bordered price-input @error('price_3_month') input-error @enderror" min="0" step="0.01" required />
                        @error('price_3_month')
                            <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                        @enderror
                    </div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">6 Month ($)</span>
                        </label>
                        <input type="number" name="price_6_month" value="{{ old('price_6_month', 0) }}" class="input input-bordered price-input @error('price_6_month') input-error @enderror" min="0" step="0.01" required />
                        @error('price_6_month')
                            <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                        @enderror
                    </div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">12 Month ($)</span>
                        </label>
                        <input type="number" name="price_12_month" value="{{ old('price_12_month', 0) }}" class="input input-bordered price-input @error('price_12_month') input-error @enderror" min="0" step="0.01" required />
                        @error('price_12_month')
                            <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                        @enderror
                    </div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">3 Year ($)</span>
                        </label>
                        <input type="number" name="price_3_year" value="{{ old('price_3_year', 0) }}" class="input input-bordered price-input @error('price_3_year') input-error @enderror" min="0" step="0.01" required />
                        @error('price_3_year')
                            <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                        @enderror
                    </div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">5 Years ($)</span>
                        </label>
                        <input type="number" name="price_5_year" value="{{ old('price_5_year', 0) }}" class="input input-bordered price-input @error('price_5_year') input-error @enderror" min="0" step="0.01" required />
                        @error('price_5_year')
                            <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Options -->
        <div class="card bg-base-100 shadow mb-6">
            <div class="card-body">
                <h2 class="card-title text-lg mb-4">Options</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-control">
                        <label class="label cursor-pointer justify-start gap-4">
                            <input type="checkbox" name="is_active" value="1" class="toggle toggle-primary" {{ old('is_active', true) ? 'checked' : '' }} />
                            <div>
                                <span class="label-text font-medium">Active</span>
                                <p class="text-xs text-base-content/60">Plan is available for selection</p>
                            </div>
                        </label>
                    </div>

                    <div class="form-control">
                        <label class="label cursor-pointer justify-start gap-4">
                            <input type="checkbox" name="is_popular" value="1" class="toggle toggle-primary" {{ old('is_popular') ? 'checked' : '' }} />
                            <div>
                                <span class="label-text font-medium">Popular</span>
                                <p class="text-xs text-base-content/60">Mark as popular/recommended plan</p>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="form-control mt-4">
                    <label class="label">
                        <span class="label-text font-medium">Sort Order</span>
                    </label>
                    <input type="number" name="sort_order" value="{{ old('sort_order', 0) }}" class="input input-bordered w-32" min="0" />
                    <label class="label">
                        <span class="label-text-alt text-base-content/60">Lower number = appears first</span>
                    </label>
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
            // Show notice and disable inputs
            freePlanNotice.classList.remove('hidden');
            pricingInputs.classList.add('opacity-50', 'pointer-events-none');

            // Set all prices to 0 and disable
            priceFields.forEach(input => {
                input.value = 0;
                input.disabled = true;
            });
        } else {
            // Hide notice and enable inputs
            freePlanNotice.classList.add('hidden');
            pricingInputs.classList.remove('opacity-50', 'pointer-events-none');

            // Enable all price fields
            priceFields.forEach(input => {
                input.disabled = false;
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
