@extends('layouts.app')

@php
    $user = auth()->user();
    // isClient = true only for inbox workspace guests (not regular workspace guests)
    // A client is a guest user viewing a ticket in an inbox workspace where they are a guest
    $isInboxWorkspace = $task->workspace->type->value === 'inbox';
    $isGuestUser = $user->is_guest || $user->role === \App\Models\User::ROLE_GUEST;
    $isWorkspaceGuest = $task->workspace->guests()->where('users.id', $user->id)->exists();
    $isClient = $isInboxWorkspace && $isGuestUser && $isWorkspaceGuest;
@endphp

@section('content')
<div class="p-4 md:p-6 pt-0! pr-0!">
    <div class="max-w mx-auto">
        

        <!-- Success/Error Messages -->
        @if(session('success'))
            <div class="alert alert-success mb-4">
                <span class="icon-[tabler--check] size-5"></span>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-error mb-4">
                <span class="icon-[tabler--x] size-5"></span>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <!-- On Hold Banner -->
        @if($task->isOnHold())
        <div class="alert alert-warning mb-4">
            <div class="flex items-start gap-3 w-full">
                <span class="icon-[tabler--player-pause] size-6 mt-0.5"></span>
                <div class="flex-1">
                    <h4 class="font-bold">Task On Hold</h4>
                    @if($task->hold_reason)
                        <p class="text-sm mt-1">{{ $task->hold_reason }}</p>
                    @endif
                    <p class="text-xs mt-2 opacity-70">
                        Put on hold by {{ $task->holdByUser?->name ?? 'Unknown' }}
                        @if($task->hold_at)
                            on {{ $task->hold_at->format('M d, Y \a\t g:i A') }}
                        @endif
                    </p>
                </div>
                <button type="button" class="btn btn-warning btn-sm" onclick="openResumeTaskModal()">
                    <span class="icon-[tabler--player-play] size-4"></span>
                    Resume
                </button>
            </div>
        </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Header -->
                <div class="py-7.5 m-0">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2 text-sm text-base-content/60">
                            <a href="{{ route('dashboard') }}" class="hover:text-primary text-[#525158]">Dashboard</a>
                            <span class="icon-[tabler--chevron-right] size-4 text-[#525158]"></span>
                            <a href="{{ route('tasks.index') }}" class="hover:text-primary text-[#525158]">Tasks</a>
                            <span class="icon-[tabler--chevron-right] size-4 text-[#525158]"></span>
                            <span class="text-[#B8B7BB]">{{ $task->task_number }}</span>
                        </div>

                        <div class="flex flex-wrap gap-2">
                            @if($isClient)
                                <!-- Client: Simple back button -->
                                <a href="{{ route('dashboard') }}" class="p-2 rounded-md bg-white border border-[#B8B7BB]">
                                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M17.5 10C17.5 10.4601 17.1271 10.8338 16.667 10.834L5.3457 10.834L8.92285 14.4111C9.24816 14.7365 9.24816 15.2644 8.92285 15.5898C8.59746 15.9152 8.06956 15.9152 7.74414 15.5898L2.74414 10.5898C2.41871 10.2644 2.41871 9.73655 2.74414 9.41113L7.74414 4.41113C8.06956 4.08578 8.59746 4.08573 8.92285 4.41113C9.24815 4.73655 9.24816 5.26442 8.92285 5.58984L5.3457 9.16699L16.667 9.16699C17.1269 9.16717 17.4997 9.54009 17.5 10Z" fill="#17151C"/>
                                    </svg>
                                    <!-- Back to My Tickets -->
                                </a>
                            @else
                                <!-- Back to Workspace Tasks -->
                                <a href="{{ route('workspace.show', ['workspace' => $task->workspace, 'tab' => 'tasks']) }}" class="p-2 rounded-md bg-white border border-[#B8B7BB]">
                                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M17.5 10C17.5 10.4601 17.1271 10.8338 16.667 10.834L5.3457 10.834L8.92285 14.4111C9.24816 14.7365 9.24816 15.2644 8.92285 15.5898C8.59746 15.9152 8.06956 15.9152 7.74414 15.5898L2.74414 10.5898C2.41871 10.2644 2.41871 9.73655 2.74414 9.41113L7.74414 4.41113C8.06956 4.08578 8.59746 4.08573 8.92285 4.41113C9.24815 4.73655 9.24816 5.26442 8.92285 5.58984L5.3457 9.16699L16.667 9.16699C17.1269 9.16717 17.4997 9.54009 17.5 10Z" fill="#17151C"/>
                                    </svg>
                                    <!-- Back to Workspace Tasks -->
                                </a>
                            @endif
                        </div>
                    </div>
                    <div class="flex flex-col md:flex-row md:items-start justify-between gap-4">
                        <div class="flex-1">
                            <!-- <p class="text-base-content/60 mt-1">
                                Created by {{ $task->creator->name }} on {{ $task->created_at->format('M d, Y') }}
                            </p> -->
                        </div>                    
                    </div>
                </div>
                <!-- Description -->
                <div class="rounded-md bg-base-100">
                    <div class="card-body">
                        <div class="flex flex-col items-start gap-2">
                            @if($task->status)
                                <div class="py-1 px-2 rounded-md text-xs mb-2" style="background-color: {{ $task->status->background_color }}20; color: {{ $task->status->background_color }}">
                                    {{ $task->status->name }}
                                </div>
                            @endif
                            <h1 class="font-semibold text-[32px] block leading-9 text-[#17151C] {{ $task->isClosed() ? 'line-through opacity-60' : '' }}">
                                {{ $task->title }}
                            </h1>
                            @if($task->is_private)
                                <span class="badge badge-warning gap-1" title="Only creator, assignee, and watchers can see this task">
                                    <span class="icon-[tabler--lock] size-3.5"></span>
                                    Private
                                </span>
                            @endif
                        </div>
                        <!-- <h2 class="card-title text-lg">
                            <span class="icon-[tabler--file-description] size-5"></span>
                            Description
                        </h2> -->
                        @if($task->description)
                            <div class="prose prose-sm max-w-none text task-description">
                                {!! $task->description !!}
                            </div>
                        @else
                            <p class="text-[base-content/60] italic">No description provided.</p>
                        @endif
                    </div>
                    <!-- Subtasks (only show for parent tasks, not for subtasks) -->
                    <div class="card-body">
                        @if($task->subtasks->count() > 0)
                            <!-- Header -->
                            <div class="flex items-center justify-between bg-[#F8F8FB] rounded-md px-3 py-[7px]">
                                <h2 class="text-sm leading-4.5 font-medium text-[#525158]">Sub-task</h2>
                                <h2 class="text-sm leading-4.5 font-medium text-[#525158]">Actions</h2>
                            </div>
                        @endif
                    @if(!$task->parentTask)
                    <div id="subtasks-section">
                        <div class=" bg-base-100" id="subtasks-card">
                            <div class="">
                                <!-- <h2 class="card-title text-lg">
                                    <span class="icon-[tabler--subtask] size-5"></span>
                                    Subtasks
                                    <span class="badge badge-sm" id="subtasks-count">{{ $task->subtasks->count() }}</span>
                                </h2> -->
                                <div class="space-y-2" id="subtasks-list">
                                    @forelse($task->subtasks as $subtask)
                                    <a href="{{ route('tasks.show', $subtask) }}" class="flex items-center border-b border-[#EDECF0] justify-between px-6 py-4 hover:bg-gray-50 transition-colors cursor-pointer no-underline">
                                        <div class="flex items-center gap-4">
                                            @if($subtask->status)
                                                <span class="px-3 py-1 text-sm font-medium text-green-700 bg-green-100 rounded" style="background-color: {{ $subtask->status->background_color }}20; color: {{ $subtask->status->background_color }}">{{ $subtask->status->name }}</span>
                                            @endif
                                            <span class="text-gray-900 flex-1 {{ $subtask->isClosed() ? 'line-through' : '' }}">{{ $subtask->title }}</span>
                                        </div>
                                        <span class="p-2 rounded-lg border border-gray-300 hover:bg-gray-50 transition-colors inline-block" onclick="event.preventDefault(); event.stopPropagation();">
                                            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                        </span>
                                    </a>
                                    @empty
                                        <p id="subtasks-empty-message" class="text-base-content/60 text-sm">No subtasks yet</p>
                                    @endforelse
                                </div>
                                <div class="mt-4">
                                    <button type="button" onclick="openSubtaskDrawer()" class=" border border-[#B8B7BB] p-2 pr-3 rounded-md hover:bg-gray-50">
                                        <span class="text-[#17151C]">Add Subtask</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                    </div>
                </div>

                <!-- Attachments -->
                <div class="rounded-md bg-base-100 shadow">
                    <div class="pt-0">
                        <h2 class="card-title text-base flex items-center gap-1.5 text-[#525158] py-2.5 px-4 border-b border-[#EDECF0]">
                            <span class="icon-[tabler--paperclip] size-5"></span>
                            <span>Attachments</span>
                            <span id="attachment-count">({{ $task->attachments->count() }})</span>
                        </h2>
                    </div>
                    <!-- Upload Button -->
                    <div class="mt-4 px-6">
                        <input type="file" name="files[]" id="attachment-files" multiple class="hidden" data-upload-url="{{ route('tasks.attachments.store', $task) }}">
                        <button type="button" id="attachment-upload-btn" class="py-2 pl-3 px-4 flex items-center gap-1.5 border border-[#B8B7BB] rounded-md" onclick="document.getElementById('attachment-files').click()">
                            <span class="icon-[tabler--upload] size-4" id="upload-icon"></span>
                            <span class="loading loading-spinner loading-sm hidden" id="upload-spinner"></span>
                            <span id="upload-btn-text" class="text-base">Upload File</span>
                        </button>
                    </div>
                    <div class="card-body">   
                        @if($task->attachments->isNotEmpty())
                            <!-- Header -->
                            <div class="flex items-center justify-between bg-[#F8F8FB] rounded-md px-3 py-[7px]">
                                <h2 class="text-sm leading-4.5 font-medium text-[#525158]">File Name</h2>
                                <h2 class="text-sm leading-4.5 font-medium text-[#525158]">Actions</h2>
                            </div>
                        @endif
                        <div class="space-y-2" id="attachments-list">
                        @forelse($task->attachments as $attachment)
                            <div class="flex items-center border-b border-[#EDECF0] justify-between px-4 py-4 hover:bg-gray-50 transition-colors" data-attachment-id="{{ $attachment->id }}">
                                <div class="flex items-center gap-4">
                                    <div class="flex-1 min-w-0">
                                        <p class="text-[#525158] text-sm font-medium">{{ $attachment->original_name }}</p>
                                        <p class="text-xs text-base-content/60">{{ $attachment->getFormattedSize() }}</p>
                                    </div>
                                </div>
                                <div class="flex gap-2">
                                    <a href="{{ route('tasks.attachments.download', $attachment) }}"
                                    class="w-7 h-7 flex items-center justify-center rounded-md border border-[#B8B7BB] hover:bg-gray-50 transition-colors"
                                    title="Download">
                                        <span class="icon-[tabler--download] size-4 text-[#525158]"></span>
                                    </a>
                                    @if($attachment->uploaded_by === $user->id || $user->isAdminOrHigher())
                                        <form action="{{ route('tasks.attachments.destroy', $attachment) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="w-7 h-7 flex items-center justify-center rounded-md border border-[#B8B7BB] hover:bg-red-50 transition-colors"
                                                    onclick="return confirm('Delete this attachment?')"
                                                    title="Delete">
                                                <span class="icon-[tabler--trash] size-4 text-[#525158]"></span>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <p id="attachments-empty-message" class="text-base-content/60 text-sm px-6 py-4">No attachments yet</p>
                        @endforelse
                    </div>
                        
                    </div>
                </div>

                <!-- Comments -->
                <div class="rounded-md bg-base-100 shadow">
                    
                        @php
                            $publicCommentsCount = $task->comments->where('is_private', false)->count();
                            $privateCommentsCount = $task->comments->where('is_private', true)->count();
                            $visibleCommentsCount = $isClient ? $publicCommentsCount : $task->comments->count();
                        @endphp

                        <!-- Filter Tabs Header (only show for team members) -->
                        @if(!$isClient)
                        <div class="mb-4">
                            <div class="tabs tabs-boxed rounded-lg inline-flex mb-3 task-comments-section">
                                <button type="button" class="tab tab-active flex items-center gap-1.5 rounded-tl-md border-b-2 border-transparent" data-filter-tab="all" onclick="switchFilterTab('all')">
                                    <span class="icon-[tabler--message] size-4 mr-1"></span>
                                    <span>All</span>
                                    <!-- <span class="badge badge-sm ml-1" id="all-count-badge">{{ $task->comments->count() }}</span> -->
                                </button>
                                <button type="button" class="tab flex items-center gap-1.5 border-b-2 border-transparent" data-filter-tab="public" onclick="switchFilterTab('public')">
                                    <svg width="17" height="17" viewBox="0 0 17 17" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M8.33366 0C3.8 0 0.000326157 3.26842 0.000326157 7.5V7.50732C0.0154209 9.19971 0.637712 10.8235 1.74186 12.0923L0.858073 15.6315C0.779286 15.9473 0.892132 16.2801 1.14616 16.4836C1.40018 16.687 1.74953 16.7246 2.04053 16.5788L5.86947 14.6598C6.67088 14.8856 7.49994 14.9999 8.33366 14.9992V15C12.8673 15 16.667 11.7316 16.667 7.5C16.667 3.26842 12.8673 0 8.33366 0ZM15.0003 7.5C15.0003 10.6351 12.1333 13.3333 8.33366 13.3333H8.33285C7.55732 13.3341 6.78631 13.2125 6.0485 12.9736L5.96956 12.9525C5.78425 12.9121 5.58966 12.936 5.41862 13.0216L2.91537 14.2757L3.46712 12.0597C3.53773 11.7763 3.45528 11.4763 3.24902 11.2695C2.2496 10.2677 1.68155 8.91484 1.66699 7.5C1.66699 4.36491 4.53399 1.66667 8.33366 1.66667C12.1333 1.66667 15.0003 4.36491 15.0003 7.5Z" fill="#3ba5ff"/>
                                    </svg>
                                    <span>Comments</span>
                                </button>
                                <button type="button" class="tab border-b-2 border-transparent" data-filter-tab="private" onclick="switchFilterTab('private')">
                                    <span class="icon-[tabler--lock] size-4 mr-1"></span>
                                    Private
                                    <!-- <span class="badge badge-sm ml-1" id="private-count-badge">{{ $privateCommentsCount }}</span> -->
                                </button>
                            </div>
                            <h2 class="rounded-md-title text-lg pt-6 pl-6 flex items-center gap-2" id="comments-section-title">
                                <span class="icon-[tabler--message-circle] size-5"></span>
                                <span id="comments-title-text">All Comments</span>
                                <span class="w-5 h-5 rounded-full bg-[#EDECF0] flex items-center justify-center text-xs text-[#17151C]" id="public-count-badge">{{ $publicCommentsCount }}</span>
                            </h2>
                        </div>
                        @else
                        <h2 class="card-title text-lg mb-4">
                            <span class="icon-[tabler--message-circle] size-5"></span>
                            Comments
                            <span class="badge badge-sm">{{ $visibleCommentsCount }}</span>
                        </h2>
                        @endif
                        <div class="card-body">
                        <!-- Add Comment Form -->
                        @if(!$isClient)
                        <div class="mb-4">
                            <!-- Comment Form -->
                            <form action="{{ route('tasks.comments.store', $task) }}" method="POST" id="comment-form" onsubmit="return prepareCommentSubmit()">
                                @csrf
                                <input type="hidden" name="is_private" id="is_private_input" value="0">
                                <input type="hidden" name="content" id="final_content_input" value="">
                                <div class="flex gap-3">
                                    <div class="avatar">
                                        <div class="w-8 h-8 rounded-full overflow-hidden">
                                            <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="w-full h-full object-cover" />
                                        </div>
                                    </div>
                                    <div class="flex-1">
                                        <!-- Comment Editor -->
                                        <div id="comment-editor-wrapper">
                                            <x-quill-editor
                                                name="comment_content"
                                                id="comment-editor"
                                                placeholder="Add a comment..."
                                                height="100px"
                                                :mentions="true"
                                            />
                                        </div>
                                        <!-- Private Note Editor (with yellow border) -->
                                        <div id="private-editor-wrapper" class="hidden">
                                            <div class="border-2 border-warning rounded-lg overflow-hidden">
                                                <div class="bg-warning/10 px-3 py-1.5 border-b border-warning/30 flex items-center gap-2">
                                                    <span class="icon-[tabler--lock] size-4 text-warning"></span>
                                                    <span class="text-sm font-medium text-warning">Private Note - Only visible to team members</span>
                                                </div>
                                                <x-quill-editor
                                                    name="private_content"
                                                    id="private-editor"
                                                    placeholder="Add a private note (only visible to team members)..."
                                                    height="100px"
                                                    :mentions="true"
                                                />
                                            </div>
                                        </div>
                                        <div class="flex justify-start mt-4">
                                            <button type="submit" class="bg-[#3da4fd] text-white rounded-md py-2.5 pl-3 pr-4 btn-sm flex items-center gap-1" id="comment-submit-btn">
                                                <span class="icon-[tabler--send] size-4"></span>
                                                <span id="submit-btn-text">Comment</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                        @else
                        <!-- Client view - simple comment form without tabs -->
                        <form action="{{ route('tasks.comments.store', $task) }}" method="POST" class="mb-4" id="comment-form">
                            @csrf
                            <div class="flex gap-3">
                                <div class="avatar">
                                    <div class="w-8 h-8 rounded-full overflow-hidden">
                                        <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="w-full h-full object-cover" />
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <x-quill-editor
                                        name="content"
                                        id="comment-editor"
                                        placeholder="Add a comment..."
                                        height="100px"
                                        :mentions="false"
                                    />
                                    <div class="flex justify-end mt-2">
                                        <button type="submit" class="btn btn-primary btn-sm">
                                            <span class="icon-[tabler--send] size-4"></span>
                                            Comment
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                        @endif

                        <!-- Comments List -->
                        <div class="space-y-4" id="comments-list">
                            @php
                                $visibleComments = $isClient
                                    ? $task->comments->where('is_private', false)->sortByDesc('created_at')
                                    : $task->comments->sortByDesc('created_at');
                            @endphp
                            @foreach($visibleComments as $comment)
                                <div class="comment-item" data-is-private="{{ $comment->is_private ? '1' : '0' }}">
                                    @include('task::partials.comment', ['comment' => $comment])
                                </div>
                            @endforeach
                            <p class="text-base-content/60 text-center py-4 empty-comments-message {{ $visibleComments->count() > 0 ? 'hidden' : '' }}">No comments yet. Be the first to comment!</p>
                        </div>
                    </div>
                </div>
                
                <!-- Activity Log -->
                <div class="card bg-base-100 shadow">
                    <div class="card-body">
                        <h2 class="card-title text-lg flex items-center gap-2 mb-6">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M3 17V7C3 4.79086 4.79086 3 7 3H17C19.2091 3 21 4.79086 21 7V17C21 19.2091 19.2091 21 17 21H7C4.79086 21 3 19.2091 3 17Z" stroke="#5334E4" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M8 16L9.8793 12.4406C10.2993 11.6452 11.4002 11.5485 11.9525 12.2584C12.5163 12.9832 13.645 12.8641 14.0452 12.0377L16 8" stroke="#5334E4" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <span class="text-[#17151C] text-xl leading-6">Activity</span>
                        </h2>

                        <div class="relative">
                            <div class="space-y-4">
                                @forelse($task->activities as $activity)
                                    <div class="flex gap-4 relative">
                                        <div class="relative flex flex-col items-center">
                                            <!-- Avatar -->
                                            <div class="w-8 h-8 avatar rounded-full flex items-center justify-center z-10 bg-white">
                                                <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="w-full h-full object-cover rounded-full" />
                                            </div>
                                            <!-- Connecting line - starts after avatar with gap -->
                                            @if(!$loop->last)
                                                <div class="w-0.5 bg-gray-300 flex-1 mt-3"></div>
                                            @endif
                                        </div>
                                        @php
                                            $fullDescription = $activity->getFormattedDescription();
                                            $userName = $activity->user?->name ?? 'Someone';
                                            $action = trim(str_replace($userName, '', $fullDescription));
                                        @endphp
                                        <div class="flex-1 pb-8">
                                            <p class="text-base text-[#17151C] pb-1">
                                                <span>{{ $userName }}</span>
                                                <span class="text-[#525158] ">{{ $action }}</span>
                                            </p>
                                            <!-- <p class="text-base text-[#17151C] pb-1">{{ $activity->getFormattedDescription() }}</p> -->
                                            <p class="text-sm text-[#B8B7BB]">{{ $activity->created_at->diffForHumans() }}</p>
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-base-content/60 text-center py-4">No activity recorded yet.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6 task-sidebar flex flex-col">
                <div class="bg-[#f6f5fe] mb-0 py-6 flex items-center justify-center gap-2">
                    @if(!$isClient && !$task->isClosed())
                    <!-- Action Buttons (hidden for closed tasks) -->
                    <div class="flex flex-wrap gap-2 border-b border-base-200">
                        <!-- Watch Button -->
                        <form action="{{ route('tasks.watch.toggle', $task) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="btn bg-white border border-[#B8B7BB] py-2 pl-2 pr-3 btn-no-shadow">
                                @if($task->isWatcher($user))
                                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M2.74414 2.74414C3.06957 2.41872 3.59741 2.41874 3.92285 2.74414L5.67578 4.49707C6.89539 3.78804 8.28975 3.33305 10 3.33301C13.2329 3.33301 15.5408 5.16033 17.4268 7.01855C19.069 8.63698 19.069 11.363 17.4268 12.9814C16.8893 13.511 16.3159 14.0366 15.6982 14.5195L17.2559 16.0771C17.5813 16.4026 17.5813 16.9304 17.2559 17.2559C16.9304 17.5811 16.4035 17.5811 16.0781 17.2559L2.74414 3.92285C2.4188 3.59747 2.4189 3.06958 2.74414 2.74414ZM3.98926 7.96191C3.90923 8.04032 3.82862 8.1196 3.74805 8.2002C2.76797 9.18057 2.76806 10.8321 3.74414 11.7939C5.56683 13.5899 7.46081 14.9999 10 15C10.3297 15 10.6488 14.976 10.958 14.9307L12.3525 16.3252C11.6257 16.5423 10.8441 16.667 10 16.667C6.76728 16.6669 4.4601 14.8396 2.57422 12.9814C0.927793 11.3592 0.946428 8.64594 2.56934 7.02246C2.64944 6.94233 2.72984 6.86239 2.81055 6.7832L3.98926 7.96191ZM10 5C8.81649 5.00004 7.81771 5.26397 6.9043 5.72559L8.30762 7.12891C8.80376 6.83576 9.38201 6.66706 10 6.66699C11.8409 6.66699 13.334 8.15906 13.334 10C13.334 10.618 13.1632 11.1953 12.8701 11.6914L14.5088 13.3301C15.1094 12.8758 15.6877 12.3547 16.2568 11.7939C17.2365 10.8283 17.2365 9.17166 16.2568 8.20605C14.4341 6.41009 12.5393 5 10 5ZM10.001 8.33301C10.001 8.48199 9.97905 8.62625 9.94238 8.76367L11.6094 10.4307C11.6461 10.2932 11.667 10.1491 11.667 10C11.667 9.07966 10.9213 8.33322 10.001 8.33301Z" fill="#17151C"/>
                                    </svg>
                                    <span class="text-[#17151C]">Unwatch</span>
                                @else
                                    <span class="icon-[tabler--eye] size-4 text-[#17151C]"></span>
                                    <span class="text-[#17151C]">Watch</span>
                                @endif
                            </button>
                        </form>

                        <!-- On Hold Button (only for creator, assignee, and admins) -->
                        @if($task->canManageHold($user))
                            @if($task->isOnHold())
                                <button type="button" class="btn btn-warning border border-[#B8B7BB] py-2 pl-2 pr-3 btn-no-shadow" onclick="openResumeTaskModal()">
                                    <span class="icon-[tabler--player-play] size-4 text-white"></span>
                                    <span class="text-white">Resume</span>
                                </button>
                            @else
                                <button type="button" class="btn bg-white border border-[#B8B7BB] py-2 pl-2 pr-3 btn-no-shadow" onclick="openOnHoldModal()">
                                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M7.5 12.5003C7.5 12.9606 7.8731 13.3337 8.33333 13.3337C8.79357 13.3337 9.16667 12.9606 9.16667 12.5003L9.16667 7.50033C9.16667 7.04009 8.79357 6.66699 8.33333 6.66699C7.8731 6.66699 7.5 7.04009 7.5 7.50033L7.5 12.5003Z" fill="#17151C"/>
                                        <path d="M10.833 12.5003C10.833 12.9606 11.2061 13.3337 11.6663 13.3337C12.1266 13.3337 12.4997 12.9606 12.4997 12.5003L12.4997 7.50033C12.4997 7.04009 12.1266 6.66699 11.6663 6.66699C11.2061 6.66699 10.833 7.04009 10.833 7.50033L10.833 12.5003Z" fill="#17151C"/>
                                        <path d="M16.667 10.0003C16.667 6.31843 13.6822 3.33366 10.0003 3.33366C6.31843 3.33366 3.33366 6.31843 3.33366 10.0003C3.33366 13.6822 6.31843 16.667 10.0003 16.667C13.6822 16.667 16.667 13.6822 16.667 10.0003ZM18.3337 10.0003C18.3337 14.6027 14.6027 18.3337 10.0003 18.3337C5.39795 18.3337 1.66699 14.6027 1.66699 10.0003C1.66699 5.39795 5.39795 1.66699 10.0003 1.66699C14.6027 1.66699 18.3337 5.39795 18.3337 10.0003Z" fill="#17151C"/>
                                    </svg>
                                    <span class="text-[#17151C]">On Hold</span>
                                </button>
                            @endif
                        @endif

                        <!-- Edit Button -->
                        @if($task->isOwner($user) && !$task->isOnHold())
                            <a href="{{ route('tasks.edit', $task) }}" class="btn bg-white border border-[#B8B7BB] py-2 pl-2 pr-3 btn-no-shadow">
                                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M15.0003 3.47624C14.8003 3.47624 14.6021 3.51565 14.4173 3.59222C14.2324 3.66879 14.0644 3.78103 13.9229 3.92252L4.49609 13.3494L3.68803 16.3122L6.65091 15.5042L16.0778 6.07734C16.2192 5.93585 16.3315 5.76788 16.4081 5.58302C16.4846 5.39816 16.524 5.20002 16.524 4.99993C16.524 4.79983 16.4846 4.6017 16.4081 4.41684C16.3315 4.23197 16.2192 4.064 16.0778 3.92252C15.9363 3.78103 15.7683 3.66879 15.5834 3.59222C15.3986 3.51565 15.2004 3.47624 15.0003 3.47624ZM13.7794 2.05242C14.1665 1.89209 14.5814 1.80957 15.0003 1.80957C15.4193 1.80957 15.8342 1.89209 16.2212 2.05242C16.6083 2.21275 16.96 2.44775 17.2563 2.744C17.5525 3.04026 17.7875 3.39196 17.9479 3.77903C18.1082 4.1661 18.1907 4.58097 18.1907 4.99993C18.1907 5.41889 18.1082 5.83375 17.9479 6.22082C17.7875 6.60789 17.5525 6.9596 17.2563 7.25585L7.67293 16.8392C7.57039 16.9417 7.44285 17.0157 7.30294 17.0539L2.71961 18.3039C2.4311 18.3826 2.12255 18.3006 1.91109 18.0892C1.69963 17.8777 1.61769 17.5692 1.69637 17.2807L2.94638 12.6973C2.98453 12.5574 3.05854 12.4299 3.16109 12.3273L12.7444 2.744C13.0407 2.44775 13.3924 2.21275 13.7794 2.05242Z" fill="#17151C"/>
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M11.0771 4.41107C11.4025 4.08563 11.9302 4.08563 12.2556 4.41107L15.5889 7.7444C15.9144 8.06984 15.9144 8.59748 15.5889 8.92291C15.2635 9.24835 14.7359 9.24835 14.4104 8.92291L11.0771 5.58958C10.7516 5.26414 10.7516 4.73651 11.0771 4.41107Z" fill="#17151C"/>
                                </svg>
                                <span class="text-[#17151C]">Edit</span>
                            </a>
                        @endif
                    </div>
                    @endif
                    <div class="flex flex-wrap gap-2">
                        @if($isClient == false)
                            @if($task->isOwner($user) && !$task->isOnHold())
                                @if($task->isClosed())
                                    <form action="{{ route('tasks.reopen', $task) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="btn btn-success btn-sm">
                                            Reopen
                                        </button>
                                    </form>
                                @else
                                    <form action="{{ route('tasks.close', $task) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="btn bg-[#00a63e] px-3 py-2 btn-no-shadow border-0">
                                            Close
                                        </button>
                                    </form>
                                @endif
                            @endif
                        @endif
                    </div>
                </div>
                <div class="bg-white h-full">
                    <!-- Task Info Card -->
                    <div class="card bg-base-100 box-no-shadow group">
                        <div class="card-body">
                            <div class="border-b border-[#EDECF0] pb-6 flex items-center gap-2">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M3 5C3 3.34315 4.34315 2 6 2H18C19.6569 2 21 3.34315 21 5V19C21 20.6569 19.6569 22 18 22H6C4.34315 22 3 20.6569 3 19V5Z" stroke="#3BA5FF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M8 10L16 10" stroke="#3BA5FF" stroke-width="2" stroke-linecap="round"/>
                                    <path d="M10 14L14 14" stroke="#3BA5FF" stroke-width="2" stroke-linecap="round"/>
                                </svg>
                                <h2 class="card-title text-xl text-[#17151C]">Details</h2>
                            </div>
                            <div class="divide-y divide-base-200">
                                <!-- Parent Task (shown first for subtasks) -->
                                @if($task->parentTask)
                                <div class="py-3 first:pt-0">
                                    <label class="text-sm font-medium text-base-content/70">Parent Task</label>
                                    <div class="mt-2">
                                        <a href="{{ route('tasks.show', $task->parentTask) }}" class="inline-flex items-center gap-1.5 text-primary hover:text-primary-focus transition-colors">
                                            <span class="icon-[tabler--subtask] size-4"></span>
                                            <span class="font-mono text-xs bg-base-200 px-1.5 py-0.5 rounded">{{ $task->parentTask->task_number }}</span>
                                            <span class="text-sm">{{ Str::limit($task->parentTask->title, 20) }}</span>
                                        </a>
                                    </div>
                                </div>
                                @endif

                                <!-- Status, Priority & Progress (Combined Inline) -->
                                <div class="py-4 first:pt-0">
                                    <!-- Display Mode -->
                                    <div id="quick-stats-display" class="mt-2 flex justify-between">
                                        <div class="flex items-center gap-4 flex-wrap">
                                            <!-- Priority -->
                                            <div class="flex flex-col gap-2">
                                                <span class="text-sm text-[#525158] font-normal">Priority</span>
                                                <div>
                                                    @if($task->workspace->type->value === 'inbox')
                                                        @if($task->workspacePriority)
                                                            <span class="py-1 px-2 rounded-md text-xs" style="background-color: {{ $task->workspacePriority->color }}15; color: {{ $task->workspacePriority->color }}">
                                                                {{ $task->workspacePriority->name }}
                                                            </span>
                                                        @else
                                                            <span class="py-1 px-2 rounded-md text-xs">No Priority</span>
                                                        @endif
                                                    @else
                                                        @if($task->priority)
                                                            <span class="py-1 px-2 rounded-md text-xs" style="background-color: {{ $task->priority->color() }}15; color: {{ $task->priority->color() }}">
                                                                {{ $task->priority->label() }}
                                                            </span>
                                                        @else
                                                            <span class="py-1 px-2 rounded-md text-xs">No Priority</span>
                                                        @endif
                                                    @endif
                                                </div>
                                            </div>
                                            <!-- Status Badge -->
                                            <div class="flex flex-col gap-2">
                                                <span class="text-sm text-[#525158] font-normal">Status</span>
                                                @if($task->status)
                                                    <span class="py-1 px-2 rounded-md text-xs" style="background-color: {{ $task->status->background_color }}20; color: {{ $task->status->background_color }}">
                                                        {{ $task->status->name }}
                                                    </span>
                                                @else
                                                    <span class="py-1 px-2 rounded-md text-xs">No Status</span>
                                                @endif
                                            </div>
                                            <!-- Progress Percentage -->
                                            <div class="flex flex-col gap-2  items-start">
                                                <span class="text-sm text-[#525158] font-normal">Progress</span>
                                                <span class="py-1 px-2 text-xs rounded-md {{ ($task->progress ?? 0) == 100 ? 'bg-success' : 'bg-primary text-white' }}">
                                                    {{ $task->progress ?? 0 }}%
                                                </span>
                                            </div>
                                        </div>
                                        <!-- Edit Mode -->
                                        <div class="flex">
                                            @if($task->canInlineEdit($user) && !$task->isClosed())
                                                <button type="button" class="w-7 h-7 bg-[#F8F8FB] rounded-md flex items-center justify-center" onclick="toggleEdit('quick-stats')" title="Edit">
                                                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M11.9997 2.7806C11.8396 2.7806 11.6811 2.81213 11.5332 2.87339C11.3853 2.93464 11.251 3.02443 11.1378 3.13762L3.59628 10.6791L2.94984 13.0494L5.32014 12.403L12.8616 4.86148C12.9748 4.74829 13.0646 4.61391 13.1259 4.46602C13.1871 4.31813 13.2186 4.15963 13.2186 3.99955C13.2186 3.83947 13.1871 3.68097 13.1259 3.53308C13.0646 3.38519 12.9748 3.25081 12.8616 3.13762C12.7484 3.02443 12.6141 2.93464 12.4662 2.87339C12.3183 2.81213 12.1598 2.7806 11.9997 2.7806ZM11.023 1.64155C11.3326 1.51328 11.6645 1.44727 11.9997 1.44727C12.3349 1.44727 12.6668 1.51328 12.9764 1.64155C13.2861 1.76981 13.5674 1.95781 13.8044 2.19481C14.0414 2.43181 14.2294 2.71318 14.3577 3.02283C14.486 3.33249 14.552 3.66438 14.552 3.99955C14.552 4.33472 14.486 4.66661 14.3577 4.97627C14.2294 5.28593 14.0414 5.56729 13.8044 5.80429L6.13776 13.471C6.05572 13.553 5.9537 13.6122 5.84177 13.6427L2.1751 14.6427C1.94429 14.7057 1.69745 14.6401 1.52829 14.471C1.35912 14.3018 1.29357 14.0549 1.35651 13.8241L2.35651 10.1575C2.38704 10.0455 2.44625 9.94352 2.52829 9.86148L10.195 2.19481C10.432 1.95781 10.7133 1.76981 11.023 1.64155Z" fill="#525158"/>
                                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M8.86225 3.52827C9.1226 3.26792 9.54471 3.26792 9.80506 3.52827L12.4717 6.19494C12.7321 6.45529 12.7321 6.8774 12.4717 7.13775C12.2114 7.3981 11.7893 7.3981 11.5289 7.13775L8.86225 4.47108C8.6019 4.21073 8.6019 3.78862 8.86225 3.52827Z" fill="#525158"/>
                                                    </svg>
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                    <!-- Edit Mode -->
                                    @if($task->canInlineEdit($user) && !$task->isClosed())
                                    <div id="quick-stats-edit" class="hidden mt-3 space-y-3 p-3 bg-base-200/50 rounded-lg">
                                        <!-- Status Select -->
                                        <div>
                                            <label class="text-xs font-medium text-base-content/60 mb-1 block">Status</label>
                                            <select id="quick-status-select" class="select select-bordered select-sm w-full">
                                                <option value="">No Status</option>
                                                @foreach($statuses as $status)
                                                    <option value="{{ $status->id }}" {{ $task->status_id == $status->id ? 'selected' : '' }}>
                                                        {{ $status->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <!-- Priority Select -->
                                        <div>
                                            <label class="text-xs font-medium text-base-content/60 mb-1 block">Priority</label>
                                            @if($task->workspace->type->value === 'inbox')
                                                <select id="quick-priority-select" data-type="workspace" class="select select-bordered select-sm w-full">
                                                    <option value="">No Priority</option>
                                                    @foreach($workspacePriorities as $wsPriority)
                                                        <option value="{{ $wsPriority->id }}" {{ $task->workspace_priority_id == $wsPriority->id ? 'selected' : '' }}>
                                                            {{ $wsPriority->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            @else
                                                <select id="quick-priority-select" data-type="task" class="select select-bordered select-sm w-full">
                                                    <option value="">No Priority</option>
                                                    @foreach(\App\Modules\Task\Enums\TaskPriority::cases() as $priority)
                                                        <option value="{{ $priority->value }}" {{ $task->priority == $priority ? 'selected' : '' }}>
                                                            {{ $priority->label() }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            @endif
                                        </div>

                                        <!-- Progress Slider -->
                                        <div>
                                            <label class="text-xs font-medium text-base-content/60 mb-1 block">Progress</label>
                                            <div class="flex items-center gap-2">
                                                <input type="range"
                                                    id="quick-progress-slider"
                                                    min="0"
                                                    max="100"
                                                    step="5"
                                                    value="{{ $task->progress ?? 0 }}"
                                                    class="range range-primary range-sm flex-1"
                                                />
                                                <span class="text-sm font-medium min-w-[3rem] text-right" id="quick-progress-percentage">{{ $task->progress ?? 0 }}%</span>
                                            </div>
                                        </div>

                                        <div class="flex gap-2 pt-2">
                                            <button type="button" class="btn btn-primary btn-xs" onclick="saveQuickStats()">
                                                <span class="icon-[tabler--check] size-3.5"></span>
                                                Save All
                                            </button>
                                            <button type="button" class="btn btn-ghost btn-xs" onclick="toggleEdit('quick-stats')">Cancel</button>
                                        </div>
                                    </div>
                                    @endif
                                </div>

                                <!-- Due Date & Created Date (Combined Inline) -->
                                <div class="py-4">
                                    <div class="flex justify-between">
                                        <!-- Display Mode -->
                                        <div id="dates-display" class="mt-2 flex-1">
                                            <div class="flex items-center gap-2">
                                                <!-- Due Date -->
                                                <div class="flex flex-col gap-2 w-1/2">
                                                    <span class="text-sm font-normal text-[#525158]">Due Date</span>
                                                    @if($task->due_date)
                                                        <span class="text-base leading-6 font-normal {{ $task->isOverdue() ? 'badge-error' : 'badge-warning' }} gap-1">
                                                            {{ $task->due_date->format('M d, Y') }}
                                                            @if($task->isOverdue())
                                                                (Overdue)
                                                            @endif
                                                        </span>
                                                    @else
                                                        <span class="text-base leading-6 font-normal">
                                                            <span class="icon-[tabler--calendar-due] size-3"></span>
                                                            No Due Date
                                                        </span>
                                                    @endif
                                                </div>
                                                <!-- Created Date -->
                                                <div class="flex flex-col gap-2 w-1/2">
                                                    <span class="text-sm font-normal text-[#525158]">Created Date</span>
                                                    <span class="text-base leading-6 font-normal">
                                                        {{ $task->created_at->format('M d, Y') }}
                                                    </span>
                                                </div> 
                                            </div>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            @if($task->canInlineEdit($user) && !$task->isClosed())
                                                <button type="button" class="w-7 h-7 bg-[#F8F8FB] rounded-md flex items-center justify-center" onclick="toggleEdit('dates')" title="Edit dates">
                                                    <span class="icon-[tabler--pencil] size-3.5"></span>
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                    <!-- Edit Mode -->
                                    @if($task->canInlineEdit($user) && !$task->isClosed())
                                    <div id="dates-edit" class="hidden mt-3 space-y-3 p-3 bg-base-200/50 rounded-lg">
                                        <input type="hidden" id="dates-due-date-input" value="{{ $task->due_date?->format('Y-m-d') }}">
                                        <input type="hidden" id="dates-created-date-input" value="{{ $task->created_at->format('Y-m-d') }}">

                                        <!-- Created Date - Click to show calendar -->
                                        <div>
                                            <label class="text-xs font-medium text-base-content/60 mb-1 block">Created Date</label>
                                            <button type="button" onclick="toggleDatesCalendar('created')" class="btn btn-sm btn-outline w-full justify-start gap-2">
                                                <span class="icon-[tabler--calendar-plus] size-4"></span>
                                                <span id="dates-created-display">{{ $task->created_at->format('M d, Y') }}</span>
                                                <span class="icon-[tabler--chevron-down] size-4 ml-auto transition-transform" id="dates-created-chevron"></span>
                                            </button>
                                            <!-- Created Date Calendar (hidden by default) -->
                                            <div id="dates-created-calendar" class="hidden mt-2 bg-base-100 rounded-lg p-3 border border-base-300">
                                                <div class="flex items-center justify-between mb-3">
                                                    <button type="button" onclick="changeDatesMonth('created', -1)" class="btn btn-ghost btn-xs btn-circle">
                                                        <span class="icon-[tabler--chevron-left] size-4"></span>
                                                    </button>
                                                    <span id="dates-created-month-year" class="font-semibold text-sm"></span>
                                                    <button type="button" onclick="changeDatesMonth('created', 1)" class="btn btn-ghost btn-xs btn-circle">
                                                        <span class="icon-[tabler--chevron-right] size-4"></span>
                                                    </button>
                                                </div>
                                                <div class="grid grid-cols-7 gap-1 mb-2">
                                                    <div class="text-center text-xs font-medium text-base-content/50 py-1">Su</div>
                                                    <div class="text-center text-xs font-medium text-base-content/50 py-1">Mo</div>
                                                    <div class="text-center text-xs font-medium text-base-content/50 py-1">Tu</div>
                                                    <div class="text-center text-xs font-medium text-base-content/50 py-1">We</div>
                                                    <div class="text-center text-xs font-medium text-base-content/50 py-1">Th</div>
                                                    <div class="text-center text-xs font-medium text-base-content/50 py-1">Fr</div>
                                                    <div class="text-center text-xs font-medium text-base-content/50 py-1">Sa</div>
                                                </div>
                                                <div id="dates-created-days" class="grid grid-cols-7 gap-1"></div>
                                                <div class="flex flex-wrap gap-1 mt-3 pt-3 border-t border-base-300">
                                                    <button type="button" onclick="setDatesQuickDate('created', 'today', event)" class="btn btn-soft btn-primary btn-xs">Today</button>
                                                    <button type="button" onclick="setDatesQuickDate('created', 'yesterday', event)" class="btn btn-soft btn-primary btn-xs">Yesterday</button>
                                                    <button type="button" onclick="setDatesQuickDate('created', 'last-week', event)" class="btn btn-soft btn-primary btn-xs">Last Week</button>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Due Date - Click to show calendar -->
                                        <div>
                                            <label class="text-xs font-medium text-base-content/60 mb-1 block">Due Date</label>
                                            <button type="button" onclick="toggleDatesCalendar('due')" class="btn btn-sm btn-outline w-full justify-start gap-2">
                                                <span class="icon-[tabler--calendar-due] size-4"></span>
                                                <span id="dates-due-display">{{ $task->due_date ? $task->due_date->format('M d, Y') : 'No Due Date' }}</span>
                                                <span class="icon-[tabler--chevron-down] size-4 ml-auto transition-transform" id="dates-due-chevron"></span>
                                            </button>
                                            <!-- Due Date Calendar (hidden by default) -->
                                            <div id="dates-due-calendar" class="hidden mt-2 bg-base-100 rounded-lg p-3 border border-base-300">
                                                <div class="flex items-center justify-between mb-3">
                                                    <button type="button" onclick="changeDatesMonth('due', -1)" class="btn btn-ghost btn-xs btn-circle">
                                                        <span class="icon-[tabler--chevron-left] size-4"></span>
                                                    </button>
                                                    <span id="dates-due-month-year" class="font-semibold text-sm"></span>
                                                    <button type="button" onclick="changeDatesMonth('due', 1)" class="btn btn-ghost btn-xs btn-circle">
                                                        <span class="icon-[tabler--chevron-right] size-4"></span>
                                                    </button>
                                                </div>
                                                <div class="grid grid-cols-7 gap-1 mb-2">
                                                    <div class="text-center text-xs font-medium text-base-content/50 py-1">Su</div>
                                                    <div class="text-center text-xs font-medium text-base-content/50 py-1">Mo</div>
                                                    <div class="text-center text-xs font-medium text-base-content/50 py-1">Tu</div>
                                                    <div class="text-center text-xs font-medium text-base-content/50 py-1">We</div>
                                                    <div class="text-center text-xs font-medium text-base-content/50 py-1">Th</div>
                                                    <div class="text-center text-xs font-medium text-base-content/50 py-1">Fr</div>
                                                    <div class="text-center text-xs font-medium text-base-content/50 py-1">Sa</div>
                                                </div>
                                                <div id="dates-due-days" class="grid grid-cols-7 gap-1"></div>
                                                <div class="flex flex-wrap gap-1 mt-3 pt-3 border-t border-base-300">
                                                    <button type="button" onclick="setDatesQuickDate('due', 'today', event)" class="btn btn-soft btn-primary btn-xs">Today</button>
                                                    <button type="button" onclick="setDatesQuickDate('due', 'tomorrow', event)" class="btn btn-soft btn-primary btn-xs">Tomorrow</button>
                                                    <button type="button" onclick="setDatesQuickDate('due', 'next-week', event)" class="btn btn-soft btn-primary btn-xs">Next Week</button>
                                                    <button type="button" onclick="clearDatesField('due', event)" class="btn btn-soft btn-error btn-xs">Clear</button>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="flex gap-2 pt-2">
                                            <button type="button" class="btn btn-primary btn-xs" onclick="saveDates()">
                                                <span class="icon-[tabler--check] size-3.5"></span>
                                                Save
                                            </button>
                                            <button type="button" class="btn btn-ghost btn-xs" onclick="toggleEdit('dates')">Cancel</button>
                                        </div>
                                    </div>
                                    @endif
                                </div>

                                <!-- Assignee & Created By (Combined Inline) -->
                                <div class="py-4">
                                    <!-- Display Mode -->
                                    <div class="flex justify-between">
                                        <div id="people-display" class="mt-2 flex-1">
                                            <div class="flex items-center gap-2">
                                                <!-- Assignee -->
                                                <div class="flex flex-col gap-2 w-1/2">
                                                    <span class="text-sm font-normal text-[#525158]">Assignee</span>
                                                    @if($task->assignee)
                                                        <div class="flex items-center gap-2 py-2.5">
                                                            <div class="avatar">
                                                                <div class="w-8 rounded-full">
                                                                    <img src="{{ $task->assignee->avatar_url }}" alt="{{ $task->assignee->name }}" />
                                                                </div>
                                                            </div>
                                                            <span>{{ $task->assignee->name }}</span>
                                                        </div>
                                                    @else
                                                        <span class="badge badge-sm badge-ghost gap-1">
                                                            <span class="icon-[tabler--user-off] size-3"></span>
                                                            Unassigned
                                                        </span>
                                                    @endif
                                                </div>
                                                <!-- Creator -->
                                                <div class="flex flex-col gap-2 w-1/2">
                                                    <span class="text-sm font-normal text-[#525158]">Created By</span>
                                                    <div class="flex items-center gap-2 py-2.5">
                                                        <div class="avatar">
                                                            <div class="w-8 rounded-full">
                                                                <img src="{{ $task->creator->avatar_url }}" alt="{{ $task->creator->name }}" />
                                                            </div>
                                                        </div>
                                                        <span>{{ $task->creator->name }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            @if($task->canInlineEdit($user) && !$task->isClosed())
                                                <button type="button" class="w-7 h-7 bg-[#F8F8FB] rounded-md flex items-center justify-center" onclick="toggleEdit('people')" title="Edit">
                                                    <span class="icon-[tabler--pencil] size-3.5"></span>
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                    <!-- Edit Mode -->
                                    @if($task->canInlineEdit($user) && !$task->isClosed())
                                    <div id="people-edit" class="hidden mt-3 space-y-3 p-3 bg-base-200/50 rounded-lg">
                                        <input type="hidden" id="people-assignee-input" value="{{ $task->assignee_id }}">
                                        <input type="hidden" id="people-creator-input" value="{{ $task->created_by }}">

                                        <!-- Assignee - Click to show dropdown -->
                                        <div>
                                            <label class="text-xs font-medium text-base-content/60 mb-1 block">Assignee</label>
                                            <button type="button" onclick="togglePeopleDropdown('assignee')" class="btn btn-sm btn-outline w-full justify-start gap-2">
                                                @if($task->assignee)
                                                    <div class="avatar">
                                                        <div class="w-5 rounded-full">
                                                            <img src="{{ $task->assignee->avatar_url }}" alt="{{ $task->assignee->name }}" />
                                                        </div>
                                                    </div>
                                                @else
                                                    <span class="icon-[tabler--user] size-4"></span>
                                                @endif
                                                <span id="people-assignee-display">{{ $task->assignee?->name ?? 'Unassigned' }}</span>
                                                <span class="icon-[tabler--chevron-down] size-4 ml-auto transition-transform" id="people-assignee-chevron"></span>
                                            </button>
                                            <!-- Assignee Dropdown (hidden by default) -->
                                            <div id="people-assignee-dropdown" class="hidden mt-2 bg-base-100 rounded-lg border border-base-300 max-h-48 overflow-y-auto">
                                                <div class="p-1">
                                                    <button type="button" onclick="selectPerson('assignee', '', 'Unassigned')" class="w-full text-left px-3 py-2 text-sm rounded hover:bg-base-200 flex items-center gap-2">
                                                        <div class="avatar placeholder">
                                                            <div class="bg-base-300 text-base-content rounded-full w-6 h-6 flex items-center justify-center">
                                                                <span class="icon-[tabler--user-off] size-3"></span>
                                                            </div>
                                                        </div>
                                                        <span>Unassigned</span>
                                                    </button>
                                                    @foreach($users as $u)
                                                    <button type="button" onclick="selectPerson('assignee', '{{ $u->id }}', '{{ $u->name }}')" class="w-full text-left px-3 py-2 text-sm rounded hover:bg-base-200 flex items-center gap-2 {{ $task->assignee_id == $u->id ? 'bg-primary/10' : '' }}">
                                                        <div class="avatar">
                                                            <div class="w-6 rounded-full">
                                                                <img src="{{ $u->avatar_url }}" alt="{{ $u->name }}" />
                                                            </div>
                                                        </div>
                                                        <span>{{ $u->name }}</span>
                                                        @if($task->assignee_id == $u->id)
                                                        <span class="icon-[tabler--check] size-4 text-primary ml-auto"></span>
                                                        @endif
                                                    </button>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Creator - Click to show dropdown -->
                                        <div>
                                            <label class="text-xs font-medium text-base-content/60 mb-1 block">Created By</label>
                                            <button type="button" onclick="togglePeopleDropdown('creator')" class="btn btn-sm btn-outline w-full justify-start gap-2">
                                                <div class="avatar">
                                                    <div class="w-5 rounded-full">
                                                        <img src="{{ $task->creator->avatar_url }}" alt="{{ $task->creator->name }}" />
                                                    </div>
                                                </div>
                                                <span id="people-creator-display">{{ $task->creator->name }}</span>
                                                <span class="icon-[tabler--chevron-down] size-4 ml-auto transition-transform" id="people-creator-chevron"></span>
                                            </button>
                                            <!-- Creator Dropdown (hidden by default) -->
                                            <div id="people-creator-dropdown" class="hidden mt-2 bg-base-100 rounded-lg border border-base-300 max-h-48 overflow-y-auto">
                                                <div class="p-1">
                                                    @foreach($users as $u)
                                                    <button type="button" onclick="selectPerson('creator', '{{ $u->id }}', '{{ $u->name }}')" class="w-full text-left px-3 py-2 text-sm rounded hover:bg-base-200 flex items-center gap-2 {{ $task->created_by == $u->id ? 'bg-primary/10' : '' }}">
                                                        <div class="avatar">
                                                            <div class="w-6 rounded-full">
                                                                <img src="{{ $u->avatar_url }}" alt="{{ $u->name }}" />
                                                            </div>
                                                        </div>
                                                        <span>{{ $u->name }}</span>
                                                        @if($task->created_by == $u->id)
                                                        <span class="icon-[tabler--check] size-4 text-primary ml-auto"></span>
                                                        @endif
                                                    </button>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>

                                        <div class="flex gap-2 pt-2">
                                            <button type="button" class="btn btn-primary btn-xs" onclick="savePeople()">
                                                <span class="icon-[tabler--check] size-3.5"></span>
                                                Save
                                            </button>
                                            <button type="button" class="btn btn-ghost btn-xs" onclick="toggleEdit('people')">Cancel</button>
                                        </div>
                                    </div>
                                    @endif
                                </div>

                                <!-- Department (Inbox workspaces only) -->
                                @if($task->workspace->type->value === 'inbox')
                                <div class="py-4">
                                    <div class="flex items-center justify-between">
                                        <label class="text-sm font-medium text-base-content/70">Department</label>
                                        @if($task->canInlineEdit($user) && !$task->isClosed())
                                            <button type="button" class="w-7 h-7 bg-[#F8F8FB] rounded-md flex items-center justify-center" onclick="toggleEdit('department')" title="Edit department">
                                                <span class="icon-[tabler--pencil] size-3.5"></span>
                                            </button>
                                        @endif
                                    </div>
                                    <!-- Display Mode -->
                                    <div id="department-display" class="mt-2">
                                        @if($task->department)
                                            <span class="inline-flex items-center gap-1.5 px-2 py-1 rounded-md text-sm bg-info/10 text-info">
                                                <span class="icon-[tabler--building] size-4"></span>
                                                {{ $task->department->name }}
                                            </span>
                                        @else
                                            <span class="text-base-content/40 text-sm">Not assigned</span>
                                        @endif
                                    </div>
                                    <!-- Edit Mode -->
                                    @if($task->canInlineEdit($user) && !$task->isClosed())
                                    <form id="department-edit" action="{{ route('tasks.update-department', $task) }}" method="POST" class="hidden mt-2">
                                        @csrf
                                        @method('PATCH')
                                        <select name="department_id" class="select select-bordered select-sm w-full">
                                            <option value="">No Department</option>
                                            @foreach($departments as $department)
                                                <option value="{{ $department->id }}" {{ $task->department_id == $department->id ? 'selected' : '' }}>
                                                    {{ $department->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <div class="flex gap-2 mt-2">
                                            <button type="submit" class="btn btn-primary btn-xs">
                                                <span class="icon-[tabler--check] size-3.5"></span>
                                                Save
                                            </button>
                                            <button type="button" class="btn btn-ghost btn-xs" onclick="toggleEdit('department')">Cancel</button>
                                        </div>
                                    </form>
                                    @endif
                                </div>

                                @endif
                                <!-- Task Type(s) -->
                                <div class="py-4">
                                    <div class="flex items-center justify-between">
                                        <label class="text-sm font-normal text-[#525158]">Type</label>
                                        @if($task->canInlineEdit($user) && !$task->isClosed())
                                            <button type="button" class="w-7 h-7 bg-[#F8F8FB] rounded-md flex items-center justify-center" onclick="toggleEdit('type')" title="Edit type">
                                                <span class="icon-[tabler--pencil] size-3.5"></span>
                                            </button>
                                        @endif
                                    </div>
                                    <!-- Display Mode -->
                                    <div id="type-display" class="mt-2 flex flex-wrap">
                                        <div class="bg-[#EDECF0] py-1 px-2 rounded-md">
                                            @if($task->types && count($task->types) > 0)
                                                @foreach($task->types as $taskType)
                                                    <span class="flex items-center gap-1 text-xs text-[#525158]">
                                                        <span class="icon-[{{ $taskType->icon() }}]"></span>
                                                        {{ $taskType->label() }}
                                                    </span>
                                                @endforeach
                                            @else
                                                <span class="text-base-content/40 text-sm">Not set</span>
                                            @endif
                                        </div>
                                    </div>
                                    <!-- Edit Mode -->
                                    @if($task->canInlineEdit($user) && !$task->isClosed())
                                        <form id="type-edit" action="{{ route('tasks.update-type', $task) }}" method="POST" class="hidden mt-2">
                                            @csrf
                                            @method('PATCH')
                                            <div class="space-y-2 p-2 bg-base-200/50 rounded-lg">
                                                @foreach(\App\Modules\Task\Enums\TaskType::cases() as $type)
                                                    <label class="flex items-center gap-2 cursor-pointer hover:bg-base-200 p-1 rounded">
                                                        <input type="checkbox" name="type[]" value="{{ $type->value }}"
                                                            class="checkbox checkbox-sm checkbox-primary"
                                                            {{ $task->types && in_array($type, $task->types) ? 'checked' : '' }}>
                                                        <span class="icon-[{{ $type->icon() }}] size-4 text-base-content/70"></span>
                                                        <span class="text-sm">{{ $type->label() }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                            <div class="flex gap-2 mt-2">
                                                <button type="submit" class="btn btn-primary btn-xs">
                                                    <span class="icon-[tabler--check] size-3.5"></span>
                                                    Save
                                                </button>
                                                <button type="button" class="btn btn-ghost btn-xs" onclick="toggleEdit('type')">Cancel</button>
                                            </div>
                                        </form>
                                    @endif
                                </div>

                                <!-- Workspace -->
                                <div class="py-4">
                                    <label class="text-sm font-medium text-[#525158]">Workspace</label>
                                    <div class="mt-2">
                                        <a href="{{ route('workspace.show', $task->workspace) }}" class="inline-flex items-center gap-1.5 text-primary hover:text-primary-focus transition-colors">
                                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M3 10C3 8.34315 4.34315 7 6 7H18C19.6569 7 21 8.34315 21 10V18C21 19.6569 19.6569 21 18 21H6C4.34315 21 3 19.6569 3 18V10Z" fill="#3ba5ff"/>
                                                <path d="M7 12L7.75705 13.4384C8.58617 15.0137 10.2198 16 12 16C13.7802 16 15.4138 15.0137 16.243 13.4384L17 12" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                <path d="M8 7C8 5.34315 9.34315 4 11 4H13C14.6569 4 16 5.34315 16 7V8H8V7Z" stroke="#3ba5ff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                            {{ $task->workspace->name }}
                                        </a>
                                    </div>
                                </div>

                                <!-- Estimated Time -->
                                @if($task->estimated_time)
                                <div class="py-3 last:pb-0">
                                    <label class="text-sm font-medium text-base-content/70">Estimated Time</label>
                                    <div class="mt-2 inline-flex items-center gap-1.5 text-base-content">
                                        <span class="icon-[tabler--clock] size-4 text-base-content/60"></span>
                                        <span class="text-sm">{{ floor($task->estimated_time / 60) }}h {{ $task->estimated_time % 60 }}m</span>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    <!-- Divider -->
                    <div class="w-full h border border-[#EDECF0]"></div>
                    <!-- Tags -->
                        <div class="card-body">
                            <h2 class="card-title text-lg flex items-center gap-2">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M20.59 13.41L13.42 20.58C13.2343 20.766 13.0137 20.9135 12.7709 21.0141C12.5281 21.1148 12.2678 21.1666 12.005 21.1666C11.7422 21.1666 11.4819 21.1148 11.2391 21.0141C10.9963 20.9135 10.7757 20.766 10.59 20.58L3.17322 13.1719C2.42207 12.4216 2 11.4035 2 10.3418V4C2 2.89543 2.89543 2 4 2H10.3431C11.404 2 12.4214 2.42143 13.1716 3.17157L20.59 10.59C20.9625 10.9647 21.1716 11.4716 21.1716 12C21.1716 12.5284 20.9625 13.0353 20.59 13.41Z" stroke="#3ba5ff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M7.50729 6C8.33169 6 9 6.67157 9 7.5C9 8.32843 8.33169 9 7.50729 9H7.49271C6.66831 9 6 8.32843 6 7.5C6 6.67157 6.66831 6 7.49271 6H7.50729Z" fill="#3ba5ff"/>
                                </svg>
                                <span class=" text-2xl font-semibold leading-6">Tags</span>
                            </h2>

                            <div class="flex flex-wrap gap-2">
                                @forelse($task->tags as $tag)
                                    <div class="badge gap-1 mt-6" style="background-color: {{ $tag->color }}20; color: {{ $tag->color }}">
                                        {{ $tag->name }}
                                        @if($task->canEdit($user))
                                            <form action="{{ route('tasks.tags.detach', [$task, $tag]) }}" method="POST" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="hover:text-error">
                                                    <span class="icon-[tabler--x] size-3"></span>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                @empty
                                    <p class="text-base-content/60 text-sm mt-6">No tags</p>
                                @endforelse
                            </div>

    {{--                        @if($task->canEdit($user) && $tags->diff($task->tags)->isNotEmpty())
                                <form action="{{ route('tasks.tags.attach', $task) }}" method="POST" class="mt-3">
                                    @csrf
                                    <div class="flex gap-2">
                                        <select name="tag_id" class="select select-bordered select-sm flex-1">
                                            <option value="">Add tag...</option>
                                            @foreach($tags->diff($task->tags) as $tag)
                                                <option value="{{ $tag->id }}">{{ $tag->name }}</option>
                                            @endforeach
                                        </select>
                                        <button type="submit" class="btn btn-primary btn-sm">Add</button>
                                    </div>
                                </form>
                            @endif--}}
                        </div>
                    <!-- Divider -->
                    <div class="w-full h border border-[#EDECF0]"></div>
                    <!-- Watchers (hide for clients) -->
                    @if(!$isClient)
                        <div class="card-body">
                            <h2 class="card-title text-lg flex items-center gap-2 mb-6">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M11.9998 6C8.8373 6 6.77147 7.56534 4.49695 9.84056C3.32082 11.0171 3.32082 12.999 4.49225 14.1532C6.67955 16.3084 8.95266 18 11.9998 18C15.047 18 17.3201 16.3084 19.5074 14.1532C20.6835 12.9944 20.6835 11.0056 19.5074 9.84675C17.3201 7.69159 15.047 6 11.9998 6ZM3.08252 8.42656C5.4483 6.06005 8.00814 4 11.9998 4C15.8793 4 18.648 6.19224 20.9111 8.42211C22.8823 10.3643 22.8823 13.6357 20.9111 15.5779C18.648 17.8078 15.8793 20 11.9998 20C8.12042 20 5.35167 17.8078 3.08854 15.5779C1.11275 13.6311 1.13493 10.3747 3.08252 8.42656Z" fill="#3ba5ff"/>
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M12 10C10.8954 10 10 10.8954 10 12C10 13.1046 10.8954 14 12 14C13.1046 14 14 13.1046 14 12C14 10.8954 13.1046 10 12 10ZM8 12C8 9.79086 9.79086 8 12 8C14.2091 8 16 9.79086 16 12C16 14.2091 14.2091 16 12 16C9.79086 16 8 14.2091 8 12Z" fill="#3ba5ff"/>
                                </svg>
                                <span class="text-xl font-semibold text-[#17151C]">Watchers</span>
                                <span class="py-0.5 px-1.5 bg-[#EDECF0] rounded-md text-xs">{{ $task->watchers->count() }}</span>
                            </h2>

                            <div class="flex flex-wrap gap-2">
                                @forelse($task->watchers as $watcher)
                                    <div class="py-1 pl-1 pr-2 flex gap-2 bg-[#F8F8FB] rounded-2xl">
                                        <div class="avatar placeholder">
                                            <div class="bg-[#00a63e] w-6 flex! text-white items-center justify-center rounded-full">
                                                <span class="text-xs">{{ substr($watcher->name, 0, 1) }}</span>
                                            </div>
                                        </div>
                                        <span>{{ $watcher->name }}</span>
                                        @if($task->canEdit($user) || $watcher->id === $user->id)
                                            <form action="{{ route('tasks.watchers.destroy', [$task, $watcher->id]) }}" method="POST" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="hover:text-error">
                                                    <span class="icon-[tabler--x] size-3"></span>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                @empty
                                    <p class="text-base-content/60 text-sm">No watchers</p>
                                @endforelse
                            </div>

                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Image Lightbox Modal -->
<div id="image-lightbox" class="fixed inset-0 z-[9999] hidden">
    <!-- Backdrop -->
    <div class="absolute inset-0 bg-black/90 backdrop-blur-sm" onclick="closeLightbox()"></div>

    <!-- Content -->
    <div class="absolute inset-0 flex items-center justify-center p-4">
        <!-- Close button -->
        <button onclick="closeLightbox()" class="absolute top-4 right-4 btn btn-circle btn-ghost text-white hover:bg-white/20 z-10">
            <span class="icon-[tabler--x] size-6"></span>
        </button>

        <!-- Download button -->
        <button id="lightbox-download" onclick="downloadLightboxImage()" class="absolute top-4 right-20 btn btn-circle btn-ghost text-white hover:bg-white/20 z-10" title="Download image">
            <span class="icon-[tabler--download] size-6"></span>
        </button>

        <!-- Zoom controls -->
        <div class="absolute top-4 left-4 flex gap-2 z-10">
            <button onclick="zoomImage(-0.25)" class="btn btn-circle btn-ghost text-white hover:bg-white/20" title="Zoom out">
                <span class="icon-[tabler--zoom-out] size-6"></span>
            </button>
            <button onclick="resetZoom()" class="btn btn-ghost text-white hover:bg-white/20 px-3" title="Reset zoom">
                <span id="zoom-level">100%</span>
            </button>
            <button onclick="zoomImage(0.25)" class="btn btn-circle btn-ghost text-white hover:bg-white/20" title="Zoom in">
                <span class="icon-[tabler--zoom-in] size-6"></span>
            </button>
        </div>

        <!-- Image container -->
        <div id="lightbox-image-container" class="relative max-w-full max-h-full overflow-auto cursor-grab active:cursor-grabbing">
            <img id="lightbox-image" src="" alt="Preview" class="max-w-none transition-transform duration-200" style="transform-origin: center center;">
        </div>
    </div>

    <!-- Navigation hint -->
    <div class="absolute bottom-4 left-1/2 -translate-x-1/2 text-white/60 text-sm">
        <span class="icon-[tabler--mouse] size-4 inline-block mr-1"></span>
        Scroll to zoom • Drag to pan • Click outside or press ESC to close
    </div>
</div>

@push('scripts')
<style>
    /* Image lightbox styles */
    #image-lightbox img {
        user-select: none;
        -webkit-user-drag: none;
    }

    /* Make images in prose/description clickable */
    .prose img,
    .comment-content img,
    [data-zoomable-image] {
        cursor: zoom-in;
        transition: opacity 0.2s;
    }

    .prose img:hover,
    .comment-content img:hover,
    [data-zoomable-image]:hover {
        opacity: 0.9;
    }

    /* Zoom icon overlay on images */
    .image-zoom-wrapper {
        position: relative;
        display: inline-block;
    }

    .image-zoom-wrapper::after {
        content: '';
        position: absolute;
        top: 8px;
        right: 8px;
        width: 28px;
        height: 28px;
        background: rgba(0,0,0,0.6);
        border-radius: 50%;
        opacity: 0;
        transition: opacity 0.2s;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='white' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Ccircle cx='11' cy='11' r='8'/%3E%3Cline x1='21' y1='21' x2='16.65' y2='16.65'/%3E%3Cline x1='11' y1='8' x2='11' y2='14'/%3E%3Cline x1='8' y1='11' x2='14' y2='11'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: center;
        background-size: 16px;
        pointer-events: none;
    }

    .image-zoom-wrapper:hover::after {
        opacity: 1;
    }

    /* Edit button - hidden by default, show on hover */
    .edit-btn {
        opacity: 0 !important;
        transition: all 0.2s ease;
    }
    .group:hover .edit-btn {
        opacity: 0.7 !important;
    }
    .group:hover .edit-btn:hover {
        opacity: 1 !important;
        transform: scale(1.1);
    }

    /* Calendar styles */
    #calendar-days .btn {
        min-height: 28px;
        height: 28px;
        font-size: 0.75rem;
    }
    #calendar-days .btn.calendar-day {
        background-color: oklch(var(--b3));
        color: oklch(var(--bc));
        border: none;
    }
    #calendar-days .btn.calendar-day:hover {
        background-color: oklch(var(--p) / 0.2);
    }
    #calendar-days .btn.calendar-day.is-past {
        color: oklch(var(--bc) / 0.4);
        background-color: oklch(var(--b2));
    }
    #calendar-days .btn.calendar-day.is-today {
        border: 3px solid #f59e0b;
        background-color: #f59e0b;
        color: #fff;
        font-weight: 800;
        font-size: 0.85rem;
        box-shadow: 0 2px 8px rgba(245, 158, 11, 0.4);
    }
    #calendar-days .btn.calendar-day.is-selected {
        background-color: oklch(var(--su));
        color: oklch(var(--suc));
        font-weight: 700;
        border: 2px solid oklch(var(--su));
        box-shadow: 0 4px 12px oklch(var(--su) / 0.4);
    }
    #calendar-days .btn.calendar-day.is-today.is-selected {
        background-color: oklch(var(--su));
        color: oklch(var(--suc));
        border: 3px solid oklch(var(--su));
    }
</style>
<script>
// Toast notification function
function showToast(message, type = 'success') {
    // Remove any existing toasts
    const existingToasts = document.querySelectorAll('.toast-notification');
    existingToasts.forEach(t => t.remove());

    const toast = document.createElement('div');
    toast.className = `toast-notification fixed bottom-4 right-4 z-50 flex items-center gap-2 px-4 py-3 rounded-lg shadow-lg transition-all duration-300 transform translate-y-2 opacity-0`;

    if (type === 'success') {
        toast.classList.add('bg-success', 'text-success-content');
        toast.innerHTML = `<span class="icon-[tabler--check] size-5"></span><span>${message}</span>`;
    } else if (type === 'error') {
        toast.classList.add('bg-error', 'text-error-content');
        toast.innerHTML = `<span class="icon-[tabler--x] size-5"></span><span>${message}</span>`;
    } else {
        toast.classList.add('bg-base-300', 'text-base-content');
        toast.innerHTML = `<span class="icon-[tabler--info-circle] size-5"></span><span>${message}</span>`;
    }

    document.body.appendChild(toast);

    // Animate in
    requestAnimationFrame(() => {
        toast.classList.remove('translate-y-2', 'opacity-0');
    });

    // Auto-remove after 3 seconds
    setTimeout(() => {
        toast.classList.add('translate-y-2', 'opacity-0');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Comment/Private Note Filter Tab Switching
let currentFilterTab = 'all';

function switchFilterTab(type) {
    const filterTabs = document.querySelectorAll('[data-filter-tab]');
    const commentItems = document.querySelectorAll('.comment-item');
    const emptyMessage = document.querySelector('.empty-comments-message');
    const commentWrapper = document.getElementById('comment-editor-wrapper');
    const privateWrapper = document.getElementById('private-editor-wrapper');
    const isPrivateInput = document.getElementById('is_private_input');
    const submitBtnText = document.getElementById('submit-btn-text');
    const submitBtn = document.getElementById('comment-submit-btn');
    const titleText = document.getElementById('comments-title-text');

    currentFilterTab = type;

    // Update active tab
    filterTabs.forEach(tab => {
        if (tab.dataset.filterTab === type) {
            tab.classList.add('tab-active');
        } else {
            tab.classList.remove('tab-active');
        }
    });

    // Update section title based on filter
    // Update section title based on filter
    if (titleText) {
    const iconElement = titleText.closest('.card-title').querySelector('span[class*="icon-"]');
    const textElement = document.getElementById('comments-title-text');
    const badgeElement = titleText.closest('.card-title').querySelector('.badge');

    if (type === 'all') {
        // Update icon
        if (iconElement) {
            iconElement.className = 'icon-[tabler--message] size-5';
        }
        // Update text
        if (textElement) {
            textElement.textContent = 'All Comments';
        }
        // Update badge
        if (badgeElement) {
            badgeElement.id = '';
            badgeElement.textContent = {{ $visibleCommentsCount }};
        }
        
        
    } else if (type === 'public') {
        // Update icon
        if (iconElement) {
            iconElement.className = 'icon-[tabler--message-circle] size-5'; // Change to your public icon
        }
        // Update text
        if (textElement) {
            textElement.textContent = 'Comments';
        }
        // Update badge
        if (badgeElement) {
            badgeElement.id = 'public-count-badge';
            badgeElement.textContent = {{ $publicCommentsCount }};
        }
        
    } else if (type === 'private') {
        // Update icon
        if (iconElement) {
            iconElement.className = 'icon-[tabler--lock] size-5'; // Change to your private icon
        }
        // Update text
        if (textElement) {
            textElement.textContent = 'Private Comments';
        }
        // Update badge
        if (badgeElement) {
            badgeElement.id = 'private-count-badge';
            badgeElement.textContent = {{ $privateCommentsCount }};
        }
    }
}
    // Filter comments
    let visibleCount = 0;
    commentItems.forEach(item => {
        const isPrivate = item.dataset.isPrivate === '1';

        if (type === 'all') {
            // Show all comments
            item.classList.remove('hidden');
            visibleCount++;
        } else if (type === 'public') {
            // Show only public comments
            if (isPrivate) {
                item.classList.add('hidden');
            } else {
                item.classList.remove('hidden');
                visibleCount++;
            }
        } else if (type === 'private') {
            // Show only private comments
            if (isPrivate) {
                item.classList.remove('hidden');
                visibleCount++;
            } else {
                item.classList.add('hidden');
            }
        }
    });

    // Show/hide empty message
    if (emptyMessage) {
        if (visibleCount === 0) {
            emptyMessage.classList.remove('hidden');
            if (type === 'private') {
                emptyMessage.textContent = 'No private notes yet.';
            } else if (type === 'public') {
                emptyMessage.textContent = 'No comments yet. Be the first to comment!';
            } else {
                emptyMessage.textContent = 'No comments yet. Be the first to comment!';
            }
        } else {
            emptyMessage.classList.add('hidden');
        }
    }

    // Switch input editor based on filter tab
    if (commentWrapper && privateWrapper && isPrivateInput && submitBtnText && submitBtn) {
        if (type === 'private') {
            // Show private note editor
            commentWrapper.classList.add('hidden');
            privateWrapper.classList.remove('hidden');
            isPrivateInput.value = '1';
            submitBtnText.textContent = 'Add Private Note';
            submitBtn.classList.remove('btn-primary');
            submitBtn.classList.add('btn-warning');
        } else {
            // Show comment editor
            commentWrapper.classList.remove('hidden');
            privateWrapper.classList.add('hidden');
            isPrivateInput.value = '0';
            submitBtnText.textContent = 'Comment';
            submitBtn.classList.remove('btn-warning');
            submitBtn.classList.add('btn-primary');
        }
    }
}

// Legacy function for backward compatibility
function switchCommentTab(type) {
    switchFilterTab(type === 'comment' ? 'public' : type);
}

// Initialize comment filter on page load
document.addEventListener('DOMContentLoaded', function() {
    // Apply initial filter (show public comments by default)
    const filterTabs = document.querySelectorAll('[data-filter-tab]');
    if (filterTabs.length > 0) {
        switchFilterTab('all');
    }
});

// Prepare comment form submission - copy content from active editor to final input
function prepareCommentSubmit() {
    const isPrivate = document.getElementById('is_private_input').value === '1';
    const finalInput = document.getElementById('final_content_input');

    if (isPrivate) {
        // Get content from private editor
        const privateInput = document.getElementById('private-editor-input');
        if (privateInput) {
            finalInput.value = privateInput.value;
        }
    } else {
        // Get content from comment editor
        const commentInput = document.getElementById('comment-editor-input');
        if (commentInput) {
            finalInput.value = commentInput.value;
        }
    }

    // Validate that content is not empty
    if (!finalInput.value || finalInput.value.trim() === '' || finalInput.value === '<p><br></p>') {
        alert('Please enter a comment');
        return false;
    }

    return true;
}

function toggleEdit(field) {
    const displayEl = document.getElementById(field + '-display');
    const editEl = document.getElementById(field + '-edit');

    if (displayEl && editEl) {
        displayEl.classList.toggle('hidden');
        editEl.classList.toggle('hidden');

        // Focus the first input/select in the edit form
        if (!editEl.classList.contains('hidden')) {
            const input = editEl.querySelector('input, select');
            if (input) {
                setTimeout(() => input.focus(), 100);
            }

            // Initialize dates calendar when opening dates edit
            if (field === 'dates' && typeof initDatesCalendar === 'function') {
                initDatesCalendar();
            }
        }
    }
}

// Close edit mode when clicking outside
document.addEventListener('click', function(e) {
    // Don't close if clicking inside card-body, edit button, or calendar/edit elements
    if (!e.target.closest('.card-body') &&
        !e.target.closest('.edit-btn') &&
        !e.target.closest('#calendar-days') &&
        !e.target.closest('#dates-calendar-days') &&
        !e.target.closest('#dates-due-calendar') &&
        !e.target.closest('#dates-created-calendar') &&
        !e.target.closest('#due-date-edit') &&
        !e.target.closest('#dates-edit') &&
        !e.target.closest('#quick-stats-edit') &&
        !e.target.closest('#people-edit') &&
        !e.target.closest('#people-assignee-dropdown') &&
        !e.target.closest('#people-creator-dropdown')) {
        document.querySelectorAll('[id$="-edit"]:not(.hidden)').forEach(form => {
            const field = form.id.replace('-edit', '');
            const display = document.getElementById(field + '-display');
            if (display) {
                form.classList.add('hidden');
                display.classList.remove('hidden');
            }
        });
    }
});

// Close edit mode on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('[id$="-edit"]:not(.hidden)').forEach(form => {
            const field = form.id.replace('-edit', '');
            const display = document.getElementById(field + '-display');
            if (display) {
                form.classList.add('hidden');
                display.classList.remove('hidden');
            }
        });
    }
});

// Calendar functionality for due date
let currentDate = new Date();
let selectedDate = document.getElementById('due-date-input')?.value ? new Date(document.getElementById('due-date-input').value + 'T00:00:00') : null;

const months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

function renderCalendar() {
    const calendarDays = document.getElementById('calendar-days');
    const monthYearEl = document.getElementById('calendar-month-year');

    if (!calendarDays || !monthYearEl) return;

    // Set header
    monthYearEl.textContent = months[currentDate.getMonth()] + ' ' + currentDate.getFullYear();

    // Get first day of month and number of days
    const firstDay = new Date(currentDate.getFullYear(), currentDate.getMonth(), 1);
    const lastDay = new Date(currentDate.getFullYear(), currentDate.getMonth() + 1, 0);
    const startDay = firstDay.getDay();
    const totalDays = lastDay.getDate();

    // Get today for comparison
    const today = new Date();
    today.setHours(0, 0, 0, 0);

    // Build calendar HTML
    let html = '';

    // Empty cells for days before first day of month
    for (let i = 0; i < startDay; i++) {
        html += '<div class="p-1"></div>';
    }

    // Days of the month
    for (let day = 1; day <= totalDays; day++) {
        const date = new Date(currentDate.getFullYear(), currentDate.getMonth(), day);
        const dateStr = formatDate(date);
        const isToday = date.getTime() === today.getTime();
        const isSelected = selectedDate && date.getTime() === selectedDate.getTime();
        const isPast = date < today;

        let classes = 'btn btn-xs w-full aspect-square calendar-day';

        if (isSelected) {
            classes += ' is-selected';
        } else if (isToday) {
            classes += ' is-today';
        } else if (isPast) {
            classes += ' is-past';
        }

        html += `<button type="button" onclick="selectDate('${dateStr}', event)" class="${classes}">${day}</button>`;
    }

    calendarDays.innerHTML = html;
}

function formatDate(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}

function formatDisplayDate(date) {
    return months[date.getMonth()] + ' ' + date.getDate() + ', ' + date.getFullYear();
}

function selectDate(dateStr, event) {
    if (event) event.stopPropagation();
    selectedDate = new Date(dateStr + 'T00:00:00');
    document.getElementById('due-date-input').value = dateStr;
    document.getElementById('selected-date-display').textContent = formatDisplayDate(selectedDate);
    renderCalendar();
}

function changeMonth(delta) {
    currentDate.setMonth(currentDate.getMonth() + delta);
    renderCalendar();
}

function setQuickDate(type, event) {
    if (event) event.stopPropagation();
    const date = new Date();
    date.setHours(0, 0, 0, 0);

    switch(type) {
        case 'today':
            break;
        case 'tomorrow':
            date.setDate(date.getDate() + 1);
            break;
        case 'next-week':
            date.setDate(date.getDate() + 7);
            break;
    }

    currentDate = new Date(date);
    selectDate(formatDate(date), event);
}

function clearDate(event) {
    if (event) event.stopPropagation();
    selectedDate = null;
    document.getElementById('due-date-input').value = '';
    document.getElementById('selected-date-display').textContent = 'No Due Date';
    renderCalendar();
}

// Initialize calendar when edit mode opens
const originalToggleEdit = toggleEdit;
toggleEdit = function(field) {
    originalToggleEdit(field);

    if (field === 'due-date') {
        const editEl = document.getElementById('due-date-edit');
        if (editEl && !editEl.classList.contains('hidden')) {
            // Reset to current month or selected date's month
            if (selectedDate) {
                currentDate = new Date(selectedDate);
            } else {
                currentDate = new Date();
            }
            renderCalendar();
        }
    }

    // Focus search input when assignee edit opens
    if (field === 'assignee') {
        const editEl = document.getElementById('assignee-edit');
        if (editEl && !editEl.classList.contains('hidden')) {
            setTimeout(() => {
                const searchInput = document.getElementById('assignee-search');
                if (searchInput) {
                    searchInput.focus();
                    searchInput.select();
                }
            }, 100);
        }
    }
};

// Assignee search functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('assignee-search');
    const dropdown = document.getElementById('assignee-dropdown');
    const hiddenInput = document.getElementById('assignee-input');
    const options = document.querySelectorAll('.assignee-option');

    if (!searchInput || !dropdown) return;

    // Show dropdown on focus
    searchInput.addEventListener('focus', function() {
        dropdown.classList.remove('hidden');
        filterOptions('');
    });

    // Filter options on input
    searchInput.addEventListener('input', function() {
        const query = this.value.toLowerCase();
        filterOptions(query);
        dropdown.classList.remove('hidden');
    });

    // Filter function
    function filterOptions(query) {
        options.forEach(option => {
            const name = option.dataset.name.toLowerCase();
            if (name.includes(query) || query === '') {
                option.classList.remove('hidden');
            } else {
                option.classList.add('hidden');
            }
        });
    }

    // Select option on click
    options.forEach(option => {
        option.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();

            const id = this.dataset.id;
            const name = this.dataset.name;

            // Update hidden input
            hiddenInput.value = id;

            // Update search input display
            searchInput.value = name === 'Unassigned' ? '' : name;

            // Update selected styling
            options.forEach(opt => {
                opt.classList.remove('bg-primary/10');
                const checkIcon = opt.querySelector('.icon-\\[tabler--check\\]');
                if (checkIcon) checkIcon.remove();
            });

            this.classList.add('bg-primary/10');
            if (id) {
                const checkSpan = document.createElement('span');
                checkSpan.className = 'icon-[tabler--check] size-4 text-primary ml-auto';
                this.appendChild(checkSpan);
            }

            // Hide dropdown
            dropdown.classList.add('hidden');
        });
    });

    // Hide dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('#assignee-edit')) {
            dropdown.classList.add('hidden');
        }
    });

    // Keyboard navigation
    searchInput.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            dropdown.classList.add('hidden');
        }
    });
});

