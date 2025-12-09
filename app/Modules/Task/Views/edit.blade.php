@extends('layouts.app')

@section('content')
<div class="p-4 md:p-6">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex items-center gap-2 text-sm text-base-content/60 mb-2">
                <a href="{{ route('dashboard') }}" class="hover:text-primary">Dashboard</a>
                <span class="icon-[tabler--chevron-right] size-4"></span>
                <a href="{{ route('tasks.index') }}" class="hover:text-primary">Tasks</a>
                <span class="icon-[tabler--chevron-right] size-4"></span>
                <a href="{{ route('tasks.show', $task) }}" class="hover:text-primary">{{ $task->task_number }}</a>
                <span class="icon-[tabler--chevron-right] size-4"></span>
                <span>Edit</span>
            </div>
            <h1 class="text-2xl font-bold text-base-content">Edit Task</h1>
            <p class="text-base-content/60">{{ $task->task_number }} - {{ $task->title }}</p>
        </div>

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

        @if(session('success'))
            <div class="alert alert-success mb-4">
                <span class="icon-[tabler--check] size-5"></span>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <form action="{{ route('tasks.update', $task) }}" method="POST" enctype="multipart/form-data" class="space-y-6" id="edit-task-form">
            @csrf
            @method('PUT')

            <!-- Card 1: Basic Info -->
            <div class="card bg-base-100 shadow">
                <div class="card-body">
                    <h2 class="card-title text-lg mb-4">
                        <span class="icon-[tabler--file-text] size-5"></span>
                        Task Information
                    </h2>

                    <div class="space-y-4">
                        <!-- Workspace (Read-only) -->
                        <div class="form-control">
                            <span class="label">
                                <span class="label-text font-medium">Workspace</span>
                            </span>
                            <div class="input input-bordered flex items-center gap-2 bg-base-200">
                                <span class="icon-[tabler--layout-grid] size-5 text-base-content/50"></span>
                                <span>{{ $task->workspace->name }}</span>
                            </div>
                        </div>

                        <!-- Task Name -->
                        <div class="form-control">
                            <label class="label" for="edit-task-title">
                                <span class="label-text font-medium">Task Name <span class="text-error">*</span></span>
                            </label>
                            <input type="text" name="title" id="edit-task-title" value="{{ old('title', $task->title) }}"
                                   class="input input-bordered w-full" placeholder="Enter task name" required>
                        </div>

                        <!-- Task Description (Quill Rich Text Editor) -->
                        <x-quill-editor
                            name="description"
                            id="task-description"
                            label="Description"
                            :value="old('description', $task->description)"
                            placeholder="Describe the task... You can drag & drop images here"
                            height="200px"
                        />

                        <!-- Parent Task (if subtask) -->
                        @if($task->parentTask)
                            <div class="form-control">
                                <span class="label">
                                    <span class="label-text font-medium">Parent Task</span>
                                </span>
                                <div class="input input-bordered flex items-center gap-2 bg-base-200">
                                    <span class="icon-[tabler--subtask] size-4"></span>
                                    <span class="font-mono text-sm">{{ $task->parentTask->task_number }}</span>
                                    <span>{{ Str::limit($task->parentTask->title, 30) }}</span>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Card 2: Task Settings -->
            <div class="card bg-base-100 shadow">
                <div class="card-body">
                    <h2 class="card-title text-lg mb-4">
                        <span class="icon-[tabler--settings] size-5"></span>
                        Task Settings
                    </h2>

                    <div class="space-y-4">
                        <!-- Priority -->
                        <div class="form-control">
                            <label class="label" for="edit-task-priority">
                                <span class="label-text font-medium">Priority</span>
                            </label>
                            <select name="priority" id="edit-task-priority" class="select select-bordered w-full">
                                @foreach($priorities as $priority)
                                    <option value="{{ $priority->value }}" {{ old('priority', $task->priority?->value) === $priority->value ? 'selected' : '' }}>
                                        {{ $priority->label() }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Task Type - Multi-select -->
                        <div class="form-control">
                            <label class="label" for="tasktype-search">
                                <span class="label-text font-medium">Task Type</span>
                            </label>
                            <div class="relative">
                                <div id="tasktype-select" class="min-h-12 p-2 border border-base-300 rounded-lg cursor-pointer flex flex-wrap gap-2 items-center">
                                    <div id="selected-tasktypes" class="flex flex-wrap gap-2">
                                        @foreach($task->types ?? [] as $selectedType)
                                            <span class="badge badge-primary gap-1" data-value="{{ $selectedType->value }}">
                                                <span class="icon-[{{ $selectedType->icon() }}] size-3"></span>
                                                {{ $selectedType->label() }}
                                                <button type="button" class="btn btn-ghost btn-xs btn-circle size-4" onclick="removeTasktype('{{ $selectedType->value }}', event)">
                                                    <span class="icon-[tabler--x] size-3"></span>
                                                </button>
                                            </span>
                                        @endforeach
                                    </div>
                                    <input type="text" id="tasktype-search" class="flex-1 min-w-32 bg-transparent border-0 outline-none text-sm" placeholder="{{ $task->types && count($task->types) > 0 ? 'Add more types...' : 'Search task types...' }}" autocomplete="off">
                                </div>
                                <div id="tasktype-dropdown" class="absolute z-50 w-full mt-1 bg-base-100 border border-base-300 rounded-lg shadow-lg max-h-60 overflow-y-auto hidden">
                                    @foreach($taskTypes as $type)
                                        @php $isSelected = $task->type && in_array($type->value, $task->type); @endphp
                                        <div class="tasktype-option flex items-center gap-3 p-3 hover:bg-base-200 cursor-pointer transition-colors {{ $isSelected ? 'bg-primary/10' : '' }}"
                                             data-value="{{ $type->value }}"
                                             data-label="{{ $type->label() }}"
                                             data-icon="{{ $type->icon() }}"
                                             data-search="{{ strtolower($type->label()) }}">
                                            <span class="icon-[{{ $type->icon() }}] size-5 text-base-content/70"></span>
                                            <span class="flex-1 text-sm">{{ $type->label() }}</span>
                                            <span class="tasktype-check icon-[tabler--check] size-5 text-primary {{ $isSelected ? '' : 'hidden' }}"></span>
                                        </div>
                                    @endforeach
                                    <div id="no-tasktype-results" class="p-3 text-center text-base-content/50 text-sm hidden">No task types found</div>
                                </div>
                            </div>
                            <div id="tasktype-hidden-inputs">
                                @foreach($task->type ?? [] as $typeValue)
                                    <input type="hidden" name="type[]" value="{{ $typeValue }}">
                                @endforeach
                            </div>
                        </div>

                        <!-- Status -->
                        <div class="form-control">
                            <label class="label" for="edit-task-status">
                                <span class="label-text font-medium">Status</span>
                            </label>
                            <select name="status_id" id="edit-task-status" class="select select-bordered w-full">
                                @foreach($statuses as $status)
                                    <option value="{{ $status->id }}" {{ old('status_id', $task->status_id) == $status->id ? 'selected' : '' }}>
                                        {{ $status->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Tags -->
                        <div class="form-control">
                            <label class="label" for="tag-input">
                                <span class="label-text font-medium">Tags <span class="font-normal text-base-content/50">(Optional)</span></span>
                            </label>
                            <div id="tags-container" class="flex flex-wrap gap-2 p-3 border border-base-300 rounded-lg min-h-[3rem] focus-within:ring-2 focus-within:ring-primary focus-within:ring-offset-2">
                                @foreach($task->tags as $tag)
                                    <span class="tag-item badge badge-primary gap-1" data-tag="{{ $tag->name }}">
                                        {{ $tag->name }}
                                        <button type="button" class="hover:text-primary-content/70" onclick="removeTagByName('{{ $tag->name }}')">
                                            <span class="icon-[tabler--x] size-3"></span>
                                        </button>
                                    </span>
                                @endforeach
                                <input type="text" id="tag-input" placeholder="Type and press Enter to add tags..." class="flex-1 min-w-[150px] outline-none bg-transparent text-sm">
                            </div>
                            <input type="hidden" name="tags" id="tags-hidden">
                            <span class="label">
                                <span class="label-text-alt text-base-content/50">Press Enter or comma to add a tag</span>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 3: Team Assignment -->
            <div class="card bg-base-100 shadow">
                <div class="card-body">
                    <h2 class="card-title text-lg mb-4">
                        <span class="icon-[tabler--users] size-5"></span>
                        Team Assignment
                    </h2>

                    <div class="space-y-4">
                        <!-- Assignee -->
                        <div class="form-control">
                            <label class="label" for="assignee-search">
                                <span class="label-text font-medium">Assignee <span class="font-normal text-base-content/50">(Optional)</span></span>
                            </label>
                            <div class="relative">
                                <div id="assignee-select" class="min-h-12 p-2 border border-base-300 rounded-lg cursor-pointer flex flex-wrap gap-2 items-center">
                                    <div id="selected-assignees" class="flex flex-wrap gap-2">
                                        @if($task->assignee)
                                            <span class="badge badge-primary gap-1" data-id="{{ $task->assignee->id }}">
                                                <span class="text-xs">{{ substr($task->assignee->name, 0, 1) }}</span>
                                                {{ $task->assignee->name }}
                                                <button type="button" class="btn btn-ghost btn-xs btn-circle size-4" onclick="removeAssignee('{{ $task->assignee->id }}', event)">
                                                    <span class="icon-[tabler--x] size-3"></span>
                                                </button>
                                            </span>
                                        @endif
                                    </div>
                                    <input type="text" id="assignee-search" class="flex-1 min-w-32 bg-transparent border-0 outline-none text-sm" placeholder="{{ $task->assignee ? 'Change assignee...' : 'Search and select assignee...' }}" autocomplete="off">
                                </div>
                                <div id="assignee-dropdown" class="absolute z-50 w-full mt-1 bg-base-100 border border-base-300 rounded-lg shadow-lg max-h-60 overflow-y-auto hidden">
                                    @foreach($users as $user)
                                        @php $isSelected = $task->assignee_id == $user->id; @endphp
                                        <div class="assignee-option flex items-center gap-3 p-3 hover:bg-base-200 cursor-pointer transition-colors {{ $isSelected ? 'bg-primary/10' : '' }}"
                                             data-id="{{ $user->id }}"
                                             data-name="{{ $user->name }}"
                                             data-search="{{ strtolower($user->name) }}">
                                            <div class="avatar">
                                                <div class="w-8 rounded-full">
                                                    <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" />
                                                </div>
                                            </div>
                                            <div class="flex-1">
                                                <p class="font-medium text-sm">{{ $user->name }}</p>
                                                <p class="text-xs text-base-content/50">{{ $user->email ?? '' }}</p>
                                            </div>
                                            <span class="assignee-check icon-[tabler--check] size-5 text-primary {{ $isSelected ? '' : 'hidden' }}"></span>
                                        </div>
                                    @endforeach
                                    <div id="no-assignee-results" class="p-3 text-center text-base-content/50 text-sm hidden">No members found</div>
                                </div>
                            </div>
                            <div id="assignee-hidden-inputs">
                                @if($task->assignee)
                                    <input type="hidden" name="assignee_ids[]" value="{{ $task->assignee->id }}">
                                @endif
                            </div>
                        </div>

                        <!-- Notification Settings -->
                        <div class="form-control">
                            <span class="label">
                                <span class="label-text font-medium">Notify about changes...</span>
                            </span>

                            <div class="space-y-3 p-4 border border-base-300 rounded-lg">
                                <!-- Option 1: All workspace members -->
                                <label id="notify-all-option" class="flex items-start gap-3 cursor-pointer p-2 rounded-lg hover:bg-base-200 transition-colors">
                                    <input type="radio" name="notify_option" value="all" class="radio radio-primary mt-0.5" {{ old('notify_option', 'none') === 'all' ? 'checked' : '' }}>
                                    <div class="flex-1">
                                        <span class="font-medium text-sm">All workspace members</span>
                                        <p class="text-xs text-base-content/50">Everyone in the workspace will be notified</p>
                                    </div>
                                </label>

                                <!-- Option 2: Select specific people -->
                                <div id="notify-selected-option" class="flex items-start gap-3 p-2 rounded-lg hover:bg-base-200 transition-colors">
                                    <input type="radio" name="notify_option" value="selected" id="notify-selected-radio" class="radio radio-primary mt-0.5 cursor-pointer" {{ old('notify_option') === 'selected' ? 'checked' : '' }}>
                                    <div class="flex-1">
                                        <label for="notify-selected-radio" class="font-medium text-sm cursor-pointer">Only specific people...</label>
                                        <p class="text-xs text-base-content/50 mb-2" id="selected-people-hint">Choose specific team members to notify</p>
                                        <div id="selected-watchers-preview" class="hidden">
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <div id="selected-avatars-container" class="avatar-group -space-x-3 cursor-pointer" onclick="openWatcherModal()"></div>
                                                <button type="button" onclick="openWatcherModal()" class="btn btn-ghost btn-xs gap-1">
                                                    <span class="icon-[tabler--edit] size-4"></span>
                                                    Edit
                                                </button>
                                            </div>
                                        </div>
                                        <button type="button" id="select-people-btn" onclick="openWatcherModal()" class="btn btn-outline btn-sm btn-primary gap-2 mt-1">
                                            <span class="icon-[tabler--user-plus] size-4"></span>
                                            Select People
                                        </button>
                                    </div>
                                </div>

                                <!-- Option 3: No one -->
                                <label class="flex items-start gap-3 cursor-pointer p-2 rounded-lg hover:bg-base-200 transition-colors">
                                    <input type="radio" name="notify_option" value="none" class="radio radio-primary mt-0.5" {{ old('notify_option', 'none') === 'none' ? 'checked' : '' }}>
                                    <div class="flex-1">
                                        <span class="font-medium text-sm">No one</span>
                                        <p class="text-xs text-base-content/50">Don't send any notifications</p>
                                    </div>
                                    <span class="icon-[tabler--bell-off] size-5 text-base-content/50"></span>
                                </label>
                            </div>

                            <div id="watcher-hidden-inputs"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Watcher Selection Modal -->
            <div id="watcher-modal" class="modal">
                <div class="modal-box max-w-lg bg-white">
                    <div class="flex items-center justify-between mb-5">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-primary to-secondary flex items-center justify-center">
                                <span class="icon-[tabler--users] size-5 text-white"></span>
                            </div>
                            <div>
                                <h3 class="font-bold text-lg">Select People to Notify</h3>
                                <p class="text-xs text-base-content/50">Choose who should receive notifications</p>
                            </div>
                        </div>
                        <button type="button" onclick="closeWatcherModal()" class="btn btn-ghost btn-sm btn-circle hover:bg-error/10 hover:text-error transition-colors">
                            <span class="icon-[tabler--x] size-5"></span>
                        </button>
                    </div>

                    <div class="relative mb-4">
                        <input type="text" id="watcher-modal-search" placeholder="Search team members..." class="input input-bordered w-full pl-11 focus:input-primary transition-all">
                        <span class="icon-[tabler--search] size-5 absolute left-4 top-1/2 -translate-y-1/2 text-base-content/40"></span>
                    </div>

                    <div class="flex items-center justify-between mb-3 px-1">
                        <span class="text-sm text-base-content/60">
                            <span id="modal-selected-count" class="font-semibold text-primary">0</span> selected
                        </span>
                        <div class="flex gap-1">
                            <button type="button" id="modal-select-all" class="btn btn-ghost btn-xs hover:btn-primary hover:text-primary-content gap-1">
                                <span class="icon-[tabler--checks] size-4"></span>
                                Select All
                            </button>
                            <button type="button" id="modal-clear-all" class="btn btn-ghost btn-xs hover:btn-error hover:text-error-content gap-1">
                                <span class="icon-[tabler--x] size-4"></span>
                                Clear
                            </button>
                        </div>
                    </div>

                    <div id="watcher-modal-list" class="space-y-1 max-h-72 overflow-y-auto border border-base-200 rounded-xl p-2 bg-base-100/50">
                        @php $avatarColors = ['bg-primary', 'bg-secondary', 'bg-accent', 'bg-info', 'bg-success', 'bg-warning']; @endphp
                        @foreach($users as $index => $user)
                            @php $colorClass = $avatarColors[$index % count($avatarColors)]; @endphp
                            <label class="watcher-modal-item flex items-center gap-3 p-3 rounded-xl hover:bg-primary/5 cursor-pointer transition-all duration-200 border border-transparent hover:border-primary/20" data-id="{{ $user->id }}" data-name="{{ $user->name }}" data-search="{{ strtolower($user->name) }}">
                                <input type="checkbox" class="checkbox checkbox-primary checkbox-sm watcher-modal-checkbox" value="{{ $user->id }}">
                                <div class="avatar placeholder">
                                    <div class="{{ $colorClass }} text-white w-10 rounded-full ring-2 ring-offset-2 ring-transparent transition-all">
                                        <span class="text-sm font-medium">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="font-medium text-sm truncate">{{ $user->name }}</p>
                                    <p class="text-xs text-base-content/50 truncate">{{ $user->email ?? 'Team Member' }}</p>
                                </div>
                                <span class="icon-[tabler--circle-check] size-5 text-primary opacity-0 transition-opacity watcher-check-icon"></span>
                            </label>
                        @endforeach
                    </div>

                    <div id="watcher-modal-empty" class="hidden py-10 text-center text-base-content/50">
                        <div class="w-16 h-16 rounded-full bg-base-200 flex items-center justify-center mx-auto mb-3">
                            <span class="icon-[tabler--users-group] size-8 opacity-50"></span>
                        </div>
                        <p class="font-medium">No team members found</p>
                        <p class="text-xs mt-1">Try a different search term</p>
                    </div>

                    <div class="modal-action mt-6 pt-4 border-t border-base-200">
                        <button type="button" onclick="closeWatcherModal()" class="btn btn-ghost">Cancel</button>
                        <button type="button" onclick="applyWatcherSelection()" class="btn btn-primary gap-2 shadow-lg shadow-primary/25 hover:shadow-primary/40 transition-shadow">
                            <span class="icon-[tabler--check] size-5"></span>
                            Apply Selection
                        </button>
                    </div>
                </div>
                <div class="modal-backdrop" onclick="closeWatcherModal()"></div>
            </div>

            <!-- Card 4: Schedule -->
            <div class="card bg-base-100 shadow">
                <div class="card-body">
                    <h2 class="card-title text-lg mb-4">
                        <span class="icon-[tabler--calendar] size-5"></span>
                        Schedule
                    </h2>

                    <div class="space-y-4">
                        <!-- Due Date (Optional) with Custom Date Picker -->
                        <div class="form-control">
                            <label class="label" for="due-date-display">
                                <span class="label-text font-medium">Due Date <span class="font-normal text-base-content/50">(Optional)</span></span>
                            </label>
                            <!-- Date Display Input -->
                            <div class="relative w-full">
                                <input type="text" id="due-date-display" readonly
                                       class="input input-bordered w-full cursor-pointer {{ $task->due_date ? 'input-primary border-primary' : '' }}" placeholder="Click to select date & time"
                                       onclick="toggleDueDatePicker()">
                                <span class="icon-[tabler--calendar] absolute right-3 top-1/2 -translate-y-1/2 size-5 text-base-content/50 pointer-events-none"></span>
                                <!-- Hidden input for form submission -->
                                <input type="hidden" name="due_date" id="due-date-value" value="{{ old('due_date', $task->due_date?->format('Y-m-d H:i')) }}">

                                <!-- Absolute Positioned Date Picker Dropdown -->
                                <div id="due-date-picker-dropdown" class="hidden absolute z-50 left-0 top-full mt-2 p-4 bg-white border border-base-300 rounded-xl shadow-xl w-80">
                                    <!-- Year and Month Selectors -->
                                    <div class="flex gap-2 mb-4">
                                        <select id="due-date-year" class="select select-bordered select-sm flex-1">
                                            <!-- Years will be populated by JS -->
                                        </select>
                                        <select id="due-date-month" class="select select-bordered select-sm flex-1">
                                            <option value="0">January</option>
                                            <option value="1">February</option>
                                            <option value="2">March</option>
                                            <option value="3">April</option>
                                            <option value="4">May</option>
                                            <option value="5">June</option>
                                            <option value="6">July</option>
                                            <option value="7">August</option>
                                            <option value="8">September</option>
                                            <option value="9">October</option>
                                            <option value="10">November</option>
                                            <option value="11">December</option>
                                        </select>
                                    </div>

                                    <!-- Calendar Grid -->
                                    <div id="due-date-calendar" class="mb-4">
                                        <!-- Weekday Headers -->
                                        <div class="grid grid-cols-7 gap-1 mb-2">
                                            <div class="text-center text-xs font-semibold text-base-content/50 py-1">Sun</div>
                                            <div class="text-center text-xs font-semibold text-base-content/50 py-1">Mon</div>
                                            <div class="text-center text-xs font-semibold text-base-content/50 py-1">Tue</div>
                                            <div class="text-center text-xs font-semibold text-base-content/50 py-1">Wed</div>
                                            <div class="text-center text-xs font-semibold text-base-content/50 py-1">Thu</div>
                                            <div class="text-center text-xs font-semibold text-base-content/50 py-1">Fri</div>
                                            <div class="text-center text-xs font-semibold text-base-content/50 py-1">Sat</div>
                                        </div>
                                        <!-- Days Grid -->
                                        <div id="due-date-days" class="grid grid-cols-7 gap-1">
                                            <!-- Days will be populated by JS -->
                                        </div>
                                    </div>

                                    <!-- Time Picker -->
                                    <div class="flex items-center gap-2 pt-3 border-t border-base-200">
                                        <span class="icon-[tabler--clock] size-5 text-base-content/50"></span>
                                        <input type="number" id="due-date-hour" min="1" max="12" value="12" class="input input-bordered input-sm w-16 text-center">
                                        <span class="text-lg font-bold">:</span>
                                        <input type="number" id="due-date-minute" min="0" max="59" value="00" class="input input-bordered input-sm w-16 text-center">
                                        <select id="due-date-ampm" class="select select-bordered select-sm">
                                            <option value="AM">AM</option>
                                            <option value="PM">PM</option>
                                        </select>
                                    </div>

                                    <!-- Actions -->
                                    <div class="flex justify-between items-center mt-4 pt-3 border-t border-base-200">
                                        <button type="button" onclick="clearDueDate()" class="btn btn-ghost btn-sm text-error">
                                            <span class="icon-[tabler--x] size-4"></span>
                                            Clear
                                        </button>
                                        <div class="flex gap-2">
                                            <button type="button" onclick="closeDueDatePicker()" class="btn btn-ghost btn-sm">Cancel</button>
                                            <button type="button" onclick="applyDueDate()" class="btn btn-primary btn-sm">Apply</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Estimated Hours -->
                        <div class="form-control">
                            <label class="label" for="edit-task-estimated-hours">
                                <span class="label-text font-medium">Estimated Hours <span class="font-normal text-base-content/50">(Optional)</span></span>
                            </label>
                            <input type="number" name="estimated_hours" id="edit-task-estimated-hours" value="{{ old('estimated_hours', $task->estimated_hours) }}"
                                   class="input input-bordered w-full" min="0" step="0.5" placeholder="Enter estimated hours">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="card bg-base-100 shadow">
                <div class="card-body">
                    <div class="flex flex-col sm:flex-row gap-3 justify-between">
                        <div class="flex gap-3">
                            <button type="submit" class="btn btn-primary">
                                <span class="icon-[tabler--check] size-5"></span>
                                Save Changes
                            </button>
                            <a href="{{ route('tasks.show', $task) }}" class="btn btn-ghost">
                                Cancel
                            </a>
                        </div>
                        <div>
                            @if(auth()->user()->isAdminOrHigher() || $task->created_by === auth()->id())
                                <button type="button" class="btn btn-error btn-outline"
                                        onclick="document.getElementById('deleteModal').showModal()">
                                    <span class="icon-[tabler--trash] size-5"></span>
                                    Delete Task
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <!-- Delete Confirmation Modal -->
        <dialog id="deleteModal" class="modal">
            <div class="modal-box">
                <h3 class="font-bold text-lg">Delete Task</h3>
                <p class="py-4">Are you sure you want to delete <strong>{{ $task->task_number }}</strong>? This action cannot be undone.</p>
                <div class="modal-action">
                    <form method="dialog">
                        <button class="btn btn-ghost">Cancel</button>
                    </form>
                    <form action="{{ route('tasks.destroy', $task) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-error">Delete</button>
                    </form>
                </div>
            </div>
            <form method="dialog" class="modal-backdrop">
                <button>close</button>
            </form>
        </dialog>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tags functionality
    const tagsContainer = document.getElementById('tags-container');
    const tagInput = document.getElementById('tag-input');
    const tagsHidden = document.getElementById('tags-hidden');

    // Initialize tags from existing badges
    let tags = [];
    document.querySelectorAll('.tag-item').forEach(tag => {
        tags.push(tag.dataset.tag);
    });

    // Form submit handler
    const editForm = document.getElementById('edit-task-form');
    if (editForm) {
        editForm.addEventListener('submit', function(e) {
            // Ensure tags are in JSON format
            if (tags.length > 0) {
                const tagsJson = JSON.stringify(tags.map(tag => ({ value: tag })));
                tagsHidden.value = tagsJson;
            }
        });
    }

    // Tags event listeners
    tagInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' || e.key === ',') {
            e.preventDefault();
            addTag(this.value.trim());
            this.value = '';
        }
        if (e.key === 'Backspace' && this.value === '' && tags.length > 0) {
            removeTag(tags.length - 1);
        }
    });

    tagsContainer.addEventListener('click', function() {
        tagInput.focus();
    });

    function addTag(tag) {
        if (tag && !tags.includes(tag)) {
            tags.push(tag);
            renderTags();
        }
    }

    window.removeTag = function(index) {
        tags.splice(index, 1);
        renderTags();
    };

    window.removeTagByName = function(tagName) {
        const index = tags.indexOf(tagName);
        if (index > -1) {
            tags.splice(index, 1);
            renderTags();
        }
    };

    function renderTags() {
        const tagElements = tagsContainer.querySelectorAll('.tag-item');
        tagElements.forEach(el => el.remove());

        tags.forEach((tag, index) => {
            const tagEl = document.createElement('span');
            tagEl.className = 'tag-item badge badge-primary gap-1';
            tagEl.dataset.tag = tag;
            tagEl.innerHTML = `
                ${tag}
                <button type="button" class="hover:text-primary-content/70" onclick="removeTag(${index})">
                    <span class="icon-[tabler--x] size-3"></span>
                </button>
            `;
            tagsContainer.insertBefore(tagEl, tagInput);
        });
    }

    // Multi-select Task Type
    const tasktypeSelect = document.getElementById('tasktype-select');
    const tasktypeDropdown = document.getElementById('tasktype-dropdown');
    const tasktypeSearch = document.getElementById('tasktype-search');
    const selectedTasktypesContainer = document.getElementById('selected-tasktypes');
    const tasktypeHiddenInputs = document.getElementById('tasktype-hidden-inputs');
    const tasktypeOptions = document.querySelectorAll('.tasktype-option');
    const noTasktypeResults = document.getElementById('no-tasktype-results');

    // Initialize selected task types from existing badges
    let selectedTasktypes = [];
    document.querySelectorAll('#selected-tasktypes .badge[data-value]').forEach(badge => {
        const value = badge.dataset.value;
        const option = document.querySelector(`.tasktype-option[data-value="${value}"]`);
        if (option) {
            selectedTasktypes.push({
                value: value,
                label: option.dataset.label,
                icon: option.dataset.icon
            });
        }
    });

    let tasktypeHighlightIndex = -1;

    function getVisibleTasktypeOptions() {
        return Array.from(tasktypeOptions).filter(opt => !opt.classList.contains('hidden'));
    }

    function updateTasktypeHighlight() {
        const visibleOptions = getVisibleTasktypeOptions();
        visibleOptions.forEach((opt, i) => {
            if (i === tasktypeHighlightIndex) {
                opt.classList.add('bg-base-200');
                opt.scrollIntoView({ block: 'nearest' });
            } else if (!opt.classList.contains('bg-primary/10')) {
                opt.classList.remove('bg-base-200');
            }
        });
    }

    function showTasktypeDropdown() {
        tasktypeDropdown.classList.remove('hidden');
        tasktypeSelect.classList.add('ring-2', 'ring-primary', 'ring-offset-2');
        tasktypeHighlightIndex = -1;
        updateTasktypeHighlight();
    }

    function hideTasktypeDropdown() {
        tasktypeDropdown.classList.add('hidden');
        tasktypeSelect.classList.remove('ring-2', 'ring-primary', 'ring-offset-2');
        tasktypeHighlightIndex = -1;
        tasktypeSearch.value = '';
        tasktypeOptions.forEach(option => option.classList.remove('hidden'));
        noTasktypeResults.classList.add('hidden');
    }

    tasktypeSelect.addEventListener('click', function(e) {
        if (e.target.closest('button')) return;
        if (tasktypeDropdown.classList.contains('hidden')) {
            showTasktypeDropdown();
        }
        tasktypeSearch.focus();
    });

    tasktypeSearch.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        let visibleCount = 0;

        tasktypeOptions.forEach(option => {
            const searchText = option.dataset.search;
            if (searchText.includes(searchTerm)) {
                option.classList.remove('hidden');
                visibleCount++;
            } else {
                option.classList.add('hidden');
            }
        });

        noTasktypeResults.classList.toggle('hidden', visibleCount > 0);
        tasktypeHighlightIndex = -1;
        updateTasktypeHighlight();

        if (tasktypeDropdown.classList.contains('hidden')) {
            showTasktypeDropdown();
        }
    });

    tasktypeSearch.addEventListener('keydown', function(e) {
        const visibleOptions = getVisibleTasktypeOptions();

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            if (tasktypeDropdown.classList.contains('hidden')) {
                showTasktypeDropdown();
            } else {
                tasktypeHighlightIndex = Math.min(tasktypeHighlightIndex + 1, visibleOptions.length - 1);
                updateTasktypeHighlight();
            }
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            if (!tasktypeDropdown.classList.contains('hidden')) {
                tasktypeHighlightIndex = Math.max(tasktypeHighlightIndex - 1, 0);
                updateTasktypeHighlight();
            }
        } else if (e.key === 'Enter') {
            e.preventDefault();
            if (tasktypeHighlightIndex >= 0 && visibleOptions[tasktypeHighlightIndex]) {
                visibleOptions[tasktypeHighlightIndex].click();
            }
        } else if (e.key === 'Escape') {
            e.preventDefault();
            hideTasktypeDropdown();
            tasktypeSearch.blur();
        } else if (e.key === 'Tab') {
            hideTasktypeDropdown();
        }
    });

    document.addEventListener('click', function(e) {
        if (!tasktypeSelect.contains(e.target) && !tasktypeDropdown.contains(e.target)) {
            hideTasktypeDropdown();
        }
    });

    tasktypeOptions.forEach(option => {
        option.addEventListener('click', function(e) {
            e.stopPropagation();
            const value = this.dataset.value;
            const label = this.dataset.label;
            const icon = this.dataset.icon;
            const checkIcon = this.querySelector('.tasktype-check');

            const index = selectedTasktypes.findIndex(t => t.value === value);
            if (index > -1) {
                selectedTasktypes.splice(index, 1);
                checkIcon.classList.add('hidden');
                this.classList.remove('bg-primary/10');
            } else {
                selectedTasktypes.push({ value, label, icon });
                checkIcon.classList.remove('hidden');
                this.classList.add('bg-primary/10');
            }

            this.classList.remove('bg-base-200');
            updateSelectedTasktypes();
            tasktypeSearch.focus();
        });
    });

    function updateSelectedTasktypes() {
        selectedTasktypesContainer.innerHTML = selectedTasktypes.map(t => `
            <span class="badge badge-primary gap-1" data-value="${t.value}">
                <span class="icon-[${t.icon}] size-3"></span>
                ${t.label}
                <button type="button" class="btn btn-ghost btn-xs btn-circle size-4" onclick="removeTasktype('${t.value}', event)">
                    <span class="icon-[tabler--x] size-3"></span>
                </button>
            </span>
        `).join('');

        tasktypeHiddenInputs.innerHTML = selectedTasktypes.map(t =>
            `<input type="hidden" name="type[]" value="${t.value}">`
        ).join('');

        tasktypeSearch.placeholder = selectedTasktypes.length > 0 ? 'Add more types...' : 'Search task types...';
    }

    window.removeTasktype = function(value, event) {
        event.stopPropagation();
        const index = selectedTasktypes.findIndex(t => t.value === value);
        if (index > -1) {
            selectedTasktypes.splice(index, 1);
            const option = document.querySelector(`.tasktype-option[data-value="${value}"]`);
            if (option) {
                option.querySelector('.tasktype-check').classList.add('hidden');
                option.classList.remove('bg-primary/10');
            }
            updateSelectedTasktypes();
        }
    };

    // Assignee Multi-select (single select behavior for edit)
    const assigneeSelect = document.getElementById('assignee-select');
    const assigneeDropdown = document.getElementById('assignee-dropdown');
    const assigneeSearch = document.getElementById('assignee-search');
    const selectedAssigneesContainer = document.getElementById('selected-assignees');
    const assigneeHiddenInputs = document.getElementById('assignee-hidden-inputs');
    const assigneeOptions = document.querySelectorAll('.assignee-option');
    const noAssigneeResults = document.getElementById('no-assignee-results');

    // Initialize selected assignee
    let selectedAssignees = [];
    document.querySelectorAll('#selected-assignees .badge[data-id]').forEach(badge => {
        const id = badge.dataset.id;
        const option = document.querySelector(`.assignee-option[data-id="${id}"]`);
        if (option) {
            selectedAssignees.push({
                id: id,
                name: option.dataset.name
            });
        }
    });

    let assigneeHighlightIndex = -1;

    function getVisibleAssigneeOptions() {
        return Array.from(assigneeOptions).filter(opt => !opt.classList.contains('hidden'));
    }

    function updateAssigneeHighlight() {
        const visibleOptions = getVisibleAssigneeOptions();
        visibleOptions.forEach((opt, i) => {
            if (i === assigneeHighlightIndex) {
                opt.classList.add('bg-base-200');
                opt.scrollIntoView({ block: 'nearest' });
            } else if (!opt.classList.contains('bg-primary/10')) {
                opt.classList.remove('bg-base-200');
            }
        });
    }

    function showAssigneeDropdown() {
        assigneeDropdown.classList.remove('hidden');
        assigneeSelect.classList.add('ring-2', 'ring-primary', 'ring-offset-2');
        assigneeHighlightIndex = -1;
        updateAssigneeHighlight();
    }

    function hideAssigneeDropdown() {
        assigneeDropdown.classList.add('hidden');
        assigneeSelect.classList.remove('ring-2', 'ring-primary', 'ring-offset-2');
        assigneeHighlightIndex = -1;
        assigneeSearch.value = '';
        assigneeOptions.forEach(option => option.classList.remove('hidden'));
        noAssigneeResults.classList.add('hidden');
    }

    assigneeSelect.addEventListener('click', function(e) {
        if (e.target.closest('button')) return;
        if (assigneeDropdown.classList.contains('hidden')) {
            showAssigneeDropdown();
        }
        assigneeSearch.focus();
    });

    assigneeSearch.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        let visibleCount = 0;

        assigneeOptions.forEach(option => {
            const name = option.dataset.search;
            if (name.includes(searchTerm)) {
                option.classList.remove('hidden');
                visibleCount++;
            } else {
                option.classList.add('hidden');
            }
        });

        noAssigneeResults.classList.toggle('hidden', visibleCount > 0);
        assigneeHighlightIndex = -1;
        updateAssigneeHighlight();

        if (assigneeDropdown.classList.contains('hidden')) {
            showAssigneeDropdown();
        }
    });

    assigneeSearch.addEventListener('keydown', function(e) {
        const visibleOptions = getVisibleAssigneeOptions();

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            if (assigneeDropdown.classList.contains('hidden')) {
                showAssigneeDropdown();
            } else {
                assigneeHighlightIndex = Math.min(assigneeHighlightIndex + 1, visibleOptions.length - 1);
                updateAssigneeHighlight();
            }
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            if (!assigneeDropdown.classList.contains('hidden')) {
                assigneeHighlightIndex = Math.max(assigneeHighlightIndex - 1, 0);
                updateAssigneeHighlight();
            }
        } else if (e.key === 'Enter') {
            e.preventDefault();
            if (assigneeHighlightIndex >= 0 && visibleOptions[assigneeHighlightIndex]) {
                visibleOptions[assigneeHighlightIndex].click();
            }
        } else if (e.key === 'Escape') {
            e.preventDefault();
            hideAssigneeDropdown();
            assigneeSearch.blur();
        } else if (e.key === 'Tab') {
            hideAssigneeDropdown();
        }
    });

    document.addEventListener('click', function(e) {
        if (!assigneeSelect.contains(e.target) && !assigneeDropdown.contains(e.target)) {
            hideAssigneeDropdown();
        }
    });

    assigneeOptions.forEach(option => {
        option.addEventListener('click', function(e) {
            e.stopPropagation();
            const id = this.dataset.id;
            const name = this.dataset.name;
            const checkIcon = this.querySelector('.assignee-check');

            // Clear previous selections (single select)
            assigneeOptions.forEach(opt => {
                opt.querySelector('.assignee-check').classList.add('hidden');
                opt.classList.remove('bg-primary/10');
            });

            const index = selectedAssignees.findIndex(a => a.id === id);
            if (index > -1) {
                // Deselect
                selectedAssignees = [];
            } else {
                // Select (replace existing)
                selectedAssignees = [{ id, name }];
                checkIcon.classList.remove('hidden');
                this.classList.add('bg-primary/10');
            }

            this.classList.remove('bg-base-200');
            updateSelectedAssignees();
            hideAssigneeDropdown();
        });
    });

    function updateSelectedAssignees() {
        selectedAssigneesContainer.innerHTML = selectedAssignees.map(a => `
            <span class="badge badge-primary gap-1" data-id="${a.id}">
                <span class="text-xs">${a.name.charAt(0)}</span>
                ${a.name}
                <button type="button" class="btn btn-ghost btn-xs btn-circle size-4" onclick="removeAssignee('${a.id}', event)">
                    <span class="icon-[tabler--x] size-3"></span>
                </button>
            </span>
        `).join('');

        assigneeHiddenInputs.innerHTML = selectedAssignees.map(a =>
            `<input type="hidden" name="assignee_ids[]" value="${a.id}">`
        ).join('');

        assigneeSearch.placeholder = selectedAssignees.length > 0 ? 'Change assignee...' : 'Search and select assignee...';
    }

    window.removeAssignee = function(id, event) {
        event.stopPropagation();
        selectedAssignees = [];
        const option = document.querySelector(`.assignee-option[data-id="${id}"]`);
        if (option) {
            option.querySelector('.assignee-check').classList.add('hidden');
            option.classList.remove('bg-primary/10');
        }
        updateSelectedAssignees();
    };

    // ===== Date Picker =====
    const dueDateDisplay = document.getElementById('due-date-display');
    const dueDateValue = document.getElementById('due-date-value');
    const dueDatePickerDropdown = document.getElementById('due-date-picker-dropdown');
    const dueDateDays = document.getElementById('due-date-days');
    const dueDateYear = document.getElementById('due-date-year');
    const dueDateMonth = document.getElementById('due-date-month');
    const dueDateHour = document.getElementById('due-date-hour');
    const dueDateMinute = document.getElementById('due-date-minute');
    const dueDateAmpm = document.getElementById('due-date-ampm');

    let selectedDate = null;
    let currentViewYear = new Date().getFullYear();
    let currentViewMonth = new Date().getMonth();

    // Populate year dropdown
    const currentYear = new Date().getFullYear();
    for (let y = currentYear; y <= currentYear + 10; y++) {
        const option = document.createElement('option');
        option.value = y;
        option.textContent = y;
        dueDateYear.appendChild(option);
    }

    function renderCalendar() {
        const today = new Date();
        today.setHours(0, 0, 0, 0);

        dueDateYear.value = currentViewYear;
        dueDateMonth.value = currentViewMonth;

        const year = currentViewYear;
        const month = currentViewMonth;

        const firstDay = new Date(year, month, 1);
        const startDayOfWeek = firstDay.getDay();
        const lastDay = new Date(year, month + 1, 0);
        const daysInMonth = lastDay.getDate();
        const prevMonthLastDay = new Date(year, month, 0).getDate();

        dueDateDays.innerHTML = '';

        // Previous month days
        for (let i = startDayOfWeek - 1; i >= 0; i--) {
            const day = prevMonthLastDay - i;
            const btn = createDayButton(day, true, false, false);
            dueDateDays.appendChild(btn);
        }

        // Current month days
        for (let day = 1; day <= daysInMonth; day++) {
            const date = new Date(year, month, day);
            const isToday = date.getTime() === today.getTime();
            const isPast = date < today;
            const isSelected = selectedDate &&
                selectedDate.getFullYear() === year &&
                selectedDate.getMonth() === month &&
                selectedDate.getDate() === day;

            const btn = createDayButton(day, false, isPast, isSelected, isToday);
            const dayNum = day;
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                if (!isPast) {
                    selectDay(dayNum);
                }
            });
            dueDateDays.appendChild(btn);
        }

        // Next month days
        const totalCells = Math.ceil((startDayOfWeek + daysInMonth) / 7) * 7;
        const nextMonthDays = totalCells - (startDayOfWeek + daysInMonth);
        for (let day = 1; day <= nextMonthDays; day++) {
            const btn = createDayButton(day, true, false, false);
            dueDateDays.appendChild(btn);
        }
    }

    function createDayButton(day, isOtherMonth, isPast, isSelected, isToday = false) {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.textContent = day;

        let classes = 'w-9 h-9 flex items-center justify-center rounded-lg text-sm font-medium transition-all ';

        if (isSelected) {
            classes += 'bg-primary text-white shadow-lg shadow-primary/40';
        } else if (isToday) {
            classes += 'border-2 border-primary bg-primary/5';
        } else if (isOtherMonth || isPast) {
            classes += 'text-base-content/30';
            if (isPast && !isOtherMonth) classes += ' cursor-not-allowed';
        } else {
            classes += 'hover:bg-primary/10';
        }

        btn.className = classes;
        return btn;
    }

    function selectDay(day) {
        selectedDate = new Date(currentViewYear, currentViewMonth, day);
        renderCalendar();
    }

    window.toggleDueDatePicker = function() {
        if (dueDatePickerDropdown.classList.contains('hidden')) {
            dueDatePickerDropdown.classList.remove('hidden');
            renderCalendar();
        } else {
            dueDatePickerDropdown.classList.add('hidden');
        }
    };

    window.closeDueDatePicker = function() {
        dueDatePickerDropdown.classList.add('hidden');
    };

    window.clearDueDate = function() {
        selectedDate = null;
        dueDateDisplay.value = '';
        dueDateValue.value = '';
        dueDateHour.value = '12';
        dueDateMinute.value = '00';
        dueDateAmpm.value = 'AM';
        dueDateDisplay.classList.remove('input-primary', 'border-primary');
        renderCalendar();
        closeDueDatePicker();
    };

    window.applyDueDate = function() {
        if (!selectedDate) {
            closeDueDatePicker();
            return;
        }

        let hours = parseInt(dueDateHour.value) || 12;
        const minutes = parseInt(dueDateMinute.value) || 0;
        const ampm = dueDateAmpm.value;

        if (hours < 1) hours = 1;
        if (hours > 12) hours = 12;

        if (ampm === 'PM' && hours !== 12) {
            hours += 12;
        } else if (ampm === 'AM' && hours === 12) {
            hours = 0;
        }

        selectedDate.setHours(hours, minutes, 0, 0);

        const options = {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: 'numeric',
            minute: '2-digit',
            hour12: true
        };
        dueDateDisplay.value = selectedDate.toLocaleDateString('en-US', options);

        const year = selectedDate.getFullYear();
        const month = String(selectedDate.getMonth() + 1).padStart(2, '0');
        const day = String(selectedDate.getDate()).padStart(2, '0');
        const hour24 = String(selectedDate.getHours()).padStart(2, '0');
        const min = String(selectedDate.getMinutes()).padStart(2, '0');
        dueDateValue.value = `${year}-${month}-${day} ${hour24}:${min}`;

        dueDateDisplay.classList.add('input-primary', 'border-primary');
        closeDueDatePicker();
    };

    dueDateYear.addEventListener('change', function() {
        currentViewYear = parseInt(this.value);
        renderCalendar();
    });

    dueDateMonth.addEventListener('change', function() {
        currentViewMonth = parseInt(this.value);
        renderCalendar();
    });

    document.addEventListener('click', function(e) {
        if (dueDatePickerDropdown.contains(e.target) || dueDateDisplay.contains(e.target)) {
            return;
        }
        if (!dueDatePickerDropdown.classList.contains('hidden')) {
            closeDueDatePicker();
        }
    });

    // Initialize with existing value
    if (dueDateValue.value) {
        const parsed = new Date(dueDateValue.value);
        if (!isNaN(parsed)) {
            selectedDate = parsed;
            currentViewYear = parsed.getFullYear();
            currentViewMonth = parsed.getMonth();

            let hours = parsed.getHours();
            const minutes = parsed.getMinutes();
            dueDateAmpm.value = hours >= 12 ? 'PM' : 'AM';
            hours = hours % 12 || 12;
            dueDateHour.value = hours;
            dueDateMinute.value = String(minutes).padStart(2, '0');

            const options = {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: 'numeric',
                minute: '2-digit',
                hour12: true
            };
            dueDateDisplay.value = parsed.toLocaleDateString('en-US', options);
        }
    }

    // ===== Watcher Modal =====
    const watcherModal = document.getElementById('watcher-modal');
    const watcherModalSearch = document.getElementById('watcher-modal-search');
    const watcherModalList = document.getElementById('watcher-modal-list');
    const watcherModalEmpty = document.getElementById('watcher-modal-empty');
    const watcherModalItems = document.querySelectorAll('.watcher-modal-item');
    const watcherHiddenInputs = document.getElementById('watcher-hidden-inputs');
    const selectedAvatarsContainer = document.getElementById('selected-avatars-container');
    const selectedWatchersPreview = document.getElementById('selected-watchers-preview');
    const selectPeopleBtn = document.getElementById('select-people-btn');
    const notifySelectedRadio = document.getElementById('notify-selected-radio');
    const modalSelectedCount = document.getElementById('modal-selected-count');
    const modalSelectAll = document.getElementById('modal-select-all');
    const modalClearAll = document.getElementById('modal-clear-all');

    let selectedWatchers = [];
    const avatarColors = ['bg-primary', 'bg-secondary', 'bg-accent', 'bg-info', 'bg-success', 'bg-warning'];

    window.openWatcherModal = function() {
        notifySelectedRadio.checked = true;
        watcherModal.classList.add('modal-open');

        watcherModalItems.forEach(item => {
            const checkbox = item.querySelector('.watcher-modal-checkbox');
            const checkIcon = item.querySelector('.watcher-check-icon');
            const id = item.dataset.id;
            const isSelected = selectedWatchers.some(w => w.id === id);

            checkbox.checked = isSelected;
            if (isSelected) {
                checkIcon.classList.remove('opacity-0');
                checkIcon.classList.add('opacity-100');
                item.classList.add('bg-primary/5', 'border-primary/20');
            } else {
                checkIcon.classList.add('opacity-0');
                checkIcon.classList.remove('opacity-100');
                item.classList.remove('bg-primary/5', 'border-primary/20');
            }
        });

        updateModalSelectedCount();
    };

    window.closeWatcherModal = function() {
        watcherModal.classList.remove('modal-open');
        watcherModalSearch.value = '';
        watcherModalItems.forEach(item => item.classList.remove('hidden'));
        watcherModalEmpty.classList.add('hidden');
    };

    window.applyWatcherSelection = function() {
        selectedWatchers = [];
        watcherModalItems.forEach(item => {
            const checkbox = item.querySelector('.watcher-modal-checkbox');
            if (checkbox.checked) {
                selectedWatchers.push({
                    id: item.dataset.id,
                    name: item.dataset.name
                });
            }
        });

        updateWatcherPreview();
        closeWatcherModal();
    };

    function updateWatcherPreview() {
        watcherHiddenInputs.innerHTML = selectedWatchers.map(w =>
            `<input type="hidden" name="watcher_ids[]" value="${w.id}">`
        ).join('');

        if (selectedWatchers.length > 0) {
            selectedWatchersPreview.classList.remove('hidden');
            selectPeopleBtn.classList.add('hidden');

            selectedAvatarsContainer.innerHTML = selectedWatchers.slice(0, 5).map((w, i) => `
                <div class="avatar placeholder">
                    <div class="${avatarColors[i % avatarColors.length]} text-white w-8 rounded-full ring-2 ring-white">
                        <span class="text-xs">${w.name.charAt(0).toUpperCase()}</span>
                    </div>
                </div>
            `).join('');

            if (selectedWatchers.length > 5) {
                selectedAvatarsContainer.innerHTML += `
                    <div class="avatar placeholder">
                        <div class="bg-base-300 text-base-content w-8 rounded-full ring-2 ring-white">
                            <span class="text-xs">+${selectedWatchers.length - 5}</span>
                        </div>
                    </div>
                `;
            }
        } else {
            selectedWatchersPreview.classList.add('hidden');
            selectPeopleBtn.classList.remove('hidden');
        }
    }

    function updateModalSelectedCount() {
        const count = document.querySelectorAll('.watcher-modal-checkbox:checked').length;
        modalSelectedCount.textContent = count;
    }

    watcherModalSearch.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        let visibleCount = 0;

        watcherModalItems.forEach(item => {
            const name = item.dataset.search;
            if (name.includes(searchTerm)) {
                item.classList.remove('hidden');
                visibleCount++;
            } else {
                item.classList.add('hidden');
            }
        });

        watcherModalEmpty.classList.toggle('hidden', visibleCount > 0);
    });

    watcherModalItems.forEach(item => {
        item.addEventListener('click', function(e) {
            if (e.target.type !== 'checkbox') {
                const checkbox = this.querySelector('.watcher-modal-checkbox');
                checkbox.checked = !checkbox.checked;
            }

            const checkbox = this.querySelector('.watcher-modal-checkbox');
            const checkIcon = this.querySelector('.watcher-check-icon');

            if (checkbox.checked) {
                checkIcon.classList.remove('opacity-0');
                checkIcon.classList.add('opacity-100');
                this.classList.add('bg-primary/5', 'border-primary/20');
            } else {
                checkIcon.classList.add('opacity-0');
                checkIcon.classList.remove('opacity-100');
                this.classList.remove('bg-primary/5', 'border-primary/20');
            }

            updateModalSelectedCount();
        });
    });

    modalSelectAll.addEventListener('click', function() {
        watcherModalItems.forEach(item => {
            if (!item.classList.contains('hidden')) {
                const checkbox = item.querySelector('.watcher-modal-checkbox');
                const checkIcon = item.querySelector('.watcher-check-icon');
                checkbox.checked = true;
                checkIcon.classList.remove('opacity-0');
                checkIcon.classList.add('opacity-100');
                item.classList.add('bg-primary/5', 'border-primary/20');
            }
        });
        updateModalSelectedCount();
    });

    modalClearAll.addEventListener('click', function() {
        watcherModalItems.forEach(item => {
            const checkbox = item.querySelector('.watcher-modal-checkbox');
            const checkIcon = item.querySelector('.watcher-check-icon');
            checkbox.checked = false;
            checkIcon.classList.add('opacity-0');
            checkIcon.classList.remove('opacity-100');
            item.classList.remove('bg-primary/5', 'border-primary/20');
        });
        updateModalSelectedCount();
    });

    // Initialize calendar
    renderCalendar();
});
</script>
@endpush
@endsection
