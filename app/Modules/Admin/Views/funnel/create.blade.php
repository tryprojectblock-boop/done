@extends('admin::layouts.app')

@section('title', 'Create Funnel')
@section('page-title', 'Create Funnel')

@section('content')
<div class="space-y-6">
    <!-- Tabs -->
    @include('admin::funnel.partials.tabs')

    <!-- Header -->
    <div class="flex items-center gap-4">
        <a href="{{ route('backoffice.funnel.index') }}" class="btn btn-ghost btn-sm">
            <span class="icon-[tabler--arrow-left] size-5"></span>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-base-content">Create Funnel</h1>
            <p class="text-base-content/60">Set up a new email automation funnel</p>
        </div>
    </div>

    @include('admin::partials.alerts')

    <form action="{{ route('backoffice.funnel.store') }}" method="POST">
        @csrf

        <div class="card bg-base-100 shadow">
            <div class="card-body space-y-6">
                <!-- Funnel Name -->
                <div class="form-control">
                    <label class="label">
                        <span class="label-text font-medium">Funnel Name <span class="text-error">*</span></span>
                    </label>
                    <input type="text" name="name" value="{{ old('name') }}"
                           class="input input-bordered @error('name') input-error @enderror"
                           placeholder="e.g., Onboarding Sequence" required />
                    @error('name')
                        <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                    @enderror
                </div>

                <!-- Description -->
                <div class="form-control">
                    <label class="label">
                        <span class="label-text font-medium">Description</span>
                    </label>
                    <textarea name="description" rows="3"
                              class="textarea textarea-bordered @error('description') textarea-error @enderror"
                              placeholder="Brief description of this funnel...">{{ old('description') }}</textarea>
                    @error('description')
                        <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                    @enderror
                </div>

                <!-- Trigger Tag -->
                <div class="form-control">
                    <label class="label">
                        <span class="label-text font-medium">Trigger Tag <span class="text-error">*</span></span>
                    </label>
                    <select name="trigger_tag_id" class="select select-bordered @error('trigger_tag_id') select-error @enderror" required>
                        <option value="">Select a trigger tag...</option>
                        @foreach($tags as $tag)
                            <option value="{{ $tag->id }}" {{ old('trigger_tag_id') == $tag->id ? 'selected' : '' }}>
                                {{ $tag->display_name }} ({{ $tag->name }})
                            </option>
                        @endforeach
                    </select>
                    <label class="label">
                        <span class="label-text-alt text-base-content/60">Users will enter this funnel when they receive this tag.</span>
                    </label>
                    @error('trigger_tag_id')
                        <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                    @enderror
                </div>

                <!-- Active Status -->
                <div class="form-control">
                    <label class="label cursor-pointer justify-start gap-4">
                        <input type="checkbox" name="is_active" value="1" class="toggle toggle-primary" {{ old('is_active') ? 'checked' : '' }} />
                        <div>
                            <span class="label-text font-medium">Activate Funnel</span>
                            <p class="text-sm text-base-content/60">Enable this to start processing subscribers immediately.</p>
                        </div>
                    </label>
                </div>

                <!-- Actions -->
                <div class="flex justify-end gap-4 pt-4 border-t border-base-200">
                    <a href="{{ route('backoffice.funnel.index') }}" class="btn btn-ghost">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <span class="icon-[tabler--check] size-5"></span>
                        Create Funnel
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