// ==================== IMAGE LIGHTBOX ====================
let currentZoom = 1;
let isDragging = false;
let startX, startY, scrollLeft, scrollTop;
let currentImageSrc = '';
let currentImageFilename = 'image.png';

// Initialize lightbox for all images in prose/comments
document.addEventListener('DOMContentLoaded', function() {
    initImageLightbox();
});

function initImageLightbox() {
    // Find all images in prose (description) and comments
    const images = document.querySelectorAll('.prose img, .comment-content img, [data-zoomable-image]');

    images.forEach(img => {
        // Skip if already initialized
        if (img.dataset.lightboxInitialized) return;
        img.dataset.lightboxInitialized = 'true';

        // Make image clickable
        img.style.cursor = 'zoom-in';
        img.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            openLightbox(this.src);
        });
    });
}

function openLightbox(imageSrc) {
    const lightbox = document.getElementById('image-lightbox');
    const lightboxImage = document.getElementById('lightbox-image');
    const container = document.getElementById('lightbox-image-container');

    if (!lightbox || !lightboxImage) return;

    // Store current image for download
    currentImageSrc = imageSrc;
    currentImageFilename = imageSrc.split('/').pop().split('?')[0] || 'image.png';

    // Set image source
    lightboxImage.src = imageSrc;

    // Reset zoom
    currentZoom = 1;
    updateZoom();

    // Show lightbox
    lightbox.classList.remove('hidden');
    document.body.style.overflow = 'hidden';

    // Setup drag to pan
    setupDragToPan(container);

    // Setup scroll to zoom
    container.addEventListener('wheel', handleWheelZoom, { passive: false });
}

