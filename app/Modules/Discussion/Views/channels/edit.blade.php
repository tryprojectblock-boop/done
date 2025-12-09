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
                <a href="{{ route('channels.show', $channel) }}" class="hover:text-primary">{{ $channel->name }}</a>
                <span class="icon-[tabler--chevron-right] size-3"></span>
                <span class="text-base-content">Edit Channel</span>
            </div>
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 rounded-lg {{ $channel->color_class }} flex items-center justify-center flex-shrink-0">
                    <span class="icon-[tabler--edit] size-5"></span>
                </div>
                <div>
                    <h1 class="text-lg font-bold text-base-content">Edit Team Channel</h1>
                    <p class="text-sm text-base-content/60">Update {{ $channel->tag }} settings</p>
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

                <!-- Edit Form -->
                <form action="{{ route('channels.update', $channel) }}" method="POST" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <!-- Channel Name -->
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Channel Name <span class="text-error">*</span></span>
                        </label>
                        <input type="text" name="name" value="{{ old('name', $channel->name) }}" placeholder="e.g., Marketing" class="input input-bordered @error('name') input-error @enderror" required maxlength="100" />
                        @error('name')
                            <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                        @enderror
                    </div>

                    <!-- Channel Tag -->
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Channel Tag <span class="text-error">*</span> <span class="text-base-content/50 font-normal">Unique identifier</span></span>
                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-base-content/50 font-medium">#</span>
                            <input type="text" name="tag" value="{{ old('tag', ltrim($channel->tag, '#')) }}" placeholder="admin resources" class="input input-bordered pl-8 w-full @error('tag') input-error @enderror" required maxlength="50" />
                        </div>
                        @error('tag')
                            <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                        @enderror
                    </div>

                    <!-- Short Description -->
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Short Description <span class="text-base-content/50 font-normal">Optional</span></span>
                        </label>
                        <textarea name="description" placeholder="Describe what this channel is about..." class="textarea textarea-bordered h-24 @error('description') textarea-error @enderror" maxlength="500">{{ old('description', $channel->description) }}</textarea>
                        @error('description')
                            <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                        @enderror
                    </div>

                    <!-- Channel Color -->
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Channel Color</span>
                        </label>
                        <div class="flex flex-wrap gap-2">
                            <label class="cursor-pointer" title="Blue">
                                <input type="radio" name="color" value="primary" class="hidden peer" {{ old('color', $channel->color) === 'primary' ? 'checked' : '' }} />
                                <div class="w-8 h-8 rounded-full bg-blue-500 border-2 border-transparent peer-checked:border-base-content peer-checked:ring-2 peer-checked:ring-blue-500 peer-checked:ring-offset-2 transition-all hover:scale-110"></div>
                            </label>
                            <label class="cursor-pointer" title="Purple">
                                <input type="radio" name="color" value="secondary" class="hidden peer" {{ old('color', $channel->color) === 'secondary' ? 'checked' : '' }} />
                                <div class="w-8 h-8 rounded-full bg-purple-500 border-2 border-transparent peer-checked:border-base-content peer-checked:ring-2 peer-checked:ring-purple-500 peer-checked:ring-offset-2 transition-all hover:scale-110"></div>
                            </label>
                            <label class="cursor-pointer" title="Pink">
                                <input type="radio" name="color" value="accent" class="hidden peer" {{ old('color', $channel->color) === 'accent' ? 'checked' : '' }} />
                                <div class="w-8 h-8 rounded-full bg-pink-500 border-2 border-transparent peer-checked:border-base-content peer-checked:ring-2 peer-checked:ring-pink-500 peer-checked:ring-offset-2 transition-all hover:scale-110"></div>
                            </label>
                            <label class="cursor-pointer" title="Cyan">
                                <input type="radio" name="color" value="info" class="hidden peer" {{ old('color', $channel->color) === 'info' ? 'checked' : '' }} />
                                <div class="w-8 h-8 rounded-full bg-cyan-500 border-2 border-transparent peer-checked:border-base-content peer-checked:ring-2 peer-checked:ring-cyan-500 peer-checked:ring-offset-2 transition-all hover:scale-110"></div>
                            </label>
                            <label class="cursor-pointer" title="Green">
                                <input type="radio" name="color" value="success" class="hidden peer" {{ old('color', $channel->color) === 'success' ? 'checked' : '' }} />
                                <div class="w-8 h-8 rounded-full bg-green-500 border-2 border-transparent peer-checked:border-base-content peer-checked:ring-2 peer-checked:ring-green-500 peer-checked:ring-offset-2 transition-all hover:scale-110"></div>
                            </label>
                            <label class="cursor-pointer" title="Yellow">
                                <input type="radio" name="color" value="warning" class="hidden peer" {{ old('color', $channel->color) === 'warning' ? 'checked' : '' }} />
                                <div class="w-8 h-8 rounded-full bg-yellow-500 border-2 border-transparent peer-checked:border-base-content peer-checked:ring-2 peer-checked:ring-yellow-500 peer-checked:ring-offset-2 transition-all hover:scale-110"></div>
                            </label>
                            <label class="cursor-pointer" title="Red">
                                <input type="radio" name="color" value="error" class="hidden peer" {{ old('color', $channel->color) === 'error' ? 'checked' : '' }} />
                                <div class="w-8 h-8 rounded-full bg-red-500 border-2 border-transparent peer-checked:border-base-content peer-checked:ring-2 peer-checked:ring-red-500 peer-checked:ring-offset-2 transition-all hover:scale-110"></div>
                            </label>
                            <label class="cursor-pointer" title="Orange">
                                <input type="radio" name="color" value="orange" class="hidden peer" {{ old('color', $channel->color) === 'orange' ? 'checked' : '' }} />
                                <div class="w-8 h-8 rounded-full bg-orange-500 border-2 border-transparent peer-checked:border-base-content peer-checked:ring-2 peer-checked:ring-orange-500 peer-checked:ring-offset-2 transition-all hover:scale-110"></div>
                            </label>
                            <label class="cursor-pointer" title="Teal">
                                <input type="radio" name="color" value="teal" class="hidden peer" {{ old('color', $channel->color) === 'teal' ? 'checked' : '' }} />
                                <div class="w-8 h-8 rounded-full bg-teal-500 border-2 border-transparent peer-checked:border-base-content peer-checked:ring-2 peer-checked:ring-teal-500 peer-checked:ring-offset-2 transition-all hover:scale-110"></div>
                            </label>
                            <label class="cursor-pointer" title="Indigo">
                                <input type="radio" name="color" value="indigo" class="hidden peer" {{ old('color', $channel->color) === 'indigo' ? 'checked' : '' }} />
                                <div class="w-8 h-8 rounded-full bg-indigo-500 border-2 border-transparent peer-checked:border-base-content peer-checked:ring-2 peer-checked:ring-indigo-500 peer-checked:ring-offset-2 transition-all hover:scale-110"></div>
                            </label>
                            <label class="cursor-pointer" title="Gray">
                                <input type="radio" name="color" value="gray" class="hidden peer" {{ old('color', $channel->color) === 'gray' ? 'checked' : '' }} />
                                <div class="w-8 h-8 rounded-full bg-gray-500 border-2 border-transparent peer-checked:border-base-content peer-checked:ring-2 peer-checked:ring-gray-500 peer-checked:ring-offset-2 transition-all hover:scale-110"></div>
                            </label>
                        </div>
                    </div>

                    <!-- Channel Status -->
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Channel Status</span>
                        </label>
                        <div class="flex flex-wrap gap-3">
                            <label class="cursor-pointer flex items-center gap-2 p-3 rounded-lg border border-base-300 hover:border-success transition-colors has-[:checked]:border-success has-[:checked]:bg-success/10">
                                <input type="radio" name="status" value="active" class="radio radio-success radio-sm" {{ old('status', $channel->status) === 'active' ? 'checked' : '' }} />
                                <div>
                                    <span class="font-medium text-sm">Active</span>
                                    <p class="text-xs text-base-content/50">Members can post and view threads</p>
                                </div>
                            </label>
                            <label class="cursor-pointer flex items-center gap-2 p-3 rounded-lg border border-base-300 hover:border-warning transition-colors has-[:checked]:border-warning has-[:checked]:bg-warning/10">
                                <input type="radio" name="status" value="inactive" class="radio radio-warning radio-sm" {{ old('status', $channel->status) === 'inactive' ? 'checked' : '' }} />
                                <div>
                                    <span class="font-medium text-sm">Inactive</span>
                                    <p class="text-xs text-base-content/50">Read-only, no new posts allowed</p>
                                </div>
                            </label>
                            <label class="cursor-pointer flex items-center gap-2 p-3 rounded-lg border border-base-300 hover:border-neutral transition-colors has-[:checked]:border-neutral has-[:checked]:bg-neutral/10">
                                <input type="radio" name="status" value="archive" class="radio radio-sm" {{ old('status', $channel->status) === 'archive' ? 'checked' : '' }} />
                                <div>
                                    <span class="font-medium text-sm">Archived</span>
                                    <p class="text-xs text-base-content/50">Hidden from listing, read-only</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Manage Members Link -->
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Team Members</span>
                        </label>
                        <div class="border border-base-300 rounded-lg p-4">
                            <div class="flex items-center justify-between mb-3">
                                <p class="text-sm text-base-content/60">{{ $channel->members->count() }} {{ Str::plural('member', $channel->members->count()) }} in this channel</p>
                                <a href="{{ route('channels.members', $channel) }}" class="btn btn-sm btn-outline btn-primary">
                                    <span class="icon-[tabler--users] size-4"></span>
                                    Manage Members
                                </a>
                            </div>
                            <!-- Preview of members -->
                            @if($channel->members->isNotEmpty())
                            <div class="flex -space-x-2">
                                @foreach($channel->members->take(8) as $member)
                                <div class="avatar border-2 border-base-100 rounded-full">
                                    @if($member->avatar_url)
                                    <div class="w-8 h-8 rounded-full">
                                        <img src="{{ $member->avatar_url }}" alt="{{ $member->name }}" />
                                    </div>
                                    @else
                                    <div class="w-8 h-8 rounded-full bg-primary/10 text-primary flex items-center justify-center text-xs">
                                        {{ substr($member->first_name, 0, 1) }}{{ substr($member->last_name, 0, 1) }}
                                    </div>
                                    @endif
                                </div>
                                @endforeach
                                @if($channel->members->count() > 8)
                                <div class="avatar border-2 border-base-100 rounded-full">
                                    <div class="w-8 h-8 rounded-full bg-base-300 text-base-content flex items-center justify-center text-xs">
                                        +{{ $channel->members->count() - 8 }}
                                    </div>
                                </div>
                                @endif
                            </div>
                            @endif
                        </div>
                        <label class="label">
                            <span class="label-text-alt text-base-content/50">Note: Channel creator ({{ $channel->creator->name }}) will always remain a member</span>
                        </label>
                    </div>

                    <!-- Actions -->
                    <div class="flex justify-between items-center pt-4 border-t border-base-200">
                        <button type="button" onclick="openModal('deleteChannelModal')" class="btn btn-ghost text-error">
                            <span class="icon-[tabler--trash] size-5"></span>
                            Delete Channel
                        </button>
                        <div class="flex gap-3">
                            <a href="{{ route('channels.show', $channel) }}" class="btn btn-ghost">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <span class="icon-[tabler--check] size-5"></span>
                                Save Changes
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>

