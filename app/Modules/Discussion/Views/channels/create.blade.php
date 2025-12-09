@extends('layouts.app')

@section('content')
<div class="flex min-h-[calc(100vh-4rem)]">
    @include('discussion::channels.partials.sidebar')

    <!-- Main Content Area -->
    <main class="flex-1 min-w-0 flex flex-col bg-base-100">
        <!-- Header -->
        <div class="border-b border-base-200 px-4 md:px-6 py-2 sticky top-16 z-10 bg-base-100">
            <!-- Breadcrumb -->
            <div class="flex items-center gap-1 text-xs text-base-content/60 mb-1">
                <a href="{{ route('discussions.index') }}" class="hover:text-primary">Discussions</a>
                <span class="icon-[tabler--chevron-right] size-3"></span>
                <a href="{{ route('channels.index') }}" class="hover:text-primary">Channels</a>
                <span class="icon-[tabler--chevron-right] size-3"></span>
                <span class="text-base-content">Create Channel</span>
            </div>
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 rounded-lg bg-primary/20 flex items-center justify-center flex-shrink-0">
                    <span class="icon-[tabler--plus] size-5 text-primary"></span>
                </div>
                <div>
                    <h1 class="text-lg font-bold text-base-content">Create Team Channel</h1>
                    <p class="text-sm text-base-content/60">Create a new channel for team discussions</p>
                </div>
            </div>
        </div>

        <!-- Form Content -->
        <div class="flex-1 p-4 md:p-6 pt-3">
            <div class="max-w-2xl mx-auto">
                <!-- Validation Errors -->
                @if($errors->any())
                <div class="alert alert-error mb-4">
                    <span class="icon-[tabler--alert-circle] size-5"></span>
                    <div>
                        <ul class="list-disc list-inside">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                @endif

                <!-- Create Form -->
                <form action="{{ route('channels.store') }}" method="POST" class="space-y-6">
                    @csrf

                    <!-- Channel Name -->
                    <div class="form-control">
                        <label class="label" for="channel-name">
                            <span class="label-text font-medium">Channel Name <span class="text-error">*</span></span>
                        </label>
                        <input type="text" name="name" id="channel-name" value="{{ old('name') }}" placeholder="e.g., Marketing" class="input input-bordered @error('name') input-error @enderror" required maxlength="100" />
                        @error('name')
                            <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                        @enderror
                    </div>

                    <!-- Channel Tag -->
                    <div class="form-control">
                        <label class="label" for="channel-tag">
                            <span class="label-text font-medium">Channel Tag <span class="text-error">*</span> <span class="text-base-content/50 font-normal">Unique identifier</span></span>
                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-base-content/50 font-medium">#</span>
                            <input type="text" name="tag" id="channel-tag" value="{{ old('tag') }}" placeholder="admin resources" class="input input-bordered pl-8 w-full @error('tag') input-error @enderror" required maxlength="50" />
                        </div>
                        @error('tag')
                            <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                        @enderror
                    </div>

                    <!-- Short Description -->
                    <div class="form-control">
                        <label class="label" for="channel-description">
                            <span class="label-text font-medium">Short Description <span class="text-base-content/50 font-normal">Optional</span></span>
                        </label>
                        <textarea name="description" id="channel-description" placeholder="Describe what this channel is about..." class="textarea textarea-bordered h-24 @error('description') textarea-error @enderror" maxlength="500">{{ old('description') }}</textarea>
                        @error('description')
                            <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                        @enderror
                    </div>

                    <!-- Channel Color -->
                    <div class="form-control">
                        <span class="label">
                            <span class="label-text font-medium">Channel Color</span>
                        </span>
                        <div class="flex flex-wrap gap-2">
                            <label class="cursor-pointer" title="Blue">
                                <input type="radio" name="color" value="primary" class="hidden peer" {{ old('color', 'primary') === 'primary' ? 'checked' : '' }} />
                                <div class="w-8 h-8 rounded-full bg-blue-500 border-2 border-transparent peer-checked:border-base-content peer-checked:ring-2 peer-checked:ring-blue-500 peer-checked:ring-offset-2 transition-all hover:scale-110"></div>
                            </label>
                            <label class="cursor-pointer" title="Purple">
                                <input type="radio" name="color" value="secondary" class="hidden peer" {{ old('color') === 'secondary' ? 'checked' : '' }} />
                                <div class="w-8 h-8 rounded-full bg-purple-500 border-2 border-transparent peer-checked:border-base-content peer-checked:ring-2 peer-checked:ring-purple-500 peer-checked:ring-offset-2 transition-all hover:scale-110"></div>
                            </label>
                            <label class="cursor-pointer" title="Pink">
                                <input type="radio" name="color" value="accent" class="hidden peer" {{ old('color') === 'accent' ? 'checked' : '' }} />
                                <div class="w-8 h-8 rounded-full bg-pink-500 border-2 border-transparent peer-checked:border-base-content peer-checked:ring-2 peer-checked:ring-pink-500 peer-checked:ring-offset-2 transition-all hover:scale-110"></div>
                            </label>
                            <label class="cursor-pointer" title="Cyan">
                                <input type="radio" name="color" value="info" class="hidden peer" {{ old('color') === 'info' ? 'checked' : '' }} />
                                <div class="w-8 h-8 rounded-full bg-cyan-500 border-2 border-transparent peer-checked:border-base-content peer-checked:ring-2 peer-checked:ring-cyan-500 peer-checked:ring-offset-2 transition-all hover:scale-110"></div>
                            </label>
                            <label class="cursor-pointer" title="Green">
                                <input type="radio" name="color" value="success" class="hidden peer" {{ old('color') === 'success' ? 'checked' : '' }} />
                                <div class="w-8 h-8 rounded-full bg-green-500 border-2 border-transparent peer-checked:border-base-content peer-checked:ring-2 peer-checked:ring-green-500 peer-checked:ring-offset-2 transition-all hover:scale-110"></div>
                            </label>
                            <label class="cursor-pointer" title="Yellow">
                                <input type="radio" name="color" value="warning" class="hidden peer" {{ old('color') === 'warning' ? 'checked' : '' }} />
                                <div class="w-8 h-8 rounded-full bg-yellow-500 border-2 border-transparent peer-checked:border-base-content peer-checked:ring-2 peer-checked:ring-yellow-500 peer-checked:ring-offset-2 transition-all hover:scale-110"></div>
                            </label>
                            <label class="cursor-pointer" title="Red">
                                <input type="radio" name="color" value="error" class="hidden peer" {{ old('color') === 'error' ? 'checked' : '' }} />
                                <div class="w-8 h-8 rounded-full bg-red-500 border-2 border-transparent peer-checked:border-base-content peer-checked:ring-2 peer-checked:ring-red-500 peer-checked:ring-offset-2 transition-all hover:scale-110"></div>
                            </label>
                            <label class="cursor-pointer" title="Orange">
                                <input type="radio" name="color" value="orange" class="hidden peer" {{ old('color') === 'orange' ? 'checked' : '' }} />
                                <div class="w-8 h-8 rounded-full bg-orange-500 border-2 border-transparent peer-checked:border-base-content peer-checked:ring-2 peer-checked:ring-orange-500 peer-checked:ring-offset-2 transition-all hover:scale-110"></div>
                            </label>
                            <label class="cursor-pointer" title="Teal">
                                <input type="radio" name="color" value="teal" class="hidden peer" {{ old('color') === 'teal' ? 'checked' : '' }} />
                                <div class="w-8 h-8 rounded-full bg-teal-500 border-2 border-transparent peer-checked:border-base-content peer-checked:ring-2 peer-checked:ring-teal-500 peer-checked:ring-offset-2 transition-all hover:scale-110"></div>
                            </label>
                            <label class="cursor-pointer" title="Indigo">
                                <input type="radio" name="color" value="indigo" class="hidden peer" {{ old('color') === 'indigo' ? 'checked' : '' }} />
                                <div class="w-8 h-8 rounded-full bg-indigo-500 border-2 border-transparent peer-checked:border-base-content peer-checked:ring-2 peer-checked:ring-indigo-500 peer-checked:ring-offset-2 transition-all hover:scale-110"></div>
                            </label>
                            <label class="cursor-pointer" title="Gray">
                                <input type="radio" name="color" value="gray" class="hidden peer" {{ old('color') === 'gray' ? 'checked' : '' }} />
                                <div class="w-8 h-8 rounded-full bg-gray-500 border-2 border-transparent peer-checked:border-base-content peer-checked:ring-2 peer-checked:ring-gray-500 peer-checked:ring-offset-2 transition-all hover:scale-110"></div>
                            </label>
                        </div>
                    </div>

                    <!-- Channel Status -->
                    <div class="form-control">
                        <span class="label">
                            <span class="label-text font-medium">Channel Status</span>
                        </span>
                        <div class="flex flex-wrap gap-3">
                            <label class="cursor-pointer flex items-center gap-2 p-3 rounded-lg border border-base-300 hover:border-success transition-colors has-[:checked]:border-success has-[:checked]:bg-success/10">
                                <input type="radio" name="status" value="active" class="radio radio-success radio-sm" {{ old('status', 'active') === 'active' ? 'checked' : '' }} />
                                <div>
                                    <span class="font-medium text-sm">Active</span>
                                    <p class="text-xs text-base-content/50">Members can post and view threads</p>
                                </div>
                            </label>
                            <label class="cursor-pointer flex items-center gap-2 p-3 rounded-lg border border-base-300 hover:border-warning transition-colors has-[:checked]:border-warning has-[:checked]:bg-warning/10">
                                <input type="radio" name="status" value="inactive" class="radio radio-warning radio-sm" {{ old('status') === 'inactive' ? 'checked' : '' }} />
                                <div>
                                    <span class="font-medium text-sm">Inactive</span>
                                    <p class="text-xs text-base-content/50">Read-only, no new posts allowed</p>
                                </div>
                            </label>
                            <label class="cursor-pointer flex items-center gap-2 p-3 rounded-lg border border-base-300 hover:border-neutral transition-colors has-[:checked]:border-neutral has-[:checked]:bg-neutral/10">
                                <input type="radio" name="status" value="archive" class="radio radio-sm" {{ old('status') === 'archive' ? 'checked' : '' }} />
                                <div>
                                    <span class="font-medium text-sm">Archived</span>
                                    <p class="text-xs text-base-content/50">Hidden from listing, read-only</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Invite Team Members -->
                    <div class="form-control">
                        <label class="label" for="member-search">
                            <span class="label-text font-medium">Invite Team Members <span class="text-base-content/50 font-normal">Optional</span></span>
                        </label>

                        @if($members->isNotEmpty())
                        <!-- Search Box -->
                        <div class="mb-3">
                            <input type="text" id="member-search" placeholder="Search team members..." class="input input-bordered input-sm w-full" />
                        </div>

                        <!-- Select All -->
                        <div class="flex items-center gap-2 mb-2 pb-2 border-b border-base-200">
                            <input type="checkbox" id="select-all-members" class="checkbox checkbox-sm checkbox-primary" />
                            <label for="select-all-members" class="text-sm font-medium cursor-pointer">Select All</label>
                            <span id="selected-count" class="text-xs text-base-content/50 ml-auto">0 selected</span>
                        </div>
                        @endif

                        <div class="border border-base-300 rounded-lg max-h-64 overflow-y-auto">
                            @if($members->isEmpty())
                                <p class="text-sm text-base-content/50 text-center py-4">No team members available</p>
                            @else
                                <div class="p-2 space-y-1" id="members-list">
                                    @foreach($members as $member)
                                    <label class="member-item flex items-center gap-3 p-2 rounded-lg hover:bg-base-200 cursor-pointer transition-colors" data-name="{{ strtolower($member->name) }}" data-email="{{ strtolower($member->email) }}">
                                        <input type="checkbox" name="member_ids[]" value="{{ $member->id }}" class="member-checkbox checkbox checkbox-sm checkbox-primary" {{ in_array($member->id, old('member_ids', [])) ? 'checked' : '' }} />
                                        <div class="avatar placeholder">
                                            @if($member->avatar_url)
                                                <div class="w-8 h-8 rounded-full">
                                                    <img src="{{ $member->avatar_url }}" alt="{{ $member->name }}" />
                                                </div>
                                            @else
                                                <div class="w-8 h-8 rounded-full bg-primary/10 text-primary">
                                                    <span class="text-sm">{{ substr($member->first_name, 0, 1) }}{{ substr($member->last_name, 0, 1) }}</span>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="font-medium text-sm truncate">{{ $member->name }}</div>
                                            <div class="text-xs text-base-content/50 truncate">{{ $member->email }}</div>
                                        </div>
                                        <span class="badge badge-{{ $member->role_color }} badge-sm">{{ $member->role_label }}</span>
                                    </label>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex justify-end gap-3 pt-4 border-t border-base-200">
                        <a href="{{ route('channels.index') }}" class="btn btn-ghost">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <span class="icon-[tabler--plus] size-5"></span>
                            Create Channel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('member-search');
    const selectAllCheckbox = document.getElementById('select-all-members');
    const memberItems = document.querySelectorAll('.member-item');
    const memberCheckboxes = document.querySelectorAll('.member-checkbox');
    const selectedCountEl = document.getElementById('selected-count');

    // Update selected count
    function updateSelectedCount() {
        const checked = document.querySelectorAll('.member-checkbox:checked').length;
        const visible = document.querySelectorAll('.member-item:not(.hidden) .member-checkbox').length;
        if (selectedCountEl) {
            selectedCountEl.textContent = checked + ' selected';
        }
        // Update select all state
        if (selectAllCheckbox && visible > 0) {
            const visibleChecked = document.querySelectorAll('.member-item:not(.hidden) .member-checkbox:checked').length;
            selectAllCheckbox.checked = visibleChecked === visible;
            selectAllCheckbox.indeterminate = visibleChecked > 0 && visibleChecked < visible;
        }
    }

    // Search functionality
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            memberItems.forEach(item => {
                const name = item.dataset.name || '';
                const email = item.dataset.email || '';
                if (name.includes(searchTerm) || email.includes(searchTerm)) {
                    item.classList.remove('hidden');
                } else {
                    item.classList.add('hidden');
                }
            });
            updateSelectedCount();
        });
    }

    // Select all functionality
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            const visibleCheckboxes = document.querySelectorAll('.member-item:not(.hidden) .member-checkbox');
            visibleCheckboxes.forEach(cb => {
                cb.checked = this.checked;
            });
            updateSelectedCount();
        });
    }

    // Individual checkbox change
    memberCheckboxes.forEach(cb => {
        cb.addEventListener('change', updateSelectedCount);
    });

    // Initial count
    updateSelectedCount();
});
</script>
@endpush
@endsection
