@extends('admin::layouts.app')

@section('title', 'Edit ' . $company->name)
@section('page-title', 'Edit Client')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <!-- Breadcrumb -->
    <div class="text-sm breadcrumbs">
        <ul>
            <li><a href="{{ route('backoffice.clients.index') }}">Clients</a></li>
            <li><a href="{{ route('backoffice.clients.show', $company) }}">{{ $company->name }}</a></li>
            <li>Edit</li>
        </ul>
    </div>

    @include('admin::partials.alerts')

    <div class="card bg-base-100 shadow">
        <div class="card-body">
            <h2 class="card-title mb-4">Edit Company</h2>

            <form action="{{ route('backoffice.clients.update', $company) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-control mb-4">
                    <label class="label" for="company-name">
                        <span class="label-text font-medium">Company Name</span>
                    </label>
                    <input type="text" name="name" id="company-name" value="{{ old('name', $company->name) }}" class="input input-bordered @error('name') input-error @enderror" required />
                    @error('name')
                        <div class="label"><span class="label-text-alt text-error">{{ $message }}</span></div>
                    @enderror
                </div>

                <div class="form-control mb-4">
                    <label class="label" for="website-url">
                        <span class="label-text font-medium">Website URL</span>
                    </label>
                    <input type="url" name="website_url" id="website-url" value="{{ old('website_url', $company->website_url) }}" class="input input-bordered @error('website_url') input-error @enderror" placeholder="https://example.com" />
                    @error('website_url')
                        <div class="label"><span class="label-text-alt text-error">{{ $message }}</span></div>
                    @enderror
                </div>

                <div class="flex justify-end gap-2 mt-6">
                    <a href="{{ route('backoffice.clients.show', $company) }}" class="btn btn-ghost">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <span class="icon-[tabler--device-floppy] size-4"></span>
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
