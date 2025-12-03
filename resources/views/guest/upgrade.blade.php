@extends('layouts.app')

@section('content')
<div class="min-h-screen p-4 md:p-6">
    <div class="max-w-2xl mx-auto">
        <!-- Page Header -->
        <div class="mb-6 text-center">
            <div class="flex justify-center mb-4">
                <div class="w-20 h-20 rounded-full bg-success/20 flex items-center justify-center">
                    <span class="icon-[tabler--rocket] size-10 text-success"></span>
                </div>
            </div>
            <h1 class="text-2xl font-bold text-base-content">Upgrade Your Account</h1>
            <p class="text-base-content/60 mt-2">Set up your company to unlock all features</p>
        </div>

        <!-- Progress Steps -->
        <div class="flex justify-center mb-8">
            <ul class="steps steps-horizontal">
                <li class="step step-success">Confirm</li>
                <li class="step step-success">Company Info</li>
                <li class="step">Complete</li>
            </ul>
        </div>

        <!-- Error Message -->
        @if(session('error'))
            <div class="alert alert-error mb-6">
                <span class="icon-[tabler--alert-circle] size-5"></span>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <!-- Upgrade Form Card -->
        <div class="card bg-base-100 shadow-xl">
            <div class="card-body">
                <h2 class="card-title text-lg mb-4">
                    <span class="icon-[tabler--building] size-6 text-primary"></span>
                    Company Information
                </h2>

                <form action="{{ route('guest.upgrade.store') }}" method="POST">
                    @csrf

                    <!-- Company Name -->
                    <div class="mb-6">
                        <label class="label" for="company_name">
                            <span class="label-text font-medium">Company Name <span class="text-error">*</span></span>
                        </label>
                        <input
                            type="text"
                            id="company_name"
                            name="company_name"
                            value="{{ old('company_name') }}"
                            class="input input-bordered w-full @error('company_name') input-error @enderror"
                            placeholder="Enter your company name"
                            required
                            autofocus
                        />
                        @error('company_name')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Company Size -->
                    <div class="mb-6">
                        <label class="label" for="company_size">
                            <span class="label-text font-medium">Company Size <span class="text-error">*</span></span>
                        </label>
                        <select
                            id="company_size"
                            name="company_size"
                            class="select select-bordered w-full @error('company_size') select-error @enderror"
                            required
                        >
                            <option value="" disabled {{ old('company_size') ? '' : 'selected' }}>Select company size</option>
                            @foreach($companySizes as $size)
                                <option value="{{ $size['value'] }}" {{ old('company_size') === $size['value'] ? 'selected' : '' }}>
                                    {{ $size['label'] }}
                                </option>
                            @endforeach
                        </select>
                        @error('company_size')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Industry Type -->
                    <div class="mb-6">
                        <label class="label" for="industry_type">
                            <span class="label-text font-medium">Industry <span class="text-error">*</span></span>
                        </label>
                        <select
                            id="industry_type"
                            name="industry_type"
                            class="select select-bordered w-full @error('industry_type') select-error @enderror"
                            required
                        >
                            <option value="" disabled {{ old('industry_type') ? '' : 'selected' }}>Select your industry</option>
                            @foreach($industryTypes as $type)
                                <option value="{{ $type['value'] }}" {{ old('industry_type') === $type['value'] ? 'selected' : '' }}>
                                    {{ $type['label'] }}
                                </option>
                            @endforeach
                        </select>
                        @error('industry_type')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Website (Optional) -->
                    <div class="mb-8">
                        <label class="label" for="website_url">
                            <span class="label-text font-medium">Website</span>
                            <span class="label-text-alt text-base-content/50">Optional</span>
                        </label>
                        <div class="join w-full">
                            <select
                                name="website_protocol"
                                class="select select-bordered join-item w-32"
                            >
                                <option value="https://" {{ old('website_protocol', 'https://') === 'https://' ? 'selected' : '' }}>https://</option>
                                <option value="http://" {{ old('website_protocol') === 'http://' ? 'selected' : '' }}>http://</option>
                            </select>
                            <input
                                type="text"
                                id="website_url"
                                name="website_url"
                                value="{{ old('website_url') }}"
                                class="input input-bordered join-item flex-1 @error('website_url') input-error @enderror"
                                placeholder="www.example.com"
                            />
                        </div>
                        @error('website_url')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- What You'll Get -->
                    <div class="bg-base-200 rounded-lg p-4 mb-8">
                        <h3 class="font-semibold text-sm mb-3 flex items-center gap-2">
                            <span class="icon-[tabler--gift] size-5 text-success"></span>
                            What you'll get with upgrade
                        </h3>
                        <ul class="space-y-2 text-sm">
                            <li class="flex items-center gap-2">
                                <span class="icon-[tabler--check] size-4 text-success"></span>
                                Create unlimited workspaces
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="icon-[tabler--check] size-4 text-success"></span>
                                Invite team members
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="icon-[tabler--check] size-4 text-success"></span>
                                Full task and project management
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="icon-[tabler--check] size-4 text-success"></span>
                                Time tracking and reports
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="icon-[tabler--check] size-4 text-success"></span>
                                Custom workflows
                            </li>
                        </ul>
                    </div>

                    <!-- Info about keeping guest access -->
                    <div class="alert alert-info mb-6">
                        <span class="icon-[tabler--info-circle] size-5"></span>
                        <span class="text-sm">You'll keep access to all workspaces you've been invited to as a guest.</span>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="flex flex-col sm:flex-row gap-3 justify-end">
                        <a href="{{ route('dashboard') }}" class="btn btn-ghost">
                            <span class="icon-[tabler--arrow-left] size-4"></span>
                            Cancel
                        </a>
                        <button type="submit" class="btn btn-success">
                            <span class="icon-[tabler--rocket] size-5"></span>
                            Complete Upgrade
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