<!-- Delete Channel Confirmation Modal -->
<div id="deleteChannelModal" class="channel-modal">
    <div class="channel-modal-backdrop" onclick="closeModal('deleteChannelModal')"></div>
    <div class="channel-modal-box bg-base-100 rounded-lg shadow-xl p-6 max-w-md w-full mx-4">
        <button type="button" onclick="closeModal('deleteChannelModal')" class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">
            <span class="icon-[tabler--x] size-5"></span>
        </button>
        <div class="text-center mb-4">
            <div class="w-16 h-16 rounded-full bg-error/10 flex items-center justify-center mx-auto mb-4">
                <span class="icon-[tabler--trash] size-8 text-error"></span>
            </div>
            <h3 class="font-bold text-lg">Delete Channel</h3>
            <p class="text-base-content/60 mt-2">Are you sure you want to delete <strong>{{ $channel->name }}</strong>?</p>
            <p class="text-sm text-error mt-2">This action cannot be undone. All threads and replies will be permanently deleted.</p>
        </div>
        <div class="flex justify-center gap-3 mt-6">
            <button type="button" onclick="closeModal('deleteChannelModal')" class="btn btn-ghost">Cancel</button>
            <form action="{{ route('channels.destroy', $channel) }}" method="POST">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-error">
                    <span class="icon-[tabler--trash] size-4"></span>
                    Delete Channel
                </button>
            </form>
        </div>
    </div>
</div>

<style>
.channel-modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 9999;
    justify-content: center;
    align-items: center;
}
.channel-modal.open {
    display: flex !important;
}
.channel-modal-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    z-index: 1;
}
.channel-modal-box {
    position: relative;
    z-index: 2;
    max-height: 90vh;
    overflow-y: auto;
}
</style>

<script>
function openModal(modalId) {
    document.getElementById(modalId).classList.add('open');
    document.body.style.overflow = 'hidden';
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.remove('open');
    document.body.style.overflow = '';
}

// Close modal on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('.channel-modal.open').forEach(function(modal) {
            modal.classList.remove('open');
        });
        document.body.style.overflow = '';
    }
});
</script>
@endsection
