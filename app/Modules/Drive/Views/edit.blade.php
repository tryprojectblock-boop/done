@extends('layouts.app')

@section('content')
<div class="p-4 md:p-6">
    <div class="max-w-3xl mx-auto">
        <!-- Breadcrumb -->
        <div class="text-sm breadcrumbs mb-6">
            <ul>
                <li><a href="{{ route('drive.index') }}">Drive</a></li>
                <li>Edit File</li>
            </ul>
        </div>

        <div class="card bg-base-100 shadow">
            <div class="card-body">
                <h2 class="card-title mb-6">
                    <span class="icon-[tabler--edit] size-6 text-primary"></span>
                    Edit File Details
                </h2>

                <!-- File Preview -->
                <div class="flex items-center gap-4 p-4 bg-base-200 rounded-lg mb-6">
                    <div class="w-16 h-16 rounded-lg overflow-hidden bg-base-300 flex items-center justify-center">
                        @if($attachment->is_image)
                            <img src="{{ $attachment->url }}" alt="{{ $attachment->name }}" class="w-full h-full object-cover" />
                        @else
                            <span class="icon-[{{ $attachment->icon }}] size-8 text-base-content/50"></span>
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="font-medium truncate">{{ $attachment->original_filename }}</div>
                        <div class="text-sm text-base-content/60">{{ $attachment->formatted_size }} - {{ $attachment->mime_type }}</div>
                        <div class="text-xs text-base-content/50">Uploaded {{ $attachment->created_at->diffForHumans() }}</div>
                    </div>
                    <a href="{{ $attachment->url }}" target="_blank" class="btn btn-ghost btn-sm">
                        <span class="icon-[tabler--external-link] size-4"></span>
                        View
                    </a>
                </div>

                <form action="{{ route('drive.update', $attachment->uuid) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <!-- File Name -->
                    <div class="form-control mb-4">
                        <label class="label">
                            <span class="label-text font-medium">Display Name <span class="text-error">*</span></span>
                        </label>
                        <input type="text" name="name" value="{{ old('name', $attachment->name) }}" class="input input-bordered" placeholder="Enter a name for this file" required />
                        @error('name')
                            <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                        @enderror
                    </div>

                    <!-- Description -->
                    <div class="form-control mb-4">
                        <label class="label">
                            <span class="label-text font-medium">Description</span>
                        </label>
                        <textarea name="description" class="textarea textarea-bordered" rows="3" placeholder="Add a description for this file (optional)">{{ old('description', $attachment->description) }}</textarea>
                        @error('description')
                            <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                        @enderror
                    </div>

                    <!-- Tags -->
                    <div class="form-control mb-4">
                        <label class="label">
                            <span class="label-text font-medium">Tags</span>
                        </label>
                        <div id="tags-container" class="flex flex-wrap gap-2 mb-2">
                            @foreach($attachment->tags as $tag)
                                <span class="badge badge-lg gap-1">
                                    {{ $tag->name }}
                                    <input type="hidden" name="tags[]" value="{{ $tag->name }}" />
                                    <button type="button" onclick="this.parentElement.remove()" class="btn btn-ghost btn-xs btn-circle">
                                        <span class="icon-[tabler--x] size-3"></span>
                                    </button>
                                </span>
                            @endforeach
                        </div>
                        <div class="flex gap-2">
                            <input type="text" id="tag-input" class="input input-bordered flex-1" placeholder="Type a tag and press Enter" />
                            <button type="button" onclick="addTag()" class="btn btn-ghost">
                                <span class="icon-[tabler--plus] size-4"></span>
                                Add
                            </button>
                        </div>
                        @if($existingTags->isNotEmpty())
                            <div class="mt-2">
                                <span class="text-xs text-base-content/60">Existing tags:</span>
                                <div class="flex flex-wrap gap-1 mt-1">
                                    @foreach($existingTags as $tag)
                                        <button type="button" onclick="addExistingTag('{{ $tag->name }}')" class="badge badge-sm hover:badge-primary cursor-pointer" style="background-color: {{ $tag->color }}20; color: {{ $tag->color }}">
                                            {{ $tag->name }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Share With Team Members -->
                    <div class="form-control mb-6">
                        <label class="label">
                            <span class="label-text font-medium">Share with Team Members</span>
                        </label>
                        <p class="text-sm text-base-content/60 mb-3">Selected team members will be able to view this file in their Drive.</p>

                        @php
                            $currentShares = $attachment->sharedWith->pluck('id')->toArray();
                        @endphp

                        @if($teamMembers->isEmpty())
                            <div class="text-sm text-base-content/50 italic">No team members available to share with.</div>
                        @else
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 max-h-60 overflow-y-auto p-2 border border-base-200 rounded-lg">
                                @foreach($teamMembers as $member)
                                    <label class="flex items-center gap-3 p-2 rounded-lg hover:bg-base-200 cursor-pointer">
                                        <input type="checkbox" name="share_with[]" value="{{ $member->id }}" class="checkbox checkbox-sm checkbox-primary" {{ in_array($member->id, old('share_with', $currentShares)) ? 'checked' : '' }} />
                                        <div class="avatar">
                                            <div class="w-8 h-8 rounded-full">
                                                <img src="{{ $member->avatar_url }}" alt="{{ $member->name }}" />
                                            </div>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="font-medium text-sm truncate">{{ $member->name }}</div>
                                            <div class="text-xs text-base-content/60 truncate">{{ $member->email }}</div>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <!-- Submit -->
                    <div class="flex justify-end gap-2">
                        <a href="{{ route('drive.index') }}" class="btn btn-ghost">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <span class="icon-[tabler--check] size-4"></span>
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
const tagsContainer = document.getElementById('tags-container');
const tagInput = document.getElementById('tag-input');

function addTag() {
    const value = tagInput.value.trim();
    if (value && !tagExists(value)) {
        const tag = createTagElement(value);
        tagsContainer.appendChild(tag);
    }
    tagInput.value = '';
}

function addExistingTag(name) {
    if (!tagExists(name)) {
        const tag = createTagElement(name);
        tagsContainer.appendChild(tag);
    }
}

function tagExists(name) {
    const inputs = tagsContainer.querySelectorAll('input[name="tags[]"]');
    return Array.from(inputs).some(input => input.value.toLowerCase() === name.toLowerCase());
}

function createTagElement(name) {
    const span = document.createElement('span');
    span.className = 'badge badge-lg gap-1';
    span.innerHTML = `
        ${name}
        <input type="hidden" name="tags[]" value="${name}" />
        <button type="button" onclick="this.parentElement.remove()" class="btn btn-ghost btn-xs btn-circle">
            <span class="icon-[tabler--x] size-3"></span>
        </button>
    `;
    return span;
}

tagInput.addEventListener('keypress', (e) => {
    if (e.key === 'Enter') {
        e.preventDefault();
        addTag();
    }
});
</script>
@endpush
@endsection
