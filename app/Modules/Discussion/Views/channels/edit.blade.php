@extends('layouts.app')

@section('content')
<div class="flex min-h-[calc(100vh-4rem)]">
    <!-- Fixed Sidebar - Channels List -->
    <aside class="w-64 bg-base-100 border-r border-base-200 flex-shrink-0 hidden lg:block">
        <div class="sticky top-16 h-[calc(100vh-4rem)] overflow-y-auto">
            <!-- Sidebar Header -->
            <div class="p-4 border-b border-base-200">
                <div class="flex items-center justify-between">
                    <h2 class="font-semibold text-base-content">Channels</h2>
                </div>
            </div>

            <!-- Channels List -->
            <nav class="p-2">
                <a href="{{ route('channels.show', $channel) }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors bg-primary/10 text-primary">
                    <span class="icon-[tabler--hash] size-4 flex-shrink-0"></span>
                    <span class="truncate flex-1">{{ $channel->name }}</span>
                    @if($channel->is_private)
                    <span class="icon-[tabler--lock] size-3 text-base-content/40"></span>
                    @endif
                </a>
            </nav>

            <!-- Back to Discussions -->
            <div class="p-4 border-t border-base-200 mt-auto">
                <a href="{{ route('discussions.index') }}" class="flex items-center gap-2 text-sm text-base-content/60 hover:text-base-content transition-colors">
                    <span class="icon-[tabler--arrow-left] size-4"></span>
                    Back to Discussions
                </a>
            </div>
        </div>
    </aside>

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
                <span class="text-base-content">Edit</span>
            </div>
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 rounded-lg {{ $channel->color_class }} flex items-center justify-center flex-shrink-0">
                    <span class="icon-[tabler--edit] size-5"></span>
                </div>
                <div class="flex-1 min-w-0">
                    <h1 class="text-lg font-bold text-base-content">Edit Channel</h1>
                    <p class="text-sm text-base-content/60">Update {{ $channel->tag }} settings</p>
                </div>
                <!-- Add Thread Button -->
                @if($channel->canPost($user))
                <a href="{{ route('channels.threads.create', $channel) }}" class="btn btn-primary btn-sm gap-2">
                    <span class="icon-[tabler--plus] size-4"></span>
                    <span class="hidden sm:inline">Add Thread</span>
                </a>
                @endif
            </div>
        </div>

        <!-- Form Content -->
        <div class="flex-1 p-4 md:p-6 pt-3">
            <div class="max-w-2xl">
                <!-- Error Messages -->
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
                            <span class="label-text font-medium">Channel Tag <span class="text-error">*</span></span>
                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-base-content/50 font-medium">#</span>
                            <input type="text" name="tag" value="{{ old('tag', ltrim($channel->tag, '#')) }}" placeholder="marketing" class="input input-bordered pl-8 w-full @error('tag') input-error @enderror" required maxlength="50" pattern="[a-zA-Z0-9_-]+" />
                        </div>
                        <label class="label"><span class="label-text-alt text-base-content/50">Only letters, numbers, hyphens and underscores</span></label>
                        @error('tag')
                            <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                        @enderror
                    </div>

                    <!-- Short Description -->
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Short Description</span>
                        </label>
                        <textarea name="description" placeholder="Describe what this channel is about..." class="textarea textarea-bordered h-24" maxlength="500">{{ old('description', $channel->description) }}</textarea>
                    </div>

                    <!-- Channel Color -->
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Channel Color</span>
                        </label>
                        <div class="flex flex-wrap gap-3">
                            @php
                                $colors = [
                                    'primary' => 'Primary',
                                    'secondary' => 'Secondary',
                                    'accent' => 'Accent',
                                    'info' => 'Info',
                                    'success' => 'Success',
                                    'warning' => 'Warning',
                                    'error' => 'Error',
                                ];
                            @endphp
                            @foreach($colors as $value => $label)
                                <label class="cursor-pointer">
                                    <input type="radio" name="color" value="{{ $value }}" class="hidden peer" {{ old('color', $channel->color) === $value ? 'checked' : '' }} />
                                    <div class="w-10 h-10 rounded-lg bg-{{ $value }}/20 border-2 border-transparent peer-checked:border-{{ $value }} flex items-center justify-center transition-all hover:scale-110">
                                        <span class="icon-[tabler--check] size-5 text-{{ $value }} opacity-0 peer-checked:opacity-100"></span>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- Privacy -->
                    <div class="form-control">
                        <label class="label cursor-pointer justify-start gap-3">
                            <input type="checkbox" name="is_private" value="1" class="toggle toggle-primary" {{ old('is_private', $channel->is_private) ? 'checked' : '' }} />
                            <div>
                                <span class="label-text font-medium">Private Channel</span>
                                <p class="text-xs text-base-content/50">Only invited members can view and post in this channel</p>
                            </div>
                        </label>
                    </div>

                    <!-- Team Members -->
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Team Members</span>
                        </label>
                        <div class="border border-base-300 rounded-lg p-4 max-h-64 overflow-y-auto">
                            @if($members->isEmpty())
                                <p class="text-sm text-base-content/50 text-center py-4">No team members available</p>
                            @else
                                <div class="space-y-2">
                                    @foreach($members as $member)
                                    <label class="flex items-center gap-3 p-2 rounded-lg hover:bg-base-200 cursor-pointer transition-colors">
                                        <input type="checkbox" name="member_ids[]" value="{{ $member->id }}" class="checkbox checkbox-sm checkbox-primary" {{ in_array($member->id, old('member_ids', $channelMemberIds)) ? 'checked' : '' }} />
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
                        <label class="label">
                            <span class="label-text-alt text-base-content/50">Note: Channel creator ({{ $channel->creator->name }}) will always remain a member</span>
                        </label>
                    </div>

                    <!-- Actions -->
                    <div class="flex justify-between items-center pt-4 border-t border-base-200">
                        <button type="button" onclick="document.getElementById('deleteChannelForm').submit()" class="btn btn-ghost text-error">
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

                <!-- Hidden Delete Form -->
                <form id="deleteChannelForm" action="{{ route('channels.destroy', $channel) }}" method="POST" class="hidden" onsubmit="return confirm('Are you sure you want to delete this channel? All threads and replies will be permanently deleted.')">
                    @csrf
                    @method('DELETE')
                </form>
            </div>
        </div>
    </main>
</div>
@endsection
