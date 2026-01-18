@extends('layouts.app')

@php
    $user = auth()->user();
    // isClient = true only for inbox workspace guests (not regular workspace guests)
    // A client is a guest user viewing a ticket in an inbox workspace where they are a guest
    $isInboxWorkspace = $task->workspace->type->value === 'inbox';
    $isGuestUser = $user->is_guest || $user->role === \App\Models\User::ROLE_GUEST;
    $isWorkspaceGuest = $task->workspace->guests()->where('users.id', $user->id)->exists();
    $isClient = $isInboxWorkspace && $isGuestUser && $isWorkspaceGuest;
    // Define status variables globally
    $currentStatus = $task->status ? $task->status->name : 'No Status';
    $statusBadgeColors = [
        'no status' => 'bg-[#3F404D] text-white',
        'ready for review' => 'bg-[#a855f720] text-[#a855f7]',
        'in progress' => 'bg-[#FDF3E3] text-[#F59E0C]',
        'closed' => 'bg-[#ECEEF0] text-[#64748B]',
        'open' => 'bg-[#E6F0FE] text-[#629BF8]',
    ];
    $statusNameLower = strtolower($currentStatus);
    $badgeClass = $statusBadgeColors[$statusNameLower] ?? 'bg-[#a855f720] text-[#a855f7] border border-base-300';

@endphp