function closeLightbox() {
    const lightbox = document.getElementById('image-lightbox');
    if (lightbox) {
        lightbox.classList.add('hidden');
        document.body.style.overflow = '';
    }
}

function downloadLightboxImage() {
    if (!currentImageSrc) return;

    const downloadBtn = document.getElementById('lightbox-download');
    const originalContent = downloadBtn.innerHTML;

    // Show loading state
    downloadBtn.innerHTML = '<span class="loading loading-spinner loading-sm"></span>';
    downloadBtn.disabled = true;

    // Use server-side proxy to download the image (bypasses CORS)
    const downloadUrl = '/images/download?url=' + encodeURIComponent(currentImageSrc);

    // Create a hidden iframe to trigger the download
    const iframe = document.createElement('iframe');
    iframe.style.display = 'none';
    iframe.src = downloadUrl;
    document.body.appendChild(iframe);

    // Restore button state after a short delay
    setTimeout(() => {
        downloadBtn.innerHTML = originalContent;
        downloadBtn.disabled = false;
        // Clean up iframe after download starts
        setTimeout(() => {
            document.body.removeChild(iframe);
        }, 5000);
    }, 1000);
}

function zoomImage(delta) {
    currentZoom = Math.max(0.25, Math.min(5, currentZoom + delta));
    updateZoom();
}

