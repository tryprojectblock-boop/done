@extends('layouts.app')

@section('content')
<div class="min-h-screen p-4 md:p-6">
    <div class="max-w-3xl mx-auto">
        <!-- Page Header -->
        <div class="mb-6">
            <div class="flex items-center gap-2 text-sm text-base-content/60 mb-2">
                <a href="{{ route('settings.index') }}" class="hover:text-primary">Settings</a>
                <span class="icon-[tabler--chevron-right] size-4"></span>
                <span>Company</span>
            </div>
            <h1 class="text-2xl font-bold text-base-content">Company Settings</h1>
            <p class="text-base-content/60">Manage your company profile and branding</p>
        </div>

        <!-- Success Message -->
        @if(session('success'))
            <div class="alert alert-success mb-6">
                <span class="icon-[tabler--circle-check] size-5"></span>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <!-- Company Form Card -->
        <div class="card bg-base-100 shadow">
            <div class="card-body">
                <form action="{{ route('settings.company.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <!-- Company Logo Section -->
                    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-6 mb-8 pb-8 border-b border-base-200">
                        <div class="avatar placeholder">
                            @if($company->logo_path)
                                <div class="w-24 h-24 rounded-lg ring ring-primary ring-offset-base-100 ring-offset-2 overflow-hidden bg-base-200">
                                    <img src="{{ Storage::url($company->logo_path) }}" alt="{{ $company->name }}" class="w-full h-full object-contain" />
                                </div>
                            @else
                                <div class="bg-primary text-primary-content rounded-lg w-24 h-24 flex items-center justify-center ring ring-primary ring-offset-base-100 ring-offset-2">
                                    <span class="text-3xl font-semibold">{{ substr($company->name ?? 'C', 0, 2) }}</span>
                                </div>
                            @endif
                        </div>
                        <div class="flex-1">
                            <h3 class="font-semibold text-lg mb-2">Company Logo</h3>
                            <p class="text-sm text-base-content/60 mb-3">Upload a logo for your company. Recommended size: 200x200px. Max size: 2MB</p>
                            <div class="flex flex-wrap gap-2">
                                <label class="btn btn-primary btn-sm">
                                    <span class="icon-[tabler--upload] size-4"></span>
                                    Upload Logo
                                    <input type="file" name="logo" accept="image/*" class="hidden" onchange="previewLogo(this)" />
                                </label>
                                @if($company->logo_path)
                                    <button type="button" class="btn btn-ghost btn-sm text-error" onclick="document.getElementById('delete-logo-form').submit()">
                                        <span class="icon-[tabler--trash] size-4"></span>
                                        Remove
                                    </button>
                                @endif
                            </div>
                            @error('logo')
                                <p class="text-error text-sm mt-2">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Company Name -->
                    <div class="form-control mb-6">
                        <label class="label" for="name">
                            <span class="label-text font-medium">Company Name <span class="text-error">*</span></span>
                        </label>
                        <input
                            type="text"
                            id="name"
                            name="name"
                            value="{{ old('name', $company->name) }}"
                            class="input input-bordered w-full @error('name') input-error @enderror"
                            placeholder="Enter company name"
                            required
                        />
                        @error('name')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Company Size -->
                    <div class="form-control mb-6">
                        <label class="label" for="size">
                            <span class="label-text font-medium">Company Size</span>
                        </label>
                        <select
                            id="size"
                            name="size"
                            class="select select-bordered w-full"
                        >
                            <option value="">Select company size</option>
                            @foreach($companySizes as $size)
                                <option value="{{ $size->value }}" {{ old('size', $company->size?->value) === $size->value ? 'selected' : '' }}>
                                    {{ $size->label() }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Industry Type -->
                    <div class="form-control mb-6">
                        <label class="label" for="industry_type">
                            <span class="label-text font-medium">Industry</span>
                        </label>
                        <select
                            id="industry_type"
                            name="industry_type"
                            class="select select-bordered w-full"
                        >
                            <option value="">Select industry</option>
                            @foreach($industryTypes as $industry)
                                <option value="{{ $industry->value }}" {{ old('industry_type', $company->industry_type?->value) === $industry->value ? 'selected' : '' }}>
                                    {{ $industry->label() }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Website URL -->
                    <div class="form-control mb-8">
                        <label class="label" for="website_url">
                            <span class="label-text font-medium">Website URL</span>
                        </label>
                        <input
                            type="url"
                            id="website_url"
                            name="website_url"
                            value="{{ old('website_url', $company->website_url) }}"
                            class="input input-bordered w-full @error('website_url') input-error @enderror"
                            placeholder="https://example.com"
                        />
                        @error('website_url')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-between items-center">
                        <a href="{{ route('settings.index') }}" class="btn btn-ghost">
                            <span class="icon-[tabler--arrow-left] size-4"></span>
                            Back to Settings
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <span class="icon-[tabler--device-floppy] size-5"></span>
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Hidden form for deleting logo -->
<form id="delete-logo-form" action="{{ route('settings.company.logo.delete') }}" method="POST" class="hidden">
    @csrf
    @method('DELETE')
</form>

@push('scripts')
<script>
function previewLogo(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const avatarContainer = document.querySelector('.avatar');
            const existingImg = avatarContainer.querySelector('img');
            const placeholder = avatarContainer.querySelector('.bg-primary');

            if (existingImg) {
                existingImg.src = e.target.result;
            } else if (placeholder) {
                placeholder.innerHTML = `<img src="${e.target.result}" alt="Preview" class="w-full h-full object-contain" />`;
                placeholder.classList.remove('bg-primary', 'text-primary-content');
                placeholder.classList.add('bg-base-200');
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endpush
@endsection