@section('content')
<div class="p-4 md:p-6 pt-0!">
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
                <div class="mb-0 py-4 pb-6">
                <!-- Header -->
                <div class="flex justify-between items-end">
                    <div class="flex items-center gap-2 text-sm text-base-content/60 mb-2">
                        <a href="{{ route('dashboard') }}" class="hover:text-primary text-[#525158]">Dashboard</a>
                        <span class="icon-[tabler--chevron-right] size-4"></span>
                        <a href="{{ route('tasks.index') }}" class="hover:text-primary text-[#525158]">Tasks</a>
                        <span class="icon-[tabler--chevron-right] size-4 text-[#B8B7BB]"></span>
                        <span>{{ $task->task_number }}</span>
                    </div>

                    <div class="flex flex-col md:flex-row md:items-start justify-between gap-4">
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                @if($task->is_private)
                                    <span class="badge badge-warning gap-1" title="Only creator, assignee, and watchers can see this task">
                                        <span class="icon-[tabler--lock] size-3.5"></span>
                                        Private
                                    </span>
                                @endif
                            </div>
                            <p class="text-base-content/60 mt-1">
                                <!-- Created by  -->
                                {{--   {{ $task->creator->name }} on {{ $task->created_at->format('M d, Y') }} --}}
                            </p> 
                        </div>

                        <div class="flex flex-wrap gap-2">
                            @if($isClient)
                                <!-- Client: Simple back button -->
                                <a href="{{ route('dashboard') }}" class="btn btn-ghost btn-sm">
                                    <span class="icon-[tabler--arrow-left] size-4"></span>
                                    Back to My Tickets
                                </a>
                            @else
                                <!-- Back to Workspace Tasks -->
                                <a href="{{ route('workspace.show', ['workspace' => $task->workspace, 'tab' => 'tasks']) }}" class="border-[#B8B7BB] border rounded-md p-2 btn-sm bg-white">
                                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M17.5 10C17.5 10.4601 17.1271 10.8338 16.667 10.834L5.3457 10.834L8.92285 14.4111C9.24816 14.7365 9.24816 15.2644 8.92285 15.5898C8.59746 15.9152 8.06956 15.9152 7.74414 15.5898L2.74414 10.5898C2.41871 10.2644 2.41871 9.73655 2.74414 9.41113L7.74414 4.41113C8.06956 4.08578 8.59746 4.08573 8.92285 4.41113C9.24815 4.73655 9.24816 5.26442 8.92285 5.58984L5.3457 9.16699L16.667 9.16699C17.1269 9.16717 17.4997 9.54009 17.5 10Z" fill="#17151C"/>
                                    </svg>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
                </div>
                <!-- Description -->
                <div class="bg-base-100 shadow rounded-xl">
                    <div class="card-body">
                        <!-- Description area (line ~14) - Simple version -->
                        <div class="description-content mb-4">
                            <span id="status-display-top" class="px-2 py-1 rounded-md text-sm font-semibold {{ $badgeClass }}">{{ $currentStatus }}</span>
                        </div>
                        <h1 class="text-2xl mb-2 font-bold text-base-content {{ $task->isClosed() ? 'line-through opacity-60' : '' }}">
                            {{ $task->title }}
                        </h1>
                        @if($task->description)
                            <div class="prose task-description prose-sm max-w-none text-[#525158] text-base">
                                {!! $task->description !!}
                            </div>
                        @else
                            <p class="text-base-content/60 italic">No description provided.</p>
                        @endif

                        <!-- Subtasks (only show for parent tasks, not for subtasks) -->
                        @if(!$task->parentTask)
                        <div id="subtasks-section" class="">
                            <div id="subtasks-card">
                                <!-- Subtasks Table -->
                                <div class="overflow-x-auto sub-task-tables">
                                    <table class="table w-full">
                                        <thead>
                                            <tr class="bg-[#F8F8FB] border-0 rounded-md py-[7px] px-[12px]">
                                                <th class="text-left text-sm font-medium capitalize text-[#525158] bg-transparent">Sub-task</th>
                                                <th class="text-right text-sm font-medium capitalize text-[#525158] bg-transparent">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody id="subtasks-list">
                                            @forelse($task->subtasks as $subtask)
                                                <tr class="border-b border-base-200 hover:bg-base-100 transition-colors">
                                                    <td class="py-3">
                                                        <div class="flex items-center gap-3">
                                                            <!-- Status Badge -->
                                                            @if($subtask->status)
                                                                @php
                                                                    $subtaskStatusName = strtolower($subtask->status->name);
                                                                    $subtaskBadgeClass = $statusBadgeColors[$subtaskStatusName] ?? 'bg-base-200 text-base-content';
                                                                @endphp
                                                                <span class="px-2 py-1 rounded-md text-xs font-semibold {{ $subtaskBadgeClass }} whitespace-nowrap">
                                                                    {{ $subtask->status->name }}
                                                                </span>
                                                            @else
                                                                <span class="px-2 py-1 rounded-md text-xs font-semibold bg-base-200 text-base-content whitespace-nowrap">
                                                                    No Status
                                                                </span>
                                                            @endif
                                                            
                                                            <!-- Subtask Title -->
                                                            <span class="text-sm text-base-content {{ $subtask->isClosed() ? 'line-through opacity-60' : '' }}">
                                                                {{ $subtask->title }}
                                                            </span>
                                                        </div>
                                                    </td>
                                                    <td class="py-3 text-right">
                                                        <!-- Eye Icon to View Subtask -->
                                                        <a href="{{ route('tasks.show', $subtask) }}" 
                                                        class="btn btn-sm bg-white border border-[#B8B7BB] p-1.5 noShadow-btn" 
                                                        title="View subtask">
                                                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                <path fill-rule="evenodd" clip-rule="evenodd" d="M7.9999 4.00033C5.89153 4.00033 4.51432 5.04389 2.99797 6.5607C2.21388 7.34503 2.21388 8.66634 2.99484 9.43582C4.45303 10.8726 5.96844 12.0003 7.9999 12.0003C10.0314 12.0003 11.5468 10.8726 13.005 9.43582C13.789 8.66329 13.789 7.33737 13.005 6.56483C11.5468 5.12805 10.0314 4.00033 7.9999 4.00033ZM2.05501 5.61803C3.6322 4.04036 5.33876 2.66699 7.9999 2.66699C10.5862 2.66699 12.432 4.12849 13.9408 5.61507C15.2549 6.90985 15.2549 9.0908 13.9408 10.3856C12.432 11.8722 10.5862 13.3337 7.9999 13.3337C5.41361 13.3337 3.56778 11.8722 2.05903 10.3856C0.741834 9.08774 0.756622 6.91682 2.05501 5.61803Z" fill="#525158"/>
                                                                <path fill-rule="evenodd" clip-rule="evenodd" d="M7.99967 6.66634C7.26329 6.66634 6.66634 7.26329 6.66634 7.99967C6.66634 8.73605 7.26329 9.33301 7.99967 9.33301C8.73605 9.33301 9.33301 8.73605 9.33301 7.99967C9.33301 7.26329 8.73605 6.66634 7.99967 6.66634ZM5.33301 7.99967C5.33301 6.52692 6.52692 5.33301 7.99967 5.33301C9.47243 5.33301 10.6663 6.52692 10.6663 7.99967C10.6663 9.47243 9.47243 10.6663 7.99967 10.6663C6.52692 10.6663 5.33301 9.47243 5.33301 7.99967Z" fill="#525158"/>
                                                            </svg>
                                                        </a>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="2" class="py-4 text-center">
                                                        <p id="subtasks-empty-message" class="text-base-content/60 text-sm">No subtasks yet</p>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                                
                                <!-- Add Subtask Button -->
                                <div class="mt-4">
                                    <button type="button" onclick="openSubtaskDrawer()" class="btn btn-sm btn-ghost">
                                        <span class="icon-[tabler--plus] size-4"></span>
                                        Add Subtask
                                    </button>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
                <!-- Comments, Private Notes & Attachments -->
                <div class="card bg-base-100 shadow">
                    <div>
                        @php
                            $visibleCommentsCount = $isClient
                                ? $task->comments->where('is_private', false)->count()
                                : $task->comments->count();
                        @endphp

                        <!-- Tabs Section -->
                        @if(!$isClient)
                        <div class="mb-4">
                            <!-- Tab Buttons -->
                            <div class="tabs tabs-boxed task-tabs-comments-area mb-3 rounded-md inline-flex">
                                <button type="button" class="tab tab-active rounded-tl-md bg-[#E6F0FE] !text-[#3ca4fc] border-b-2 border-transparent" data-main-tab="comment" data-tab-color="comment" onclick="switchMainTab('comment')">
                                    <span class="icon-[tabler--message] size-4 mr-1"></span>
                                    Comments
                                    <span class="badge badge-sm ml-1">{{ $visibleCommentsCount }}</span>
                                </button>
                                <button type="button" class="tab  border-b-2  border-transparent" data-main-tab="private" data-tab-color="private" onclick="switchMainTab('private')">
                                    <span class="icon-[tabler--lock] size-4 mr-1"></span>
                                    Private Notes
                                </button>
                                <button type="button" class="tab  border-b-2  border-transparent" data-main-tab="attachments" data-tab-color="attachments" onclick="switchMainTab('attachments')">
                                    <span class="icon-[tabler--paperclip] size-4 mr-1"></span>
                                    Attachments
                                    <span class="badge badge-sm ml-1" id="attachment-count">{{ $task->attachments->count() }}</span>
                                </button>
                            </div>

                            <!-- Comment Tab Content -->
                            <div id="comment-tab-content" class="p-6">
                                <div class="flex items-center gap-2 mb-6">
                                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M3.19 14.23L2 19L6.95 16.52C7.93519 16.839 8.96445 17.001 10 17C15 17 19 13.42 19 9C19 4.58 15 1 10 1C5 1 1 4.58 1 9C1.01751 10.9627 1.80376 12.8404 3.19 14.23Z" stroke="#3ca4fc" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    <span class="capitalize text-[#17151C] text-xl font-semibold">comments</span>
                                    <span class="rounded-full w-5 h-5 flex justify-center items-center text-xs bg-[#edecf0] text-black ml-1">{{ $visibleCommentsCount }}</span>
                                </div>
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
                                            <x-quill-editor
                                                name="comment_content"
                                                id="comment-editor"
                                                placeholder="Add a comment..."
                                                height="100px"
                                                :mentions="true"
                                            />
                                            <div class="flex justify-end mt-4 ">
                                                <button type="submit" class="btn btn-primary btn-sm noShadow-btn" id="comment-submit-btn">
                                                    <span class="icon-[tabler--send] size-4"></span>
                                                    <span id="submit-btn-text">Comment</span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </form>

                                <!-- Comments List -->
                                <div class="space-y-4 mt-4">
                                    @php
                                        $publicComments = $task->comments->where('is_private', false);
                                    @endphp
                                    @forelse($publicComments as $comment)
                                        @include('task::partials.comment', ['comment' => $comment])
                                    @empty
                                        <p class="text-base-content/60 text-center py-4">No comments yet. Be the first to comment!</p>
                                    @endforelse
                                </div>
                            </div>

                            <!-- Private Notes Tab Content -->
                            <div id="private-tab-content" class="hidden p-6">
                                <div class="flex items-center gap-2 mb-6">
                                    <svg width="18" height="20" viewBox="0 0 18 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M16 11C16 10.7348 15.8946 10.4805 15.707 10.293C15.5195 10.1054 15.2652 10 15 10H3C2.73478 10 2.48051 10.1054 2.29297 10.293C2.10543 10.4805 2 10.7348 2 11V17L2.00488 17.0986C2.02757 17.3276 2.12883 17.5429 2.29297 17.707C2.48051 17.8946 2.73478 18 3 18H15C15.2652 18 15.5195 17.8946 15.707 17.707C15.8946 17.5195 16 17.2652 16 17V11ZM8 15V13C8 12.4477 8.44771 12 9 12C9.55229 12 10 12.4477 10 13V15C10 15.5523 9.55229 16 9 16C8.44771 16 8 15.5523 8 15ZM12 5C12 4.20435 11.6837 3.44152 11.1211 2.87891C10.5585 2.3163 9.79565 2 9 2C8.20435 2 7.44152 2.3163 6.87891 2.87891C6.3163 3.44152 6 4.20435 6 5V8H12V5ZM14 8H15C15.7957 8 16.5585 8.3163 17.1211 8.87891C17.6837 9.44151 18 10.2043 18 11V17C18 17.7957 17.6837 18.5585 17.1211 19.1211C16.5585 19.6837 15.7957 20 15 20H3C2.20435 20 1.44151 19.6837 0.878906 19.1211C0.316297 18.5585 0 17.7957 0 17V11C0 10.2044 0.316297 9.44151 0.878906 8.87891C1.44151 8.3163 2.20435 8 3 8H4V5C4 3.67392 4.52716 2.40253 5.46484 1.46484C6.40253 0.527162 7.67392 0 9 0C10.3261 0 11.5975 0.527162 12.5352 1.46484C13.4728 2.40253 14 3.67392 14 5V8Z" fill="#ffad0d"/>
                                    </svg>

                                    <span class="capitalize text-[#17151C] text-xl font-semibold">Private Notes</span>
                                    <span class="rounded-full w-5 h-5 flex justify-center items-center text-xs bg-[#edecf0] text-black ml-1">0</span>
                                </div>
                                <form action="{{ route('tasks.comments.store', $task) }}" method="POST" id="private-form" onsubmit="return preparePrivateSubmit()">
                                    @csrf
                                    <input type="hidden" name="is_private" id="is_private_input_private" value="1">
                                    <input type="hidden" name="content" id="final_content_input_private" value="">
                                    <div class="flex gap-3">
                                        <div class="avatar">
                                            <div class="w-8 h-8 rounded-full overflow-hidden">
                                                <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="w-full h-full object-cover" />
                                            </div>
                                        </div>
                                        <div class="flex-1">
                                            <div class="border-1 border-[#B8B7BB] rounded-lg overflow-hidden">
                                                
                                                <x-quill-editor
                                                    name="private_content"
                                                    id="private-editor"
                                                    placeholder="Add a private note (only visible to team members)..."
                                                    height="100px"
                                                    :mentions="true"
                                                />
                                            </div>
                                            <div class="flex justify-end mt-2">
                                                <button type="submit" class="btn btn-warning btn-sm" id="private-submit-btn">
                                                    <span class="icon-[tabler--send] size-4"></span>
                                                    Add Private Note
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </form>

                                <!-- Private Notes List -->
                                <div class="space-y-4 mt-4">
                                    @php
                                        $privateComments = $task->comments->where('is_private', true);
                                    @endphp
                                    @forelse($privateComments as $comment)
                                        @include('task::partials.comment', ['comment' => $comment])
                                    @empty
                                        <p class="text-base-content/60 text-center py-4">No private notes yet.</p>
                                    @endforelse
                                </div>
                            </div>

                            <!-- Attachments Tab Content -->
                            <div id="attachments-tab-content" class="hidden p-6">
                                <div class="flex items-center gap-2 mb-6">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M4.92826 9.17135C2.19459 11.905 2.19459 16.3372 4.92826 19.0708C7.66193 21.8045 12.0941 21.8045 14.8278 19.0708L19.0704 14.8282C19.4609 14.4377 19.4609 13.8045 19.0704 13.414C18.6799 13.0235 18.0467 13.0235 17.6562 13.414L13.4135 17.6566C11.4609 19.6092 8.2951 19.6092 6.34248 17.6566C4.38985 15.704 4.38985 12.5382 6.34248 10.5856L11.9993 4.92871C13.1709 3.75713 15.0704 3.75713 16.242 4.92871C17.4135 6.10028 17.4135 7.99977 16.242 9.17135L10.5851 14.8282C10.1946 15.2187 9.56143 15.2187 9.1709 14.8282C8.78038 14.4377 8.78038 13.8045 9.1709 13.414L13.4135 9.17135C13.8041 8.78082 13.8041 8.14766 13.4135 7.75713C13.023 7.36661 12.3899 7.36661 11.9993 7.75713L7.75669 11.9998C6.58512 13.1713 6.58512 15.0708 7.75669 16.2424C8.92826 17.414 10.8278 17.414 11.9993 16.2424L17.6562 10.5856C19.6088 8.63294 19.6088 5.46711 17.6562 3.51449C15.7036 1.56187 12.5377 1.56187 10.5851 3.51449L4.92826 9.17135Z" fill="#5334E4"/>
                                    </svg>
                                    <span class="capitalize text-[#17151C] text-xl font-semibold">Attachments</span>
                                    <span class="rounded-full w-5 h-5 flex justify-center items-center text-xs bg-[#edecf0] text-black ml-1">0</span>
                                </div>
                                

                                <!-- Upload Button -->
                                <div class="mt-4">
                                    <input type="file" name="files[]" id="attachment-files" multiple class="hidden" data-upload-url="{{ route('tasks.attachments.store', $task) }}">
                                    <button type="button" id="attachment-upload-btn" class="btn bg-transparent border border-[#B8B7BB] py-2 pl-3 pr-4 noShadow-btn" onclick="document.getElementById('attachment-files').click()">
                                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M2.5 14.1667V13.3333C2.5 12.8731 2.8731 12.5 3.33333 12.5C3.79357 12.5 4.16667 12.8731 4.16667 13.3333V14.1667C4.16667 14.6087 4.34239 15.0325 4.65495 15.3451C4.96751 15.6576 5.39131 15.8333 5.83333 15.8333H14.1667L14.3311 15.8252C14.7127 15.7874 15.0715 15.6186 15.3451 15.3451C15.6186 15.0715 15.7874 14.7127 15.8252 14.3311L15.8333 14.1667V13.3333C15.8333 12.8731 16.2064 12.5 16.6667 12.5C17.1269 12.5 17.5 12.8731 17.5 13.3333V14.1667C17.5 15.0507 17.1486 15.8983 16.5234 16.5234C15.8983 17.1486 15.0507 17.5 14.1667 17.5H5.83333C4.94928 17.5 4.10168 17.1486 3.47656 16.5234C2.85144 15.8983 2.5 15.0507 2.5 14.1667Z" fill="#17151C"/>
                                            <path d="M9.16628 13.3337C9.16628 13.7939 9.53937 14.167 9.99961 14.167C10.4598 14.167 10.8329 13.7939 10.8329 13.3337V5.34538L12.7438 7.25618C13.0692 7.58162 13.5967 7.58162 13.9221 7.25618C14.2476 6.93075 14.2476 6.40324 13.9221 6.0778L10.5888 2.74447C10.2634 2.41903 9.73586 2.41903 9.41042 2.74447L6.07709 6.0778C5.75165 6.40324 5.75165 6.93075 6.07709 7.25618C6.40252 7.58162 6.93003 7.58162 7.25547 7.25618L9.16628 5.34538V13.3337Z" fill="#17151C"/>
                                        </svg>
                                        <span class="loading loading-spinner loading-sm hidden pl-1.5" id="upload-spinner"></span>
                                        <span id="upload-btn-text" class="text-base text-[#17151C]">Upload</span>
                                    </button>
                                </div>

                                @if($task->attachments->isEmpty())
                                    <p class="text-base-content/60 text-center py-4">No attachments yet.</p>
                                @endif

                                <div id="attachments-list" class="{{ $task->attachments->isEmpty() ? 'hidden' : '' }}">
                                    <div class="overflow-x-auto border border-base-300 rounded-lg">
                                        <table class="table table-zebra table-compact w-full">
                                            <thead>
                                                <tr>
                                                    <th class="bg-base-200">File Name</th>
                                                    <th class="bg-base-200 text-center w-32">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($task->attachments as $attachment)
                                                    <tr data-attachment-id="{{ $attachment->id }}">
                                                        <td>
                                                            <div class="flex flex-col">
                                                                <span class="font-medium">{{ $attachment->original_name }}</span>
                                                                <span class="text-xs text-base-content/60">{{ $attachment->getFormattedSize() }}</span>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="flex justify-center gap-1">
                                                                <a href="{{ route('tasks.attachments.download', $attachment) }}"
                                                                class="btn btn-xs btn-outline">
                                                                    Download
                                                                </a>
                                                                @if($attachment->uploaded_by === $user->id || $user->isAdminOrHigher())
                                                                    <form action="{{ route('tasks.attachments.destroy', $attachment) }}" method="POST" class="inline">
                                                                        @csrf
                                                                        @method('DELETE')
                                                                        <button type="submit" 
                                                                                class="btn btn-xs btn-outline btn-error"
                                                                                onclick="return confirm('Delete this attachment?')">
                                                                            Delete
                                                                        </button>
                                                                    </form>
                                                                @endif
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                
                            </div>
                        </div>
                        @else
                        <!-- Client view - simple comment form without tabs -->
                        <form action="{{ route('tasks.comments.store', $task) }}" method="POST" class="mb-4" id="comment-form">
                            @csrf
                            <div class="flex gap-3">
                                <div class="avatar">
                                    <div class="w-10 h-10 rounded-full overflow-hidden">
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

                        <!-- Comments List for Client -->
                        <div class="space-y-4">
                            @php
                                $visibleComments = $task->comments->where('is_private', false);
                            @endphp
                            @forelse($visibleComments as $comment)
                                @include('task::partials.comment', ['comment' => $comment])
                            @empty
                                <p class="text-base-content/60 text-center py-4">No comments yet. Be the first to comment!</p>
                            @endforelse
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Activity Log -->
                <div class="card bg-base-100 shadow">
                    <div class="card-body">
                        <div class="flex gap-2 items-center">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M3 17V7C3 4.79086 4.79086 3 7 3H17C19.2091 3 21 4.79086 21 7V17C21 19.2091 19.2091 21 17 21H7C4.79086 21 3 19.2091 3 17Z" stroke="#3ca4fc" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M8 16L9.8793 12.4406C10.2993 11.6452 11.4002 11.5485 11.9525 12.2584C12.5163 12.9832 13.645 12.8641 14.0452 12.0377L16 8" stroke="#3ca4fc" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <h2 class="card-title text-xl text-[#17151C]">
                            Activity
                            </h2>
                        </div>

                        <div class="relative mt-6">
                            <!-- Vertical Line - only show if there are activities -->
                            @if($task->activities->count() > 0)
                                <div class="absolute left-4 top-0 w-0.5 bg-base-300" 
                                    style="height: calc(100% - 2rem);"></div>
                            @endif
                            
                            <div class="space-y-4">
                                @forelse($task->activities as $index => $activity)
                                    <div class="flex gap-4 relative">
                                        <!-- User Avatar -->
                                        @if($activity->user)
                                            <div class="avatar z-10">
                                                <div class="w-8 h-8 rounded-full overflow-hidden ring-2 ring-base-100">
                                                    <img src="{{ $activity->user->avatar_url }}" 
                                                        alt="{{ $activity->user->name }}" 
                                                        class="w-full h-full object-cover" />
                                                </div>
                                            </div>
                                        @else
                                            <div class="w-8 h-8 rounded-full bg-base-200 flex items-center justify-center z-10 ring-2 ring-base-100">
                                                <span class="icon-[{{ $activity->type->icon() }}] size-4 text-base-content/60"></span>
                                            </div>
                                        @endif
                                        
                                        <!-- Activity Content -->
                                        <div class="flex-1 {{ $loop->last ? '' : 'pb-4' }}">
                                            <p class="text-sm">{{ $activity->getFormattedDescription() }}</p>
                                            <p class="text-xs text-base-content/60">{{ $activity->created_at->diffForHumans() }}</p>
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
            <div class="border-l border-[#EDECF0] -mt-4 bg-white">
                <div class="flex items-center justify-center space-x-3 py-6 bg-[#f6f5fe]">
                    @if(!$isClient && !$task->isClosed())
                    <!-- Action Buttons (hidden for closed tasks) -->    
                    <!-- Watch Button -->
                        <form action="{{ route('tasks.watch.toggle', $task) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="pl-2 pr-3 py-2 border bg-white border-[#B8B7BB] rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 flex items-center space-x-2">
                            @if($task->isWatcher($user))
                                <i class="fas fa-eye-slash pr-1.5"></i>
                                <span class="text-base">Unwatch</span>
                                
                                @else
                                <i class="fas fa-eye pr-1.5"></i>
                                <span class="text-base">Watch</span>
                                    
                            @endif
                            </button>
                        </form> 
                        <!-- On Hold Button -->
                        @if($task->canManageHold($user))
                            @if($task->isOnHold())
                                <button type="button" class="pl-2 pr-3 py-2 border bg-white border-[#B8B7BB] rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 flex items-center space-x-2" onclick="openResumeTaskModal()">
                                <i class="fas fa-play-circle text-black"></i>    
                                <span class="text-black text-base">Resume</span>
                                </button>
                            @else
                                <button type="button" class="pl-2 pr-3 py-2 border bg-white border-[#B8B7BB] rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 flex items-center space-x-2" onclick="openOnHoldModal()">
                                
                                <i class="fa-regular fa-circle-pause text-black"></i>
                                <span class="text-black text-base">On Hold</span>
                                </button>
                            @endif
                        @endif

                        <!-- Edit Button -->
                        @if($task->isOwner($user) && !$task->isOnHold())
                            <a href="{{ route('tasks.edit', $task) }}" class="px-4 py-2 border border-[#B8B7BB] rounded-lg bg-white text-base font-medium text-[#17151C] hover:bg-gray-50 flex items-center space-x-2">
                            <span class="icon-[tabler--pencil] size-4"></span>
                                <span>Edit</span>   
                            </a>
                        @endif
                    @endif
                    <!-- Close/Reopen Task -->
                    @if($task->isOwner($user) && !$task->isOnHold())
                        @if($task->isClosed())
                            <form action="{{ route('tasks.reopen', $task) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700">
                                    <span class="icon-[tabler--refresh] size-4"></span>
                                    Reopen
                                </button>
                            </form>
                        @else
                            <form action="{{ route('tasks.close', $task) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="flex items-center px-4 py-2 bg-green-600 text-white rounded-lg text-base font-medium hover:bg-green-700">
                                    Close
                                </button>
                            </form>
                        @endif
                    @endif
                </div>
                
                <!-- Task Info Card -->
                <div class="card card-lt bg-base-100 group">
                    <div class="card-body pb-0">
                        <div class="flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                <path d="M3 5C3 3.34315 4.34315 2 6 2H18C19.6569 2 21 3.34315 21 5V19C21 20.6569 19.6569 22 18 22H6C4.34315 22 3 20.6569 3 19V5Z" stroke="#3BA5FF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M8 10L16 10" stroke="#3BA5FF" stroke-width="2" stroke-linecap="round"/>
                                <path d="M10 14L14 14" stroke="#3BA5FF" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                            <h2 class="card-title text-[20px]">Details</h2>
                        </div>
                        <!-- Divider -->
                        <div class="w-full h-px bg-[#EDECF0] mt-6 mb-4"></div>

                        <!-- Status, Priority & Progress -->
                        <div class="py-3 first:pt-0"> 
                            <div class="space-y-5">
                                <!-- Status Dropdown -->
                                <div>
                                    <label class="text-sm font-medium text-[#525158] mb-2 block">Status</label>
                                    <div id="status-dropdown-container" class="relative">
                                            <button 
                                                id="status-dropdown-button"
                                                type="button" 
                                                class="w-full p-2 pr-3 border-1 border-[#B8B7BB] rounded-md text-sm flex items-center justify-between bg-base-100 hover:bg-base-200 focus:outline-none transition-all duration-200"
                                            >
                                                
                                                
                                                <div class="flex items-center gap-3">
                                                    <span id="status-badge" class="px-2 py-1 rounded-md text-sm font-semibold {{ $badgeClass }}">
                                                        {{ $currentStatus }}
                                                    </span>
                                                </div>
                                                <i id="status-chevron" class="fas fa-chevron-down text-sm transition-transform duration-200"></i>
                                            </button>
                                            
                                            <div 
                                                id="status-dropdown-menu"
                                                class="hidden absolute z-20 w-full mt-2 bg-base-100 rounded-xl border-2 border-base-300 shadow-lg max-h-60 overflow-y-auto"
                                            >
                                                <div class="p-2">
                                                    <!-- No Status Option -->
                                                    <button 
                                                        type="button" 
                                                        class="status-option w-full text-left px-4 py-3 text-sm rounded-lg hover:bg-base-200 transition-colors flex items-center gap-3 {{ !$task->status_id ? 'bg-primary/10' : '' }}" 
                                                        data-status-id=""
                                                        data-status-name="No Status"
                                                        data-status-badge-class="bg-[#3f404d] text-white"
                                                    >
                                                        <span class="px-2 py-1 rounded-md text-sm bg-[#3f404d] text-white">No Status</span>
                                                    </button>
                                                    
                                                    <!-- Status Options from $statuses variable -->
                                                    @foreach($statuses as $status)
                                                        @php
                                                            $statusNameLower = strtolower($status->name);
                                                            $optionBadgeClass = $statusBadgeColors[$statusNameLower] ?? 'bg-base-300 text-base-content border border-base-300';
                                                        @endphp
                                                        
                                                        <button 
                                                            type="button" 
                                                            class="status-option w-full text-left px-4 py-3 text-sm rounded-lg hover:bg-base-200 transition-colors flex items-center gap-3 {{ $task->status_id == $status->id ? 'bg-primary/10' : '' }}" 
                                                            data-status-id="{{ $status->id }}"
                                                            data-status-name="{{ $status->name }}"
                                                            data-status-badge-class="{{ $optionBadgeClass }}"
                                                        >
                                                            <span class="px-2 py-1 rounded-md text-sm {{ $optionBadgeClass }}">{{ $status->name }}</span>
                                                        </button>
                                                    @endforeach
                                                </div>
                                            </div>
                                            
                                            <!-- Hidden input for form submission -->
                                            <input type="hidden" id="quick-status-select" name="status_id" value="{{ $task->status_id }}">
                                        </div>
                                    </div>
                                </div>                                
                                <!-- Priority Dropdown -->
                                <div class="mt-4">
                                    <label class="text-sm font-medium text-[#525158] mb-2 block">Priority</label>
                                    <div id="priority-dropdown-container" class="relative">
                                        <button 
                                            id="priority-dropdown-button"
                                            type="button" 
                                            class="w-full p-2 pr-3  border-1 border-[#B8B7BB] rounded-md text-sm flex items-center justify-between bg-base-100 hover:bg-base-200 focus:outline-none transition-all duration-200"
                                        >
                                            <!-- Priority Badge Display -->
                                            @php
                                                // Determine current priority and label based on workspace type
                                                $currentPriorityLabel = 'No Priority';
                                                $currentPriorityId = '';
                                                
                                                if($task->workspace->type->value === 'inbox') {
                                                    $currentWsPriority = $workspacePriorities->firstWhere('id', $task->workspace_priority_id);
                                                    $currentPriorityLabel = $currentWsPriority ? $currentWsPriority->name : 'No Priority';
                                                    $currentPriorityId = $currentWsPriority ? $currentWsPriority->id : '';
                                                } else {
                                                    $currentPriorityLabel = $task->priority?->label() ?? 'No Priority';
                                                    $currentPriorityId = $task->priority?->value ?? '';
                                                }
                                                
                                                // Define badge colors for priorities
                                                $priorityBadgeColors = [
                                                    'no priority' => 'bg-[#3F404D] text-white',
                                                    'medium' => 'bg-[#FDF3E3] text-[#F59E0C]',
                                                    'lowest' => 'bg-[#ECEEF0] text-[#64748B]',
                                                    'low' => 'bg-[#E6F0FE] text-[#629BF8]',
                                                    'high' => 'bg-[#FEF3EC] text-[#F97D2B]',
                                                    'highest' => 'bg-[#FDF0F0] text-[#EF4445]',
                                                ];
                                                
                                                $priorityNameLower = strtolower($currentPriorityLabel);
                                                $priorityBadgeClass = $priorityBadgeColors[$priorityNameLower] ?? 'bg-base-300 text-base-content border border-base-300';
                                            @endphp
                                            
                                            <div class="flex items-center gap-3">
                                                <span id="priority-badge" class="px-2 py-1 rounded-md text-sm font-semibold {{ $priorityBadgeClass }}">
                                                    {{ $currentPriorityLabel }}
                                                </span>
                                            </div>
                                            <i id="priority-chevron" class="fas fa-chevron-down text-sm transition-transform duration-200"></i>
                                        </button>
                                        
                                        <div 
                                            id="priority-dropdown-menu"
                                            class="hidden absolute z-20 w-full mt-2 bg-base-100 rounded-xl border-2 border-base-300 shadow-lg max-h-60 overflow-y-auto"
                                        >
                                            <div class="p-2">
                                                @if($task->workspace->type->value === 'inbox')
                                                    <!-- Workspace Priorities -->
                                                    <!-- No Priority Option -->
                                                    <button 
                                                        type="button" 
                                                        class="priority-option w-full text-left px-4 py-3 text-sm rounded-lg hover:bg-base-200 transition-colors flex items-center gap-3 {{ !$task->workspace_priority_id ? 'bg-primary/10' : '' }}" 
                                                        data-priority-id=""
                                                        data-priority-name="No Priority"
                                                        data-priority-badge-class="bg-base-300 text-base-content border border-base-300"
                                                        data-priority-type="workspace"
                                                    >
                                                        <span class="px-2 py-1 rounded-md text-sm bg-base-300 text-base-content border border-base-300">No Priority</span>
                                                    </button>
                                                    
                                                    @foreach($workspacePriorities as $wsPriority)
                                                        @php
                                                            $priorityNameLower = strtolower($wsPriority->name);
                                                            $priorityBadgeClass = $priorityBadgeColors[$priorityNameLower] ?? 'bg-base-300 text-base-content border border-base-300';
                                                        @endphp
                                                        
                                                        <button 
                                                            type="button" 
                                                            class="priority-option w-full text-left px-4 py-3 text-sm rounded-lg hover:bg-base-200 transition-colors flex items-center gap-3 {{ $task->workspace_priority_id == $wsPriority->id ? 'bg-primary/10' : '' }}" 
                                                            data-priority-id="{{ $wsPriority->id }}"
                                                            data-priority-name="{{ $wsPriority->name }}"
                                                            data-priority-badge-class="{{ $priorityBadgeClass }}"
                                                            data-priority-type="workspace"
                                                        >
                                                            <span class="px-2 py-1 rounded-md text-sm {{ $priorityBadgeClass }}">{{ $wsPriority->name }}</span>
                                                        </button>
                                                    @endforeach
                                                @else
                                                    <!-- Task Priorities -->
                                                    <!-- No Priority Option -->
                                                    <button 
                                                        type="button" 
                                                        class="priority-option w-full text-left px-4 py-3 text-sm rounded-lg hover:bg-base-200 transition-colors flex items-center gap-3 {{ !$task->priority ? 'bg-primary/10' : '' }}" 
                                                        data-priority-id=""
                                                        data-priority-name="No Priority"
                                                        data-priority-badge-class="bg-[#3F404D] text-white"
                                                        data-priority-type="task"
                                                    >
                                                        <span class="px-2 py-1 rounded-md text-sm bg-[#3F404D] text-white ">No Priority</span>
                                                    </button>
                                                    
                                                    @foreach(\App\Modules\Task\Enums\TaskPriority::cases() as $priority)
                                                        @php
                                                            $priorityLabel = $priority->label();
                                                            $priorityNameLower = strtolower($priorityLabel);
                                                            $priorityBadgeClass = $priorityBadgeColors[$priorityNameLower] ?? 'bg-base-300 text-base-content border border-base-300';
                                                        @endphp
                                                        
                                                        <button 
                                                            type="button" 
                                                            class="priority-option w-full text-left px-4 py-3 text-sm rounded-lg hover:bg-base-200 transition-colors flex items-center gap-3 {{ $task->priority == $priority ? 'bg-primary/10' : '' }}" 
                                                            data-priority-id="{{ $priority->value }}"
                                                            data-priority-name="{{ $priorityLabel }}"
                                                            data-priority-badge-class="{{ $priorityBadgeClass }}"
                                                            data-priority-type="task"
                                                        >
                                                            <span class="px-2 py-1 rounded-md text-sm {{ $priorityBadgeClass }}">{{ $priorityLabel }}</span>
                                                        </button>
                                                    @endforeach
                                                @endif
                                            </div>
                                        </div>
                                        
                                        <!-- Hidden input for form submission -->
                                        <input type="hidden" id="quick-priority-select" name="priority_id" 
                                            value="{{ $task->workspace->type->value === 'inbox' ? $task->workspace_priority_id : $task->priority?->value }}">
                                        <input type="hidden" id="priority-type" value="{{ $task->workspace->type->value === 'inbox' ? 'workspace' : 'task' }}">
                                    </div>
                                </div>

                                <!-- Progress Slider -->
                                <div class="mt-4">
                                    <div class="w-full flex justify-between items-center">
                                    <label class="text-sm font-medium text-[#525158]">Progress</label>
                                         <span class="text-sm font-medium text-center" id="quick-progress-percentage">
                                            {{ $task->progress ?? 0 }}%
                                        </span> 
                                    </div>
                                    
                                    <div class="space-y-3">
                                    <div class="flex items-center gap-4">
                                        <div class="flex-1 relative">
                                            <input type="range"
                                                id="quick-progress-slider"
                                                min="0"
                                                max="100"
                                                step="5"
                                                value="{{ $task->progress ?? 0 }}"
                                                class="progress-slider w-full"
                                            />
                                        </div>
                                        
                                    </div>
                                    </div>
                                </div>
                            </div>
                                           
                            <!-- Save All Button -->
                            <div>
                                <button type="button" class="btn btn-primary btn-md noShadow-btn" onclick="saveQuickStats()">
                                    Save
                                </button>
                            </div>
                            <!-- Divider -->
                            <div class="w-full h-px bg-[#EDECF0] mt-6 mb-4"></div>
                            <!-- Due Date & Created Date  -->
                            <div class="py-3">
                                <div class="grid grid-cols-2 gap-4">
                                    <!-- Due Date -->
                                    <div>
                                        <label class="text-sm font-medium text-[#525158] mb-2 block">Due Date</label>
                                        <div class="relative">
                                            <button type="button" onclick="toggleDatesCalendar('due')" class="w-full p-3 pr-10 border border-[#B8B7BB] rounded-md text-sm flex items-center bg-base-100 hover:bg-base-200 focus:outline-none transition-all duration-200 text-left">
                                                <span id="dates-due-display" class="text-base-content/50">
                                                    {{ $task->due_date ? $task->due_date->format('M d, Y') : 'Select date...' }}
                                                </span>
                                            </button>
                                            <span class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none">
                                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M16.6667 7.5C17.1269 7.5 17.5 7.8731 17.5 8.33333C17.5 8.79357 17.1269 9.16667 16.6667 9.16667H3.33333C2.8731 9.16667 2.5 8.79357 2.5 8.33333C2.5 7.8731 2.8731 7.5 3.33333 7.5H16.6667Z" fill="#525158"/>
                            <path d="M16.667 6.66634C16.667 5.74587 15.9208 4.99967 15.0003 4.99967H5.00032C4.07985 4.99967 3.33366 5.74587 3.33366 6.66634V14.1663C3.33366 15.0868 4.07985 15.833 5.00032 15.833H15.0003C15.9208 15.833 16.667 15.0868 16.667 14.1663V6.66634ZM18.3337 14.1663C18.3337 16.0073 16.8413 17.4997 15.0003 17.4997H5.00032C3.15938 17.4997 1.66699 16.0073 1.66699 14.1663V6.66634C1.66699 4.82539 3.15938 3.33301 5.00032 3.33301H15.0003C16.8413 3.33301 18.3337 4.82539 18.3337 6.66634V14.1663Z" fill="#525158"/>
                            <path d="M5.83301 4.16634V1.66634C5.83301 1.2061 6.2061 0.833008 6.66634 0.833008C7.12658 0.833008 7.49967 1.2061 7.49967 1.66634V4.16634C7.49967 4.62658 7.12658 4.99967 6.66634 4.99967C6.2061 4.99967 5.83301 4.62658 5.83301 4.16634Z" fill="#525158"/>
                            <path d="M12.5 4.16634V1.66634C12.5 1.2061 12.8731 0.833008 13.3333 0.833008C13.7936 0.833008 14.1667 1.2061 14.1667 1.66634V4.16634C14.1667 4.62658 13.7936 4.99967 13.3333 4.99967C12.8731 4.99967 12.5 4.62658 12.5 4.16634Z" fill="#525158"/>
                            </svg>

                                            </span>
                                            
                                            <!-- Due Date Calendar (hidden by default) -->
                                            <div id="dates-due-calendar" class="hidden absolute z-20 mt-2 bg-base-100 rounded-lg p-3 border-2 border-base-300 shadow-lg w-full">
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
                                        <input type="hidden" id="dates-due-date-input" value="{{ $task->due_date?->format('Y-m-d') }}">
                                    </div>
                                    <!-- Created Date -->
                                    <div>
                                        <label class="text-sm font-medium text-[#525158] mb-2 block">Created Date</label>
                                        <div class="relative">
                                            <button type="button" onclick="toggleDatesCalendar('created')" class="w-full p-3 pr-10 border border-[#B8B7BB] rounded-md text-sm flex items-center bg-base-100 hover:bg-base-200 focus:outline-none transition-all duration-200 text-left">
                                                <span id="dates-created-display" class="text-base-content/50">
                                                    {{ $task->created_at->format('M d, Y') }}
                                                </span>
                                            </button>
                                            <span class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none">
                                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M16.6667 7.5C17.1269 7.5 17.5 7.8731 17.5 8.33333C17.5 8.79357 17.1269 9.16667 16.6667 9.16667H3.33333C2.8731 9.16667 2.5 8.79357 2.5 8.33333C2.5 7.8731 2.8731 7.5 3.33333 7.5H16.6667Z" fill="#525158"/>
                            <path d="M16.667 6.66634C16.667 5.74587 15.9208 4.99967 15.0003 4.99967H5.00032C4.07985 4.99967 3.33366 5.74587 3.33366 6.66634V14.1663C3.33366 15.0868 4.07985 15.833 5.00032 15.833H15.0003C15.9208 15.833 16.667 15.0868 16.667 14.1663V6.66634ZM18.3337 14.1663C18.3337 16.0073 16.8413 17.4997 15.0003 17.4997H5.00032C3.15938 17.4997 1.66699 16.0073 1.66699 14.1663V6.66634C1.66699 4.82539 3.15938 3.33301 5.00032 3.33301H15.0003C16.8413 3.33301 18.3337 4.82539 18.3337 6.66634V14.1663Z" fill="#525158"/>
                            <path d="M5.83301 4.16634V1.66634C5.83301 1.2061 6.2061 0.833008 6.66634 0.833008C7.12658 0.833008 7.49967 1.2061 7.49967 1.66634V4.16634C7.49967 4.62658 7.12658 4.99967 6.66634 4.99967C6.2061 4.99967 5.83301 4.62658 5.83301 4.16634Z" fill="#525158"/>
                            <path d="M12.5 4.16634V1.66634C12.5 1.2061 12.8731 0.833008 13.3333 0.833008C13.7936 0.833008 14.1667 1.2061 14.1667 1.66634V4.16634C14.1667 4.62658 13.7936 4.99967 13.3333 4.99967C12.8731 4.99967 12.5 4.62658 12.5 4.16634Z" fill="#525158"/>
                            </svg>

                                            </span>
                                            
                                            <!-- Created Date Calendar (hidden by default) -->
                                            <div id="dates-created-calendar" class="hidden absolute z-20 mt-2 bg-base-100 rounded-lg p-3 border-2 border-base-300 shadow-lg w-full">
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
                                        <input type="hidden" id="dates-created-date-input" value="{{ $task->created_at->format('Y-m-d') }}">
                                    </div>
                                </div>
                                <!-- Save Button Below -->
                                <div class="mt-4">
                                    <button type="button" class="btn btn-primary btn-md noShadow-btn" onclick="saveDates()">
                                        Save
                                    </button>
                                </div>
                            </div>
                            <!-- Divider -->
                            <div class="w-full h-px bg-[#EDECF0] mt-6 mb-4"></div>
                            <!-- Assignee & Created By (Stacked - Always Editable) -->
                            <div class="py-3">
                                <div class="space-y-4">
                                    <!-- Assignee -->
                                    <div>
                                        <label class="text-sm font-medium text-[#525158] mb-2 block">Assignee</label>
                                        <div class="relative">
                                            <button type="button" onclick="togglePeopleDropdown('assignee')" class="w-full p-3 pr-10 rounded-md border-1 border-[#B8B7BB] text-sm flex items-center bg-base-100 hover:bg-base-200 focus:outline-none transition-all duration-200 text-left gap-2">
                                                
                                                <span id="people-assignee-display" class="flex-1 font-normal text-base text-[#525158]">{{ $task->assignee?->name ?? 'Unassigned' }}</span>
                                            </button>
                                            <span class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none">
                                            <svg width="12" height="7" viewBox="0 0 12 7" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path fill-rule="evenodd" clip-rule="evenodd" d="M0.244075 0.244409C-0.0813586 0.569826 -0.0813586 1.09749 0.244075 1.42291L5.24408 6.42292C5.5695 6.74835 6.09717 6.74835 6.42258 6.42292L11.4226 1.42291C11.748 1.09749 11.748 0.569825 11.4226 0.244409C11.0972 -0.081008 10.5695 -0.0810079 10.2441 0.244409L5.83333 4.65516L1.42259 0.244409C1.09715 -0.0810075 0.569516 -0.0810075 0.244075 0.244409Z" fill="#525158"/>
                                            </svg>

                                            </span>
                                            
                                            <!-- Assignee Dropdown  -->
                                            <div id="people-assignee-dropdown" class="hidden absolute z-20 mt-2 bg-base-100 rounded-md border-1 border-[#B8B7BB] shadow-lg max-h-48 overflow-y-auto w-full">
                                                <div class="p-1">
                                                    <button type="button" onclick="selectPerson('assignee', '', 'Unassigned', '')" class="w-full text-left px-3 py-2 text-sm rounded hover:bg-base-200 flex items-center gap-2">
                                                        <span>Unassigned</span>
                                                    </button>
                                                    @foreach($users as $u)
                                                    <button type="button" onclick="selectPerson('assignee', '{{ $u->id }}', '{{ $u->name }}', '{{ $u->avatar_url }}')" class="w-full text-left px-3 py-2 text-sm rounded hover:bg-base-200 flex items-center gap-2 {{ $task->assignee_id == $u->id ? 'bg-primary/10' : '' }}">
                                                        <span>{{ $u->name }}</span>
                                                        @if($task->assignee_id == $u->id)
                                                        <span class="icon-[tabler--check] size-4 text-primary ml-auto"></span>
                                                        @endif
                                                    </button>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                        <input type="hidden" id="people-assignee-input" value="{{ $task->assignee_id }}">
                                    </div>

                                    <!-- Creator -->
                                    <div>
                                        <label class="text-sm font-medium text-[#525158] mb-2 block">Created by</label>
                                        <div class="relative">
                                            <button type="button" onclick="togglePeopleDropdown('creator')" class="w-full p-3 pr-10 border border-[#B8B7BB] rounded-md text-sm flex items-center bg-base-100 hover:bg-base-200 focus:outline-none transition-all duration-200 text-left gap-2">
                                                <span id="people-creator-display" class="flex-1 font-normal text-base text-[#525158]">{{ $task->creator->name }}</span>
                                            </button>
                                            <span class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none">
                                            <svg width="12" height="7" viewBox="0 0 12 7" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path fill-rule="evenodd" clip-rule="evenodd" d="M0.244075 0.244409C-0.0813586 0.569826 -0.0813586 1.09749 0.244075 1.42291L5.24408 6.42292C5.5695 6.74835 6.09717 6.74835 6.42258 6.42292L11.4226 1.42291C11.748 1.09749 11.748 0.569825 11.4226 0.244409C11.0972 -0.081008 10.5695 -0.0810079 10.2441 0.244409L5.83333 4.65516L1.42259 0.244409C1.09715 -0.0810075 0.569516 -0.0810075 0.244075 0.244409Z" fill="#525158"/>
                                                </svg>

                                            </span>
                                            
                                            <!-- Creator Dropdown (hidden by default) -->
                                            <div id="people-creator-dropdown" class="hidden absolute z-20 mt-2 bg-base-100 rounded-lg border-2 border-base-300 shadow-lg max-h-48 overflow-y-auto w-full">
                                                <div class="p-1">
                                                    @foreach($users as $u)
                                                    <button type="button" onclick="selectPerson('creator', '{{ $u->id }}', '{{ $u->name }}', '{{ $u->avatar_url }}')" class="w-full text-left px-3 py-2 text-sm rounded hover:bg-base-200 flex items-center gap-2 {{ $task->created_by == $u->id ? 'bg-primary/10' : '' }}">
                                                        
                                                        <span>{{ $u->name }}</span>
                                                        @if($task->created_by == $u->id)
                                                        <span class="icon-[tabler--check] size-4 text-primary ml-auto"></span>
                                                        @endif
                                                    </button>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                        <input type="hidden" id="people-creator-input" value="{{ $task->created_by }}">
                                    </div>
                                </div>

                                <!-- Save Button Below -->
                                <div class="mt-4">
                                    <button type="button" class="btn btn-primary btn-md noShadow-btn" onclick="savePeople()">
                                        Save
                                    </button>
                                </div>
                            </div>
                            <!-- Divider -->
                            <div class="w-full h-px bg-[#EDECF0] mt-6 mb-4"></div>
                            <!-- Task Type -->
                            <div class="py-3">
                                <label class="text-sm font-medium text-[#525158] mb-2 block">Type</label>
                                <div class="relative">
                                    <button type="button" onclick="toggleTypeDropdown()" class="w-full min-h-[48px] p-2 pr-10 border-1 border-[#B8B7BB] rounded-md text-sm flex items-center flex-wrap gap-2 bg-base-100 hover:bg-base-200 focus:outline-none transition-all duration-200 text-left">
                                        <div id="type-display-container" class="flex items-center flex-wrap gap-2 flex-1">
                                            @php
                                                $selectedTypes = $task->types && count($task->types) > 0 ? $task->types : [];
                                            @endphp
                                            @if($selectedTypes && count($selectedTypes) > 0)
                                                @foreach($selectedTypes as $type)
                                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-[#EDECF0] text-xs text-[#525158] font-medium type-badge" data-type="{{ $type->value }}">
                                                        <span class="icon-[{{ $type->icon() }}] size-4"></span>
                                                        {{ $type->label() }}
                                                    </span>
                                                @endforeach
                                            @else
                                                <span class="text-base-content/50">Select types...</span>
                                            @endif
                                        </div>
                                    </button>
                                    <span class="absolute right-3 top-5 pointer-events-none">
                                        <svg width="12" height="7" viewBox="0 0 12 7" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd" clip-rule="evenodd" d="M0.244075 0.244409C-0.0813586 0.569826 -0.0813586 1.09749 0.244075 1.42291L5.24408 6.42292C5.5695 6.74835 6.09717 6.74835 6.42258 6.42292L11.4226 1.42291C11.748 1.09749 11.748 0.569825 11.4226 0.244409C11.0972 -0.081008 10.5695 -0.0810079 10.2441 0.244409L5.83333 4.65516L1.42259 0.244409C1.09715 -0.0810075 0.569516 -0.0810075 0.244075 0.244409Z" fill="#525158"/>
                                        </svg>

                                    </span>
                                    
                                    <!-- Type Dropdown with Checkboxes (hidden by default) -->
                                    <div id="type-dropdown" class="hidden absolute z-20 mt-2 bg-base-100 rounded-lg border-2 border-base-300 shadow-lg w-full max-h-80 overflow-y-auto">
                                        <div class="p-2">
                                            @foreach(\App\Modules\Task\Enums\TaskType::cases() as $type)
                                                <label class="flex items-center gap-2.5 px-3 py-2.5 text-sm rounded hover:bg-base-200 cursor-pointer">
                                                    <input type="checkbox" 
                                                        name="type[]" 
                                                        value="{{ $type->value }}"
                                                        data-label="{{ $type->label() }}"
                                                        data-icon="{{ $type->icon() }}"
                                                        class="checkbox checkbox-sm checkbox-primary type-checkbox"
                                                        {{ $task->types && in_array($type, $task->types) ? 'checked' : '' }}
                                                        onchange="updateTypeDisplay()">
                                                    <span class="icon-[{{ $type->icon() }}] size-5 text-base-content/70"></span>
                                                    <span class="flex-1">{{ $type->label() }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>

                                <!-- Save Button Below -->
                                <div class="mt-4">
                                    <button type="button" class="btn btn-primary btn-md noShadow-btn" onclick="saveType()">
                                        Save
                                    </button>
                                </div>
                            </div>
                            <!-- Divider -->
                            <div class="w-full h-px bg-[#EDECF0] mt-6 mb-4"></div>
                            <!-- Workspace -->
                            <div class="py-3">
                                <label class="text-sm font-normal text-[#525158]">Workspace</label>
                                <div class="mt-2">
                                    <a href="{{ route('workspace.show', $task->workspace) }}" class="inline-flex items-center gap-1.5 text-primary hover:text-primary-focus transition-colors">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M3 10C3 8.34315 4.34315 7 6 7H18C19.6569 7 21 8.34315 21 10V18C21 19.6569 19.6569 21 18 21H6C4.34315 21 3 19.6569 3 18V10Z" fill="#3BA5FF"/>
                                        <path d="M7 12L7.75705 13.4384C8.58617 15.0137 10.2198 16 12 16C13.7802 16 15.4138 15.0137 16.243 13.4384L17 12" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M8 7C8 5.34315 9.34315 4 11 4H13C14.6569 4 16 5.34315 16 7V8H8V7Z" stroke="#3BA5FF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    <span class="text-base text-black">{{ $task->workspace->name }}</span> 
                                    </a>
                                </div>
                            </div>
                        </div>
                        <!-- Divider -->
                        <div class="w-full h-px bg-[#EDECF0] mt-6 mb-4"></div>
                        <!-- Tags -->
                        <div class="bg-base-100">
                            <div class="card-body">
                                <div class="flex items-center gap-2">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M20.59 13.41L13.42 20.58C13.2343 20.766 13.0137 20.9135 12.7709 21.0141C12.5281 21.1148 12.2678 21.1666 12.005 21.1666C11.7422 21.1666 11.4819 21.1148 11.2391 21.0141C10.9963 20.9135 10.7757 20.766 10.59 20.58L3.17322 13.1719C2.42207 12.4216 2 11.4035 2 10.3418V4C2 2.89543 2.89543 2 4 2H10.3431C11.404 2 12.4214 2.42143 13.1716 3.17157L20.59 10.59C20.9625 10.9647 21.1716 11.4716 21.1716 12C21.1716 12.5284 20.9625 13.0353 20.59 13.41Z" stroke="#3BA5FF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M7.50729 6C8.33169 6 9 6.67157 9 7.5C9 8.32843 8.33169 9 7.50729 9H7.49271C6.66831 9 6 8.32843 6 7.5C6 6.67157 6.66831 6 7.49271 6H7.50729Z" fill="#3BA5FF"/>
                                    </svg>
                                    <h2 class="card-title text-xl">
                                        Tags
                                    </h2>
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    @forelse($task->tags as $tag)
                                        <div class="badge gap-1" style="background-color: {{ $tag->color }}20; color: {{ $tag->color }}">
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
                                        <p class="text-[#B8B7BB] text-base">No tags</p>
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
                        </div>
                        <!-- Divider -->
                        <div class="w-full h-px bg-[#EDECF0] mt-6 mb-4"></div>
                        <!-- Watchers (hide for clients) -->
                        @if(!$isClient)
                        <div  class="card-body">
                            <div class="flex items-center gap-2 mb-3">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M11.9998 6C8.8373 6 6.77147 7.56534 4.49695 9.84056C3.32082 11.0171 3.32082 12.999 4.49225 14.1532C6.67955 16.3084 8.95266 18 11.9998 18C15.047 18 17.3201 16.3084 19.5074 14.1532C20.6835 12.9944 20.6835 11.0056 19.5074 9.84675C17.3201 7.69159 15.047 6 11.9998 6ZM3.08252 8.42656C5.4483 6.06005 8.00814 4 11.9998 4C15.8793 4 18.648 6.19224 20.9111 8.42211C22.8823 10.3643 22.8823 13.6357 20.9111 15.5779C18.648 17.8078 15.8793 20 11.9998 20C8.12042 20 5.35167 17.8078 3.08854 15.5779C1.11275 13.6311 1.13493 10.3747 3.08252 8.42656Z" fill="#3BA5FF"/>
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M12 10C10.8954 10 10 10.8954 10 12C10 13.1046 10.8954 14 12 14C13.1046 14 14 13.1046 14 12C14 10.8954 13.1046 10 12 10ZM8 12C8 9.79086 9.79086 8 12 8C14.2091 8 16 9.79086 16 12C16 14.2091 14.2091 16 12 16C9.79086 16 8 14.2091 8 12Z" fill="#3BA5FF"/>
                            </svg>
                                <h2 class="text-[20px] font-semibold text-[#17151C]">Watchers</h2>
                                <span class="badge badge-sm bg-[#EDECF0] text-[#17151C] border-0 rounded-full font-semibold">{{ $task->watchers->count() }}</span>
                            </div>
                            <div class="flex flex-wrap items-center gap-3">
                                @forelse($task->watchers as $watcher)
                                    <div class="avatar" title="{{ $watcher->name }}">
                                        <div class="w-12 h-12 rounded-full hover:ring-primary transition-all cursor-pointer">
                                            @if($watcher->avatar_url)
                                                <img src="{{ $watcher->avatar_url }}" alt="{{ $watcher->name }}" />
                                            @else
                                                <div class="bg-primary text-primary-content flex items-center justify-center w-full h-full">
                                                    <span class="text-lg font-semibold">{{ strtoupper(substr($watcher->name, 0, 2)) }}</span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-base-content/60 text-sm">No watchers</p>
                                @endforelse
                            </div>
                        </div>
                        @endif
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

                            

                                <!-- Department (Inbox workspaces only) -->
                                @if($task->workspace->type->value === 'inbox')
                                <div class="py-3">
                                    <div class="flex items-center justify-between">
                                        <label class="text-sm font-medium text-base-content/70">Department</label>
                                        @if($task->canInlineEdit($user) && !$task->isClosed())
                                            <button type="button" class="btn btn-soft btn-primary btn-xs btn-circle edit-btn" onclick="toggleEdit('department')" title="Edit department">
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

// Toggle type dropdown
function toggleTypeDropdown() {
    const dropdown = document.getElementById('type-dropdown');
    if (dropdown) {
        dropdown.classList.toggle('hidden');
    }
}

// Update display with badges when checkboxes change
function updateTypeDisplay() {
    const checkboxes = document.querySelectorAll('.type-checkbox:checked');
    const container = document.getElementById('type-display-container');
    
    if (checkboxes.length > 0) {
        // Build badges HTML
        let badgesHTML = '';
        checkboxes.forEach(checkbox => {
            const label = checkbox.getAttribute('data-label');
            const icon = checkbox.getAttribute('data-icon');
            const value = checkbox.value;
            
            badgesHTML += `
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-base-200 text-sm font-medium type-badge" data-type="${value}">
                    <span class="icon-[${icon}] size-4"></span>
                    ${label}
                </span>
            `;
        });
        container.innerHTML = badgesHTML;
    } else {
        container.innerHTML = '<span class="text-base-content/50">Select types...</span>';
    }
}

async function saveType() {
    const saveBtn = document.querySelector('button[onclick="saveType()"]');
    const originalBtnText = saveBtn.innerHTML;
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<span class="loading loading-spinner loading-xs"></span> Saving...';

    const checkboxes = document.querySelectorAll('.type-checkbox:checked');
    const typeValues = Array.from(checkboxes).map(cb => cb.value);
    
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

    // Create FormData
    const formData = new FormData();
    formData.append('_method', 'PATCH');
    typeValues.forEach(value => {
        formData.append('type[]', value);
    });

    try {
        const response = await fetch('{{ route("tasks.update-type", $task) }}', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        });

        // Check if response is actually JSON
        const contentType = response.headers.get("content-type");
        
        if (!response.ok) {
            // Try to get error message
            let errorMessage = `HTTP error! status: ${response.status}`;
            
            if (contentType && contentType.includes("application/json")) {
                const errorData = await response.json();
                errorMessage = errorData.message || errorMessage;
            } else {
                // It's HTML (error page)
                const htmlText = await response.text();
                console.error('Server returned HTML error page:', htmlText);
                errorMessage = 'Server error occurred. Check console for details.';
            }
            
            throw new Error(errorMessage);
        }

        // Parse JSON response
        let data;
        if (contentType && contentType.includes("application/json")) {
            data = await response.json();
            console.log('Success:', data);
        } else {
            // Success but HTML response - might still work
            console.warn('Success but received HTML instead of JSON');
        }
        
        // Close dropdown
        document.getElementById('type-dropdown')?.classList.add('hidden');
        
        if (typeof showToast === 'function') {
            showToast('Type updated successfully');
        } else {
            alert('Type updated successfully');
        }

    } catch (error) {
        console.error('Error saving type:', error);
        alert('Error saving type: ' + error.message);
    } finally {
        saveBtn.disabled = false;
        saveBtn.innerHTML = originalBtnText;
    }
}

// Close dropdown when clicking outside
document.addEventListener('click', function(event) {
    const dropdown = document.getElementById('type-dropdown');
    const button = document.querySelector('button[onclick="toggleTypeDropdown()"]');
    
    if (dropdown && button && !dropdown.contains(event.target) && !button.contains(event.target)) {
        dropdown.classList.add('hidden');
    }
});

document.addEventListener('DOMContentLoaded', function() {
    const slider = document.getElementById('quick-progress-slider');
    const percentage = document.getElementById('quick-progress-percentage');
    
    // Update percentage display and track color
    function updateProgress() {
        const value = slider.value;
        percentage.textContent = value + '%';
        slider.style.setProperty('--progress', value + '%');
    }
    
    // Initialize
    updateProgress();
    
    // Update on input
    slider.addEventListener('input', updateProgress);
});
document.addEventListener('DOMContentLoaded', function() {
    // ========== STATUS DROPDOWN ==========
    const statusDropdownButton = document.getElementById('status-dropdown-button');
    const statusDropdownMenu = document.getElementById('status-dropdown-menu');
    const statusChevron = document.getElementById('status-chevron');
    const statusBadge = document.getElementById('status-badge');
    const statusHiddenInput = document.getElementById('quick-status-select');
    const statusOptions = document.querySelectorAll('.status-option');
    
    if (statusDropdownButton) {
        // Toggle status dropdown
        statusDropdownButton.addEventListener('click', function(e) {
            e.stopPropagation();
            toggleDropdown(statusDropdownMenu, statusChevron, 'status');
        });
        
        // Handle status selection
        statusOptions.forEach(option => {
            option.addEventListener('click', function() {
                const statusId = this.getAttribute('data-status-id');
                const statusName = this.getAttribute('data-status-name');
                const badgeClass = this.getAttribute('data-status-badge-class');
                
                // Update badge display
                statusBadge.textContent = statusName;
                statusBadge.className = 'px-2 py-1 rounded-full text-xs font-medium leading-none ' + badgeClass;
                
                // Update hidden input
                statusHiddenInput.value = statusId;
                
                // Update active state in dropdown
                statusOptions.forEach(opt => opt.classList.remove('bg-primary/10'));
                this.classList.add('bg-primary/10');
                
                // Close dropdown
                statusDropdownMenu.classList.add('hidden');
                statusChevron.classList.remove('fa-chevron-up');
                statusChevron.classList.add('fa-chevron-down');
            });
        });
    }
    
    // ========== PRIORITY DROPDOWN ==========
    const priorityDropdownButton = document.getElementById('priority-dropdown-button');
    const priorityDropdownMenu = document.getElementById('priority-dropdown-menu');
    const priorityChevron = document.getElementById('priority-chevron');
    const priorityBadge = document.getElementById('priority-badge');
    const priorityHiddenInput = document.getElementById('quick-priority-select');
    const priorityOptions = document.querySelectorAll('.priority-option');
    const priorityTypeInput = document.getElementById('priority-type');
    
    if (priorityDropdownButton) {
        // Toggle priority dropdown
        priorityDropdownButton.addEventListener('click', function(e) {
            e.stopPropagation();
            toggleDropdown(priorityDropdownMenu, priorityChevron, 'priority');
        });
        
        // Handle priority selection
        priorityOptions.forEach(option => {
            option.addEventListener('click', function() {
                const priorityId = this.getAttribute('data-priority-id');
                const priorityName = this.getAttribute('data-priority-name');
                const badgeClass = this.getAttribute('data-priority-badge-class');
                const priorityType = this.getAttribute('data-priority-type');
                
                // Update badge display
                priorityBadge.textContent = priorityName;
                priorityBadge.className = 'px-2 py-1 rounded-full text-xs font-medium leading-none ' + badgeClass;
                
                // Update hidden inputs
                priorityHiddenInput.value = priorityId;
                if (priorityTypeInput) {
                    priorityTypeInput.value = priorityType;
                }
                
                // Update active state in dropdown
                priorityOptions.forEach(opt => opt.classList.remove('bg-primary/10'));
                this.classList.add('bg-primary/10');
                
                // Close dropdown
                priorityDropdownMenu.classList.add('hidden');
                priorityChevron.classList.remove('fa-chevron-up');
                priorityChevron.classList.add('fa-chevron-down');
            });
        });
    }
    
    // ========== PROGRESS SLIDER ==========
    const progressSlider = document.getElementById('quick-progress-slider');
    const progressPercentage = document.getElementById('quick-progress-percentage');
    
    if (progressSlider && progressPercentage) {
        // Update percentage display when slider changes
        progressSlider.addEventListener('input', function() {
            progressPercentage.textContent = this.value + '%';
        });
        
        // Update percentage display when slider changes via mouse/touch
        progressSlider.addEventListener('change', function() {
            progressPercentage.textContent = this.value + '%';
        });
    }
    
    // ========== DROPDOWN UTILITY FUNCTION ==========
    function toggleDropdown(dropdownMenu, chevron, type) {
        const isHidden = dropdownMenu.classList.contains('hidden');
        
        // Close all other dropdowns first
        document.querySelectorAll('#status-dropdown-menu, #priority-dropdown-menu').forEach(menu => {
            if (menu !== dropdownMenu) {
                menu.classList.add('hidden');
            }
        });
        
        // Reset all other chevrons
        document.querySelectorAll('#status-chevron, #priority-chevron').forEach(icon => {
            if (icon !== chevron) {
                icon.classList.remove('fa-chevron-up');
                icon.classList.add('fa-chevron-down');
            }
        });
        
        // Toggle current dropdown
        dropdownMenu.classList.toggle('hidden');
        chevron.classList.toggle('fa-chevron-down');
        chevron.classList.toggle('fa-chevron-up');
    }
    
    // Close dropdowns when clicking outside
    document.addEventListener('click', function(e) {
        const statusContainer = document.getElementById('status-dropdown-container');
        const priorityContainer = document.getElementById('priority-dropdown-container');
        
        if (statusContainer && !statusContainer.contains(e.target)) {
            statusDropdownMenu?.classList.add('hidden');
            statusChevron?.classList.remove('fa-chevron-up');
            statusChevron?.classList.add('fa-chevron-down');
        }
        
        if (priorityContainer && !priorityContainer.contains(e.target)) {
            priorityDropdownMenu?.classList.add('hidden');
            priorityChevron?.classList.remove('fa-chevron-up');
            priorityChevron?.classList.add('fa-chevron-down');
        }
    });
});


// ========== TOGGLE EDIT FUNCTION (from your original code) ==========
function toggleEdit(section) {
    const editSection = document.getElementById(`quick-stats-edit`);
    if (editSection) {
        editSection.classList.toggle('hidden');
    }
}


 
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

// Main Tab Switching for Comments/Private Notes/Attachments
let currentCommentTab = 'comment';

function switchMainTab(type) {
    const tabs = document.querySelectorAll('[data-main-tab]');
    const commentContent = document.getElementById('comment-tab-content');
    const privateContent = document.getElementById('private-tab-content');
    const attachmentsContent = document.getElementById('attachments-tab-content');

    currentCommentTab = type;

    // Update active tab and colors
    tabs.forEach(tab => {
        // Remove all possible color classes first
        tab.classList.remove('bg-[#3ba5ff]', 'bg-[#3BA5FF]',  'text-white', '!text-white', 'bg-[#fffbf3]', 'text-[#ffad0d]', 'bg-[#f6f5fe]', 'text-[#5334e4]', 'bg-[#E6F0FE]', '!text-[#3ca4fc]');
        
        if (tab.dataset.mainTab === type) {
            tab.classList.add('tab-active');
            
            // Add color based on tab type
            const tabColor = tab.dataset.tabColor;
            if (tabColor === 'comment') {
                tab.classList.add('!bg-[#e6f0fe]', '!text-[#3ca4fc]', '!border-[#3ca4fc]');
            } else if (tabColor === 'private') {
                tab.classList.add('!bg-[#fffbf3]', '!text-[#ffad0d]', '!border-[#ffad0d]');
            } else if (tabColor === 'attachments') {
                tab.classList.add('!bg-[#f6f5fe]', '!text-[#5334E4]', '!border-[#5334E4]');
            }
        } else {
            tab.classList.remove('tab-active');
            tab.classList.remove('!bg-[#f6f5fe]', '!text-[#5334E4]', '!border-[#5334E4]');
            tab.classList.remove('!bg-[#fffbf3]', '!text-[#ffad0d]', '!border-[#ffad0d]');
            tab.classList.remove('!bg-[#e6f0fe]', '!text-[#3ca4fc]', '!border-[#3ca4fc]');
        }
    });

    // Show/hide content
    if (commentContent) commentContent.classList.add('hidden');
    if (privateContent) privateContent.classList.add('hidden');
    if (attachmentsContent) attachmentsContent.classList.add('hidden');

    if (type === 'comment') {
        if (commentContent) commentContent.classList.remove('hidden');
    } else if (type === 'private') {
        if (privateContent) privateContent.classList.remove('hidden');
    } else if (type === 'attachments') {
        if (attachmentsContent) attachmentsContent.classList.remove('hidden');
    }
}

// Prepare comment form submission - copy content from comment editor to final input
function prepareCommentSubmit() {
    const finalInput = document.getElementById('final_content_input');
    
    // Get content from comment editor
    const commentInput = document.getElementById('comment-editor-input');
    if (commentInput) {
        finalInput.value = commentInput.value;
    }

    // Validate that content is not empty
    if (!finalInput.value || finalInput.value.trim() === '' || finalInput.value === '<p><br></p>') {
        alert('Please enter a comment');
        return false;
    }

    return true;
}

// Prepare private note form submission - copy content from private editor to final input
function preparePrivateSubmit() {
    const finalInput = document.getElementById('final_content_input_private');
    
    // Get content from private editor
    const privateInput = document.getElementById('private-editor-input');
    if (privateInput) {
        finalInput.value = privateInput.value;
    }

    // Validate that content is not empty
    if (!finalInput.value || finalInput.value.trim() === '' || finalInput.value === '<p><br></p>') {
        alert('Please enter a private note');
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
    // Find the Save All button by its onclick attribute or by its position
    const saveBtn = document.querySelector('button[onclick="saveQuickStats()"]');
    if (!saveBtn) {
        console.error('Save button not found');
        alert('Save button not found');
        return;
    }
    
    const originalBtnText = saveBtn.innerHTML;
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<span class="loading loading-spinner loading-xs"></span> Saving...';

    const statusSelect = document.getElementById('quick-status-select');
    const statusId = statusSelect ? statusSelect.value : '';
    const prioritySelect = document.getElementById('quick-priority-select');
    const priorityValue = prioritySelect ? prioritySelect.value : '';
    
    // Get priority type from the hidden input
    const priorityTypeInput = document.getElementById('priority-type');
    const priorityType = priorityTypeInput ? priorityTypeInput.value : 'task';
    
    const progressSlider = document.getElementById('quick-progress-slider');
    const progress = progressSlider ? parseInt(progressSlider.value) : 0;

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

    try {
        const errors = [];
        let statusData = null;
        let priorityData = null;

        // Status - only update if a status is selected
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
        
        // **NEW: Update top status display after successful save**
        const statusBadge = document.getElementById('status-badge');
        const statusDisplayTop = document.getElementById('status-display-top');
        if (statusBadge && statusDisplayTop) {
            statusDisplayTop.textContent = statusBadge.textContent;
            statusDisplayTop.className = statusBadge.className;
        }
        
        // Show success message
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
        statusHtml = `<span class="badge badge-sm" style="background-color: ${statusData.background_color}20; color: ${statusData.background_color}">${statusData.name}</span>`;
    } else {
        statusHtml = '<span class="badge badge-sm badge-ghost">No Status</span>';
    }

    // Build priority badge HTML
    let priorityHtml = '';
    if (priorityData) {
        if (priorityType === 'workspace') {
            priorityHtml = `<span class="badge badge-sm" style="background-color: ${priorityData.color}15; color: ${priorityData.color}">
                <span class="icon-[tabler--flag] size-3 mr-0.5"></span>
                ${priorityData.name}
            </span>`;
        } else {
            priorityHtml = `<span class="badge badge-sm" style="background-color: ${priorityData.color}15; color: ${priorityData.color}">
                <span class="icon-[${priorityData.icon}] size-3 mr-0.5"></span>
                ${priorityData.label}
            </span>`;
        }
    } else {
        priorityHtml = '<span class="badge badge-sm badge-ghost">No Priority</span>';
    }

    // Build progress badge HTML
    const progressClass = progress === 100 ? 'badge-success' : 'badge-primary';
    const progressHtml = `<span class="badge badge-sm ${progressClass} badge-outline">${progress}%</span>`;

    displayEl.innerHTML = `
        <div class="flex items-center gap-2 flex-wrap">
            ${statusHtml}
            <span class="text-base-content/30">•</span>
            ${priorityHtml}
            <span class="text-base-content/30">•</span>
            ${progressHtml}
        </div>
    `;
}

// People (Assignee / Creator combined section)
function togglePeopleDropdown(type) {
    const dropdown = document.getElementById(`people-${type}-dropdown`);
    
    if (dropdown) {
        const isHidden = dropdown.classList.contains('hidden');
        
        // Close all other dropdowns first
        document.querySelectorAll('[id^="people-"][id$="-dropdown"]').forEach(d => {
            if (d.id !== `people-${type}-dropdown`) {
                d.classList.add('hidden');
            }
        });

        dropdown.classList.toggle('hidden');
    }
}

function selectPerson(type, id, name, avatarUrl) {
    document.getElementById(`people-${type}-input`).value = id;
    document.getElementById(`people-${type}-display`).textContent = name;
    togglePeopleDropdown(type);
}

async function savePeople() {
    // Fixed selector - find the save button by onclick attribute
    const saveBtn = document.querySelector('button[onclick="savePeople()"]');
    const originalBtnText = saveBtn.innerHTML;
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<span class="loading loading-spinner loading-xs"></span> Saving...';

    const assigneeId = document.getElementById('people-assignee-input')?.value || null;
    const creatorId = document.getElementById('people-creator-input')?.value || null;
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

    try {
        const errors = [];

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
            }
        } catch (e) {
            errors.push('Creator: Network error');
        }

        if (errors.length > 0) {
            console.error('Save errors:', errors);
            alert('Some changes failed to save: ' + errors.join(', '));
        } else {
            // Close dropdowns after successful save
            document.getElementById('people-assignee-dropdown')?.classList.add('hidden');
            document.getElementById('people-creator-dropdown')?.classList.add('hidden');
            
            if (typeof showToast === 'function') {
                showToast('Changes saved successfully');
            } else {
                alert('Changes saved successfully');
            }
        }

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
    // Updated selector - no longer inside #dates-edit
    const saveBtn = document.querySelector('button[onclick="saveDates()"]');
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
        // Removed toggleEdit('dates') since we don't have edit mode anymore
        
        // Close calendars after save
        document.getElementById('dates-due-calendar')?.classList.add('hidden');
        document.getElementById('dates-created-calendar')?.classList.add('hidden');
        
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
            <button type="submit" class="btn btn-ghost btn-xs text-error"
                    onclick="return confirm('Delete this attachment?')">
                <span class="icon-[tabler--trash] size-4"></span>
            </button>
        </form>
    ` : '';

    return `
        <div class="flex items-center gap-3 p-3 bg-base-200 rounded-lg group" data-attachment-id="${attachment.id}">
            <span class="icon-[${attachment.icon_class}] size-8 text-base-content/60"></span>
            <div class="flex-1 min-w-0">
                <p class="font-medium truncate">${escapeHtml(attachment.original_name)}</p>
                <p class="text-xs text-base-content/60">${attachment.formatted_size}</p>
            </div>
            <div class="flex gap-1">
                <a href="${attachment.download_url}" class="btn btn-ghost btn-xs">
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