function resetZoom() {
    currentZoom = 1;
    updateZoom();
}

function updateZoom() {
    const lightboxImage = document.getElementById('lightbox-image');
    const zoomLevel = document.getElementById('zoom-level');

    if (lightboxImage) {
        lightboxImage.style.transform = `scale(${currentZoom})`;
    }

    if (zoomLevel) {
        zoomLevel.textContent = Math.round(currentZoom * 100) + '%';
    }
}

function handleWheelZoom(e) {
    e.preventDefault();
    const delta = e.deltaY > 0 ? -0.1 : 0.1;
    currentZoom = Math.max(0.25, Math.min(5, currentZoom + delta));
    updateZoom();
}

function setupDragToPan(container) {
    container.addEventListener('mousedown', (e) => {
        isDragging = true;
        container.style.cursor = 'grabbing';
        startX = e.pageX - container.offsetLeft;
        startY = e.pageY - container.offsetTop;
        scrollLeft = container.scrollLeft;
        scrollTop = container.scrollTop;
    });

    container.addEventListener('mouseleave', () => {
        isDragging = false;
        container.style.cursor = 'grab';
    });

    container.addEventListener('mouseup', () => {
        isDragging = false;
        container.style.cursor = 'grab';
    });

    container.addEventListener('mousemove', (e) => {
        if (!isDragging) return;
        e.preventDefault();
        const x = e.pageX - container.offsetLeft;
        const y = e.pageY - container.offsetTop;
        const walkX = (x - startX) * 2;
        const walkY = (y - startY) * 2;
        container.scrollLeft = scrollLeft - walkX;
        container.scrollTop = scrollTop - walkY;
    });
}

