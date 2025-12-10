@extends('layouts.app')

@section('content')
<div class="p-4 md:p-6">
    <div class="max-w-3xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex items-center gap-2 text-sm text-base-content/60 mb-2">
                <a href="{{ route('dashboard') }}" class="hover:text-primary">Dashboard</a>
                <span class="icon-[tabler--chevron-right] size-4"></span>
                <a href="{{ route('workspace.show', $workspace->uuid) }}" class="hover:text-primary">{{ $workspace->name }}</a>
                <span class="icon-[tabler--chevron-right] size-4"></span>
                <a href="{{ route('milestones.index', $workspace->uuid) }}" class="hover:text-primary">Milestones</a>
                <span class="icon-[tabler--chevron-right] size-4"></span>
                <span>Edit</span>
            </div>
            <h1 class="text-2xl font-bold text-base-content">Edit Milestone</h1>
            <p class="text-base-content/60">Update milestone details</p>
        </div>

        <!-- Success/Error Messages -->
        <div class="mb-4">
            @include('partials.alerts')
        </div>

        <!-- Form -->
        <div class="card bg-base-100 shadow">
            <div class="card-body">
                <form action="{{ route('milestones.update', [$workspace->uuid, $milestone->uuid]) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <!-- Title -->
                    <div class="form-control mb-4">
                        <label class="label">
                            <span class="label-text font-medium">Milestone Title <span class="text-error">*</span></span>
                        </label>
                        <input type="text" name="title" value="{{ old('title', $milestone->title) }}" class="input input-bordered @error('title') input-error @enderror" placeholder="Enter milestone title" required>
                        @error('title')
                            <label class="label">
                                <span class="label-text-alt text-error">{{ $message }}</span>
                            </label>
                        @enderror
                    </div>

                    <!-- Description -->
                    <div class="form-control mb-4">
                        <label class="label">
                            <span class="label-text font-medium">Description</span>
                        </label>
                        <div id="description-editor" class="min-h-32 border border-base-300 rounded-lg"></div>
                        <input type="hidden" name="description" id="description-input" value="{{ old('description', $milestone->description) }}">
                        @error('description')
                            <label class="label">
                                <span class="label-text-alt text-error">{{ $message }}</span>
                            </label>
                        @enderror
                    </div>

                    <!-- Status & Progress -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium">Status <span class="text-error">*</span></span>
                            </label>
                            <select name="status" class="select select-bordered @error('status') select-error @enderror" required>
                                @foreach($statuses as $value => $label)
                                    <option value="{{ $value }}" {{ old('status', $milestone->status) == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('status')
                                <label class="label">
                                    <span class="label-text-alt text-error">{{ $message }}</span>
                                </label>
                            @enderror
                        </div>

                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium">Progress (%) <span class="text-error">*</span></span>
                            </label>
                            <input type="number" name="progress" value="{{ old('progress', $milestone->progress) }}" min="0" max="100" class="input input-bordered @error('progress') input-error @enderror" required>
                            @error('progress')
                                <label class="label">
                                    <span class="label-text-alt text-error">{{ $message }}</span>
                                </label>
                            @enderror
                        </div>
                    </div>

                    <!-- Dates -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium">Start Date</span>
                            </label>
                            <input type="date" name="start_date" value="{{ old('start_date', $milestone->start_date?->format('Y-m-d')) }}" class="input input-bordered @error('start_date') input-error @enderror">
                            @error('start_date')
                                <label class="label">
                                    <span class="label-text-alt text-error">{{ $message }}</span>
                                </label>
                            @enderror
                        </div>

                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium">Due Date</span>
                            </label>
                            <input type="date" name="due_date" value="{{ old('due_date', $milestone->due_date?->format('Y-m-d')) }}" class="input input-bordered @error('due_date') input-error @enderror">
                            @error('due_date')
                                <label class="label">
                                    <span class="label-text-alt text-error">{{ $message }}</span>
                                </label>
                            @enderror
                        </div>
                    </div>

                    <!-- Owner & Priority -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium">Milestone Owner</span>
                            </label>
                            <select name="owner_id" class="select select-bordered @error('owner_id') select-error @enderror">
                                <option value="">Select Owner</option>
                                @foreach($members as $member)
                                    <option value="{{ $member->id }}" {{ old('owner_id', $milestone->owner_id) == $member->id ? 'selected' : '' }}>
                                        {{ $member->full_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('owner_id')
                                <label class="label">
                                    <span class="label-text-alt text-error">{{ $message }}</span>
                                </label>
                            @enderror
                        </div>

                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium">Priority <span class="text-error">*</span></span>
                            </label>
                            <select name="priority" class="select select-bordered @error('priority') select-error @enderror" required>
                                @foreach($priorities as $value => $label)
                                    <option value="{{ $value }}" {{ old('priority', $milestone->priority) == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('priority')
                                <label class="label">
                                    <span class="label-text-alt text-error">{{ $message }}</span>
                                </label>
                            @enderror
                        </div>
                    </div>

                    <!-- Tags -->
                    @if($tags->isNotEmpty())
                    <div class="form-control mb-4">
                        <label class="label">
                            <span class="label-text font-medium">Tags</span>
                        </label>
                        <div class="flex flex-wrap gap-2 p-3 border border-base-300 rounded-lg bg-base-50">
                            @php
                                $selectedTags = old('tags', $milestone->tags->pluck('id')->toArray());
                            @endphp
                            @foreach($tags as $tag)
                                <label class="cursor-pointer">
                                    <input type="checkbox" name="tags[]" value="{{ $tag->id }}" class="hidden peer" {{ in_array($tag->id, $selectedTags) ? 'checked' : '' }}>
                                    <span class="badge badge-lg peer-checked:ring-2 peer-checked:ring-primary transition-all" style="background-color: {{ $tag->color }}20; color: {{ $tag->color }}; border: 1px solid {{ $tag->color }}40;">
                                        {{ $tag->name }}
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <!-- Color -->
                    <div class="form-control mb-6">
                        <label class="label">
                            <span class="label-text font-medium">Color (optional)</span>
                        </label>
                        <div class="flex items-center gap-3">
                            <input type="color" name="color" value="{{ old('color', $milestone->color ?? '#3b82f6') }}" class="w-12 h-10 rounded cursor-pointer border border-base-300">
                            <span class="text-sm text-base-content/60">Choose a color to identify this milestone</span>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-between pt-4 border-t border-base-200">
                        <button type="button" class="btn btn-error btn-outline"
                            data-delete
                            data-delete-action="{{ route('milestones.destroy', [$workspace->uuid, $milestone->uuid]) }}"
                            data-delete-title="Delete Milestone"
                            data-delete-name="{{ $milestone->title }}"
                            data-delete-warning="This action cannot be undone. All tasks will be unassigned from this milestone.">
                            <span class="icon-[tabler--trash] size-4"></span>
                            Delete
                        </button>

                        <div class="flex items-center gap-3">
                            <a href="{{ route('milestones.show', [$workspace->uuid, $milestone->uuid]) }}" class="btn btn-ghost">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <span class="icon-[tabler--device-floppy] size-5"></span>
                                Save Changes
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const quill = new Quill('#description-editor', {
            theme: 'snow',
            placeholder: 'Describe the milestone...',
            modules: {
                toolbar: [
                    ['bold', 'italic', 'underline'],
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                    ['link'],
                    ['clean']
                ]
            }
        });

        // Set initial content
        const initialContent = document.getElementById('description-input').value;
        if (initialContent) {
            quill.root.innerHTML = initialContent;
        }

        // Update hidden input on form submit
        document.querySelector('form').addEventListener('submit', function() {
            document.getElementById('description-input').value = quill.root.innerHTML;
        });
    });
</script>
@endpush
@endsection