// Close lightbox on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeLightbox();
    }
});

// Re-initialize lightbox when new content is added (e.g., after AJAX)
if (typeof MutationObserver !== 'undefined') {
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.addedNodes.length) {
                initImageLightbox();
            }
        });
    });

    const proseContainers = document.querySelectorAll('.prose, .comment-content');
    proseContainers.forEach(container => {
        observer.observe(container, { childList: true, subtree: true });
    });
}

// Quick Stats (Status, Priority, Progress) - Combined Edit
const quickProgressSlider = document.getElementById('quick-progress-slider');
const quickProgressPercentage = document.getElementById('quick-progress-percentage');

if (quickProgressSlider) {
    quickProgressSlider.addEventListener('input', function() {
        quickProgressPercentage.textContent = this.value + '%';
    });
}

async function saveQuickStats() {
    const saveBtn = document.querySelector('#quick-stats-edit .btn-primary');
    const originalBtnText = saveBtn.innerHTML;
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<span class="loading loading-spinner loading-xs"></span> Saving...';

    const statusSelect = document.getElementById('quick-status-select');
    const statusId = statusSelect ? statusSelect.value : '';
    const prioritySelect = document.getElementById('quick-priority-select');
    const priorityValue = prioritySelect ? prioritySelect.value : '';
    const priorityType = prioritySelect ? prioritySelect.dataset.type : 'task';
    const progressSlider = document.getElementById('quick-progress-slider');
    const progress = progressSlider ? parseInt(progressSlider.value) : 0;

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

    try {
        const errors = [];
        let statusData = null;
        let priorityData = null;

        // Status - only update if a status is selected (required field)
        if (statusId && statusId !== '') {
            try {
                const statusResponse = await fetch('{{ route("tasks.update-status", $task) }}', {
                    method: 'PATCH',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ status_id: parseInt(statusId) })
                });
                if (!statusResponse.ok) {
                    const errorData = await statusResponse.json().catch(() => ({ message: 'Unknown error' }));
                    errors.push('Status: ' + (errorData.message || 'Update failed'));
                } else {
                    const data = await statusResponse.json();
                    statusData = data.status;
                }
            } catch (e) {
                errors.push('Status: Network error');
            }
        }

        // Priority
        try {
            if (priorityType === 'workspace') {
                const priorityResponse = await fetch('{{ route("tasks.update-workspace-priority", $task) }}', {
                    method: 'PATCH',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ workspace_priority_id: priorityValue ? parseInt(priorityValue) : null })
                });
                if (!priorityResponse.ok) {
                    const errorData = await priorityResponse.json().catch(() => ({ message: 'Unknown error' }));
                    errors.push('Priority: ' + (errorData.message || 'Update failed'));
                } else {
                    const data = await priorityResponse.json();
                    priorityData = data.priority;
                }
            } else {
                const priorityResponse = await fetch('{{ route("tasks.update-priority", $task) }}', {
                    method: 'PATCH',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ priority: priorityValue || null })
                });
                if (!priorityResponse.ok) {
                    const errorData = await priorityResponse.json().catch(() => ({ message: 'Unknown error' }));
                    errors.push('Priority: ' + (errorData.message || 'Update failed'));
                } else {
                    const data = await priorityResponse.json();
                    priorityData = data.priority;
                }
            }
        } catch (e) {
            errors.push('Priority: Network error');
        }

        // Progress
        try {
            const progressResponse = await fetch('{{ route("tasks.update-progress", $task) }}', {
                method: 'PATCH',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ progress: progress })
            });
            if (!progressResponse.ok) {
                const errorData = await progressResponse.json().catch(() => ({ message: 'Unknown error' }));
                errors.push('Progress: ' + (errorData.message || 'Update failed'));
            }
        } catch (e) {
            errors.push('Progress: Network error');
        }

        if (errors.length > 0) {
            console.error('Save errors:', errors);
            alert('Some changes failed to save: ' + errors.join(', '));
            saveBtn.disabled = false;
            saveBtn.innerHTML = originalBtnText;
            return;
        }

        // Update the display without page reload
        updateQuickStatsDisplay(statusData, priorityData, priorityType, progress);
        toggleEdit('quick-stats');
        showToast('Changes saved successfully');

        saveBtn.disabled = false;
        saveBtn.innerHTML = originalBtnText;

    } catch (error) {
        console.error('Error saving quick stats:', error);
        alert('Error saving changes: ' + error.message);
        saveBtn.disabled = false;
        saveBtn.innerHTML = originalBtnText;
    }
}

function updateQuickStatsDisplay(statusData, priorityData, priorityType, progress) {
    const displayEl = document.getElementById('quick-stats-display');
    if (!displayEl) return;

    // Build status badge HTML
    let statusHtml = '';
    if (statusData) {
        statusHtml = `<span class="py-1 px-2 rounded-md text-xs" style="background-color: ${statusData.background_color}20; color: ${statusData.background_color}">${statusData.name}</span>`;
    } else {
        statusHtml = '<span class="py-1 px-2 rounded-md text-xs badge-ghost">No Status</span>';
    }

    // Build priority badge HTML
    let priorityHtml = '';
    if (priorityData) {
        if (priorityType === 'workspace') {
            priorityHtml = `<span class="py-1 px-2 rounded-md text-xs" style="background-color: ${priorityData.color}15; color: ${priorityData.color}">
                <span class="icon-[tabler--flag] size-3 mr-0.5"></span>
                ${priorityData.name}
            </span>`;
        } else {
            priorityHtml = `<span class="py-1 px-2 rounded-md text-xs" style="background-color: ${priorityData.color}15; color: ${priorityData.color}">
                <span class=" size-3 mr-0.5"></span>
                ${priorityData.label}
            </span>`;
        }
    } else {
        priorityHtml = '<span class="py-1 px-2 rounded-md text-xs badge-ghost">No Priority</span>';
    }

    // Build progress badge HTML
    const progressClass = progress === 100 ? 'bg-success' : 'bg-primary';
    const progressHtml = `<span class="py-1 px-2 rounded-md text-xs bg-primary text-white ${progressClass} badge-outline">${progress}%</span>`;

    displayEl.innerHTML = `
        <div class="flex items-center gap-2 flex-wrap">
            ${statusHtml}
            
            ${priorityHtml}
            
            ${progressHtml}
        </div>
    `;
}

// People (Assignee / Creator combined section)
function togglePeopleDropdown(type) {
    const dropdown = document.getElementById(`people-${type}-dropdown`);
    const chevron = document.getElementById(`people-${type}-chevron`);

    if (dropdown) {
        const isHidden = dropdown.classList.contains('hidden');
        // Close all other dropdowns first
        document.querySelectorAll('[id^="people-"][id$="-dropdown"]').forEach(d => {
            if (d.id !== `people-${type}-dropdown`) {
                d.classList.add('hidden');
            }
        });
        document.querySelectorAll('[id^="people-"][id$="-chevron"]').forEach(c => {
            if (c.id !== `people-${type}-chevron`) {
                c.style.transform = '';
            }
        });

        dropdown.classList.toggle('hidden');

        if (chevron) {
            chevron.style.transform = isHidden ? 'rotate(180deg)' : '';
        }
    }
}

function selectPerson(type, id, name) {
    document.getElementById(`people-${type}-input`).value = id;
    document.getElementById(`people-${type}-display`).textContent = name;
    togglePeopleDropdown(type);
}

async function savePeople() {
    const saveBtn = document.querySelector('#people-edit .btn-primary');
    const originalBtnText = saveBtn.innerHTML;
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<span class="loading loading-spinner loading-xs"></span> Saving...';

    const assigneeId = document.getElementById('people-assignee-input')?.value || null;
    const creatorId = document.getElementById('people-creator-input')?.value || null;
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

    try {
        const errors = [];
        let assigneeData = null;
        let creatorData = null;

        // Update assignee
        try {
            const assigneeResponse = await fetch('{{ route("tasks.update-assignee", $task) }}', {
                method: 'PATCH',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ assignee_id: assigneeId || null })
            });
            if (!assigneeResponse.ok) {
                errors.push('Assignee update failed');
            } else {
                const data = await assigneeResponse.json();
                assigneeData = data.assignee;
            }
        } catch (e) {
            errors.push('Assignee: Network error');
        }

        // Update creator
        try {
            const creatorResponse = await fetch('{{ route("tasks.update-creator", $task) }}', {
                method: 'PATCH',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ created_by: creatorId })
            });
            if (!creatorResponse.ok) {
                errors.push('Creator update failed');
            } else {
                const data = await creatorResponse.json();
                creatorData = data.creator;
            }
        } catch (e) {
            errors.push('Creator: Network error');
        }

        if (errors.length > 0) {
            console.error('Save errors:', errors);
            alert('Some changes failed to save: ' + errors.join(', '));
            saveBtn.disabled = false;
            saveBtn.innerHTML = originalBtnText;
            return;
        }

        // Update the display without page reload
        updatePeopleDisplay(assigneeData, creatorData);
        toggleEdit('people');
        showToast('Changes saved successfully');

        saveBtn.disabled = false;
        saveBtn.innerHTML = originalBtnText;

    } catch (error) {
        console.error('Error saving people:', error);
        alert('Error saving: ' + error.message);
        saveBtn.disabled = false;
        saveBtn.innerHTML = originalBtnText;
    }
}

function updatePeopleDisplay(assigneeData, creatorData) {
    const displayEl = document.getElementById('people-display');
    if (!displayEl) return;

    // Build assignee badge HTML
    let assigneeHtml = '';
    if (assigneeData) {
        assigneeHtml = `<div class="badge badge-sm gap-1.5 py-2.5">
            <div class="avatar">
                <div class="w-4 rounded-full">
                    <img src="${assigneeData.avatar_url}" alt="${assigneeData.name}" />
                </div>
            </div>
            ${assigneeData.name}
        </div>`;
    } else {
        assigneeHtml = `<span class="badge badge-sm badge-ghost gap-1">
            <span class="icon-[tabler--user-off] size-3"></span>
            Unassigned
        </span>`;
    }

    // Build creator badge HTML
    let creatorHtml = '';
    if (creatorData) {
        creatorHtml = `<div class="badge badge-sm badge-outline gap-1.5 py-2.5">
            <div class="avatar">
                <div class="w-4 rounded-full">
                    <img src="${creatorData.avatar_url}" alt="${creatorData.name}" />
                </div>
            </div>
            ${creatorData.name}
        </div>`;
    }

    displayEl.innerHTML = `
        <div class="flex items-center gap-2 flex-wrap">
            ${assigneeHtml}
            <span class="text-base-content/30">•</span>
            ${creatorHtml}
        </div>
    `;
}

// Dates Calendar (Due Date / Created Date combined section)
let datesCalendarState = {
    due: {
        currentMonth: new Date(),
        selectedDate: @json($task->due_date?->format('Y-m-d'))
    },
    created: {
        currentMonth: new Date(),
        selectedDate: @json($task->created_at->format('Y-m-d'))
    }
};

function initDatesCalendar() {
    // Initialize due date calendar month
    if (datesCalendarState.due.selectedDate) {
        datesCalendarState.due.currentMonth = new Date(datesCalendarState.due.selectedDate + 'T00:00:00');
    }
    // Initialize created date calendar month
    if (datesCalendarState.created.selectedDate) {
        datesCalendarState.created.currentMonth = new Date(datesCalendarState.created.selectedDate + 'T00:00:00');
    }
}

function toggleDatesCalendar(type) {
    const calendar = document.getElementById(`dates-${type}-calendar`);
    const chevron = document.getElementById(`dates-${type}-chevron`);

    if (calendar) {
        const isHidden = calendar.classList.contains('hidden');
        calendar.classList.toggle('hidden');

        if (chevron) {
            chevron.style.transform = isHidden ? 'rotate(180deg)' : '';
        }

        // Render calendar when showing
        if (isHidden) {
            renderDatesCalendar(type);
        }
    }
}

function renderDatesCalendar(type) {
    const calendarDays = document.getElementById(`dates-${type}-days`);
    const monthYearDisplay = document.getElementById(`dates-${type}-month-year`);
    const state = datesCalendarState[type];

    if (!calendarDays || !monthYearDisplay || !state) return;

    const year = state.currentMonth.getFullYear();
    const month = state.currentMonth.getMonth();

    monthYearDisplay.textContent = new Date(year, month).toLocaleDateString('en-US', { month: 'long', year: 'numeric' });

    const firstDay = new Date(year, month, 1).getDay();
    const daysInMonth = new Date(year, month + 1, 0).getDate();
    const today = new Date();
    today.setHours(0, 0, 0, 0);

    let html = '';

    // Empty cells for days before the first day of the month
    for (let i = 0; i < firstDay; i++) {
        html += '<div></div>';
    }

    // Days of the month
    for (let day = 1; day <= daysInMonth; day++) {
        const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
        const date = new Date(year, month, day);
        const isToday = date.getTime() === today.getTime();
        const isSelected = dateStr === state.selectedDate;

        let classes = 'btn btn-ghost btn-xs h-8 w-full';
        if (isSelected) {
            classes = 'btn btn-primary btn-xs h-8 w-full';
        } else if (isToday) {
            classes = 'btn btn-outline btn-primary btn-xs h-8 w-full';
        }

        html += `<button type="button" onclick="selectDatesDate('${type}', '${dateStr}', event)" class="${classes}">${day}</button>`;
    }

    calendarDays.innerHTML = html;
}

function changeDatesMonth(type, delta) {
    datesCalendarState[type].currentMonth.setMonth(datesCalendarState[type].currentMonth.getMonth() + delta);
    renderDatesCalendar(type);
}

function selectDatesDate(type, dateStr, event) {
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }

    datesCalendarState[type].selectedDate = dateStr;

    const inputId = type === 'due' ? 'dates-due-date-input' : 'dates-created-date-input';
    const displayId = `dates-${type}-display`;

    document.getElementById(inputId).value = dateStr;

    const date = new Date(dateStr + 'T00:00:00');
    document.getElementById(displayId).textContent = date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });

    renderDatesCalendar(type);

    // Close only the calendar, not the edit panel
    const calendar = document.getElementById(`dates-${type}-calendar`);
    const chevron = document.getElementById(`dates-${type}-chevron`);
    if (calendar) calendar.classList.add('hidden');
    if (chevron) chevron.style.transform = '';
}

function setDatesQuickDate(type, quickType, event) {
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }

    const today = new Date();
    let targetDate;

    switch (quickType) {
        case 'today':
            targetDate = today;
            break;
        case 'tomorrow':
            targetDate = new Date(today);
            targetDate.setDate(targetDate.getDate() + 1);
            break;
        case 'yesterday':
            targetDate = new Date(today);
            targetDate.setDate(targetDate.getDate() - 1);
            break;
        case 'next-week':
            targetDate = new Date(today);
            targetDate.setDate(targetDate.getDate() + 7);
            break;
        case 'last-week':
            targetDate = new Date(today);
            targetDate.setDate(targetDate.getDate() - 7);
            break;
    }

    const dateStr = targetDate.toISOString().split('T')[0];
    datesCalendarState[type].currentMonth = new Date(targetDate);
    selectDatesDate(type, dateStr, event);
}

function clearDatesField(type, event) {
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }

    if (type === 'due') {
        datesCalendarState.due.selectedDate = null;
        document.getElementById('dates-due-date-input').value = '';
        document.getElementById('dates-due-display').textContent = 'No Due Date';
        renderDatesCalendar('due');
        // Close only the calendar
        const calendar = document.getElementById('dates-due-calendar');
        const chevron = document.getElementById('dates-due-chevron');
        if (calendar) calendar.classList.add('hidden');
        if (chevron) chevron.style.transform = '';
    }
}

async function saveDates() {
    const saveBtn = document.querySelector('#dates-edit .btn-primary');
    const originalBtnText = saveBtn.innerHTML;
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<span class="loading loading-spinner loading-xs"></span> Saving...';

    const dueDate = document.getElementById('dates-due-date-input')?.value || null;
    const createdDate = document.getElementById('dates-created-date-input')?.value || null;

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

    try {
        const errors = [];
        let dueDateData = null;
        let createdDateData = null;

        // Update due date
        try {
            const dueDateResponse = await fetch('{{ route("tasks.update-due-date", $task) }}', {
                method: 'PATCH',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ due_date: dueDate })
            });
            if (!dueDateResponse.ok) {
                errors.push('Due date update failed');
            } else {
                const data = await dueDateResponse.json();
                dueDateData = data.due_date;
            }
        } catch (e) {
            errors.push('Due date: Network error');
        }

        // Update created date
        try {
            const createdDateResponse = await fetch('{{ route("tasks.update-created-date", $task) }}', {
                method: 'PATCH',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ created_at: createdDate })
            });
            if (!createdDateResponse.ok) {
                errors.push('Created date update failed');
            } else {
                const data = await createdDateResponse.json();
                createdDateData = data.created_at;
            }
        } catch (e) {
            errors.push('Created date: Network error');
        }

        if (errors.length > 0) {
            console.error('Save errors:', errors);
            alert('Some changes failed to save: ' + errors.join(', '));
            saveBtn.disabled = false;
            saveBtn.innerHTML = originalBtnText;
            return;
        }

        // Update the display without page reload
        updateDatesDisplay(createdDateData, dueDateData);
        toggleEdit('dates');
        showToast('Changes saved successfully');

        saveBtn.disabled = false;
        saveBtn.innerHTML = originalBtnText;

    } catch (error) {
        console.error('Error saving dates:', error);
        alert('Error saving dates: ' + error.message);
        saveBtn.disabled = false;
        saveBtn.innerHTML = originalBtnText;
    }
}

function updateDatesDisplay(createdDateData, dueDateData) {
    const displayEl = document.getElementById('dates-display');
    if (!displayEl) return;

    // Build created date badge HTML
    let createdHtml = '';
    if (createdDateData) {
        createdHtml = `<span class="badge badge-sm badge-outline gap-1">
            <span class="icon-[tabler--calendar-plus] size-3"></span>
            ${createdDateData.formatted}
        </span>`;
    }

    // Build due date badge HTML
    let dueHtml = '';
    if (dueDateData) {
        const badgeClass = dueDateData.is_overdue ? 'badge-error' : 'badge-warning';
        const overdueText = dueDateData.is_overdue ? ' (Overdue)' : '';
        dueHtml = `<span class="badge badge-sm ${badgeClass} gap-1">
            <span class="icon-[tabler--calendar-due] size-3"></span>
            ${dueDateData.formatted}${overdueText}
        </span>`;
    } else {
        dueHtml = `<span class="badge badge-sm badge-ghost gap-1">
            <span class="icon-[tabler--calendar-due] size-3"></span>
            No Due Date
        </span>`;
    }

    displayEl.innerHTML = `
        <div class="flex items-center gap-2 flex-wrap">
            ${createdHtml}
            <span class="text-base-content/30">•</span>
            ${dueHtml}
        </div>
    `;
}

// ==================== ATTACHMENT AJAX UPLOAD ====================
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('attachment-files');
    const uploadBtn = document.getElementById('attachment-upload-btn');
    const uploadIcon = document.getElementById('upload-icon');
    const uploadSpinner = document.getElementById('upload-spinner');
    const uploadBtnText = document.getElementById('upload-btn-text');

    if (!fileInput) return;

    // Auto-upload when files are selected
    fileInput.addEventListener('change', async function() {
        const files = this.files;
        if (!files || files.length === 0) {
            return;
        }

        const uploadUrl = fileInput.dataset.uploadUrl;
        if (!uploadUrl) {
            showToast('Upload URL not configured', 'error');
            return;
        }

        // Show loading state
        uploadBtn.disabled = true;
        uploadIcon.classList.add('hidden');
        uploadSpinner.classList.remove('hidden');
        uploadBtnText.textContent = 'Uploading...';

        const formData = new FormData();
        for (let i = 0; i < files.length; i++) {
            formData.append('files[]', files[i]);
        }

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
        formData.append('_token', csrfToken);

        try {
            const response = await fetch(uploadUrl, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            });

            const data = await response.json();

            if (response.ok && data.success) {
                // Add new attachments to the list
                addAttachmentsToList(data.attachments);

                // Update attachment count
                updateAttachmentCount(data.total_count);

                // Show success message
                showToast(data.message, 'success');

                // Show warning if any files failed
                if (data.warning) {
                    setTimeout(() => showToast(data.warning, 'warning'), 1500);
                }

                // Clear file input
                fileInput.value = '';
            } else {
                showToast(data.message || 'Upload failed', 'error');
            }
        } catch (error) {
            console.error('Upload error:', error);
            showToast('An error occurred while uploading', 'error');
        } finally {
            // Reset button state
            uploadBtn.disabled = false;
            uploadIcon.classList.remove('hidden');
            uploadSpinner.classList.add('hidden');
            uploadBtnText.textContent = 'Upload';
        }
    });
});

function addAttachmentsToList(attachments) {
    const attachmentsList = document.getElementById('attachments-list');
    if (!attachmentsList) return;

    // Show the list if it was hidden
    attachmentsList.classList.remove('hidden');

    attachments.forEach(attachment => {
        const attachmentHtml = createAttachmentElement(attachment);
        attachmentsList.insertAdjacentHTML('beforeend', attachmentHtml);
    });
}

function createAttachmentElement(attachment) {
    const deleteButton = attachment.can_delete ? `
        <form action="${attachment.delete_url}" method="POST" class="inline">
            <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]')?.content || ''}">
            <input type="hidden" name="_method" value="DELETE">
            <button type="submit" class="w-7 h-7 flex items-center justify-center rounded-md border border-[#B8B7BB] hover:bg-red-50 transition-colors"
                    onclick="return confirm('Delete this attachment?')">
                <span class="icon-[tabler--trash] size-4"></span>
            </button>
        </form>
    ` : '';

    return `
        <div class="flex items-center gap-3 p-4 rounded-lg group" data-attachment-id="${attachment.id}">
            <div class="flex-1 min-w-0">
                <p class="text-[#525158] text-sm font-medium truncate">${escapeHtml(attachment.original_name)}</p>
            </div>
            <div class="flex gap-2">
                <a href="${attachment.download_url}" class="w-7 h-7 flex items-center justify-center rounded-md border border-[#B8B7BB] hover:bg-gray-50 transition-colors">
                    <span class="icon-[tabler--download] size-4"></span>
                </a>
                ${deleteButton}
            </div>
        </div>
    `;
}

function updateAttachmentCount(count) {
    const countBadge = document.getElementById('attachment-count');
    if (countBadge) {
        countBadge.textContent = count;
    }
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

</script>
@endpush

<!-- On Hold Modal -->
@include('task::partials.on-hold-modal')

<!-- Subtask Drawer (only for parent tasks) -->
@if(!$task->parentTask)
@include('task::partials.subtask-drawer', ['task' => $task, 'users' => $users])
@endif

@endsection
