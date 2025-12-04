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
                <span>Add Task</span>
            </div>
            <h1 class="text-2xl font-bold text-base-content">Add New Task</h1>
            <p class="text-base-content/60">Create a new task for your team</p>
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

        <form action="{{ route('tasks.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <!-- Card 1: Basic Info -->
            <div class="card bg-base-100 shadow">
                <div class="card-body">
                    <h2 class="card-title text-lg mb-4">
                        <span class="icon-[tabler--file-text] size-5"></span>
                        Task Information
                    </h2>

                    <div class="space-y-4">
                        <!-- Workspace (Required) - Searchable -->
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium">Workspace <span class="text-error">*</span></span>
                            </label>
                            <div class="relative">
                                <div id="workspace-select-container" class="min-h-12 p-2 border border-base-300 rounded-lg cursor-pointer flex items-center gap-2">
                                    <span class="icon-[tabler--layout-grid] size-5 text-base-content/50"></span>
                                    <input type="text" id="workspace-search" class="flex-1 bg-transparent border-0 outline-none text-sm" placeholder="Search and select workspace..." autocomplete="off">
                                    <span id="workspace-clear" class="icon-[tabler--x] size-4 text-base-content/50 hover:text-error cursor-pointer hidden" onclick="clearWorkspace(event)"></span>
                                    <span id="workspace-chevron" class="icon-[tabler--chevron-down] size-4 text-base-content/50"></span>
                                </div>
                                <div id="workspace-dropdown" class="absolute z-50 w-full mt-1 bg-base-100 border border-base-300 rounded-lg shadow-lg max-h-60 overflow-y-auto hidden">
                                    @foreach($workspaces as $workspace)
                                        <div class="workspace-option flex items-center justify-between p-3 hover:bg-base-200 cursor-pointer transition-colors"
                                             data-id="{{ $workspace->id }}"
                                             data-name="{{ $workspace->name }}"
                                             data-search="{{ strtolower($workspace->name) }}"
                                             data-statuses='@json($workspace->workflow?->statuses ?? [])'>
                                            <span class="text-sm">{{ $workspace->name }}</span>
                                            <span class="workspace-check icon-[tabler--check] size-5 text-primary hidden"></span>
                                        </div>
                                    @endforeach
                                    <div id="no-workspace-results" class="p-3 text-center text-base-content/50 text-sm hidden">No workspaces found</div>
                                </div>
                            </div>
                            <!-- Hidden input for form submission -->
                            <input type="hidden" name="workspace_id" id="workspace-hidden-input" required>
                        </div>

                        <!-- Task Name -->
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium">Task Name <span class="text-error">*</span></span>
                            </label>
                            <input type="text" name="title" value="{{ old('title') }}"
                                   class="input input-bordered w-full" placeholder="Enter task name" required>
                        </div>

                        <!-- Task Description (Quill Rich Text Editor) -->
                        <x-quill-editor
                            name="description"
                            id="task-description"
                            label="Description"
                            :value="old('description')"
                            placeholder="Describe the task... You can drag & drop images here"
                            height="200px"
                        />

                        <!-- File Upload -->
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium">Attachments <span class="font-normal text-base-content/50">(Optional)</span></span>
                            </label>
                            <div id="file-drop-zone" class="border-2 border-dashed border-base-300 rounded-lg p-6 text-center hover:border-primary transition-colors cursor-pointer">
                                <input type="file" name="files[]" id="file-input" multiple class="hidden" accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.txt,.zip">
                                <div class="flex flex-col items-center gap-2">
                                    <span class="icon-[tabler--cloud-upload] size-10 text-base-content/40"></span>
                                    <p class="text-sm text-base-content/60">
                                        <span class="text-primary font-medium">Click to upload</span> or drag and drop
                                    </p>
                                    <p class="text-xs text-base-content/40">PNG, JPG, PDF, DOC up to 10MB each</p>
                                </div>
                            </div>
                            <div id="file-preview" class="mt-3 space-y-2 hidden"></div>
                        </div>

                        <!-- Task Visibility -->
                        <div class="form-control">
{{--
                            <label class="label">
                                <span class="label-text font-medium">Task Visibility</span>
                            </label>
--}}
                            <div class="flex items-center gap-4 p-3 border border-base-300 rounded-lg">
                                <label class="flex items-center gap-3 cursor-pointer flex-1">
                                    <input type="checkbox" name="is_private" value="1" class="toggle toggle-primary" {{ old('is_private') ? 'checked' : '' }}>
                                    <div>
                                        <span class="font-medium" id="visibility-label">{{ old('is_private') ? 'Private Task' : 'Public Task' }}</span>
                                        <p class="text-xs text-base-content/50" id="visibility-description">{{ old('is_private') ? 'Only you and assigned members can see this task' : 'All workspace members can see this task' }}</p>
                                    </div>
                                </label>
                                <span id="visibility-icon" class="icon-[tabler--{{ old('is_private') ? 'lock' : 'world' }}] size-6 text-base-content/50"></span>
                            </div>
                        </div>


                        <!-- Parent Task (for subtasks) -->
                        @if($parentTask)
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-medium">Parent Task</span>
                                </label>
                                <input type="hidden" name="parent_task_id" value="{{ $parentTask->id }}">
                                <div class="input input-bordered flex items-center gap-2 bg-base-200">
                                    <span class="icon-[tabler--subtask] size-4"></span>
                                    <span class="font-mono text-sm">{{ $parentTask->task_number }}</span>
                                    <span>{{ Str::limit($parentTask->title, 30) }}</span>
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
                            <label class="label">
                                <span class="label-text font-medium">Priority</span>
                            </label>
                            <select name="priority" class="select select-bordered w-full">
                                @foreach($priorities as $priority)
                                    <option value="{{ $priority->value }}" {{ old('priority', 'medium') === $priority->value ? 'selected' : '' }}>
                                        {{ $priority->label() }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Task Type - Multi-select -->
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium">Task Type</span>
                            </label>
                            <div class="relative">
                                <div id="tasktype-select" class="min-h-12 p-2 border border-base-300 rounded-lg cursor-pointer flex flex-wrap gap-2 items-center">
                                    <div id="selected-tasktypes" class="flex flex-wrap gap-2">
                                        <!-- Selected task types will be shown here -->
                                    </div>
                                    <input type="text" id="tasktype-search" class="flex-1 min-w-32 bg-transparent border-0 outline-none text-sm" placeholder="Search task types..." autocomplete="off">
                                </div>
                                <div id="tasktype-dropdown" class="absolute z-50 w-full mt-1 bg-base-100 border border-base-300 rounded-lg shadow-lg max-h-60 overflow-y-auto hidden">
                                    @foreach($taskTypes as $type)
                                        <div class="tasktype-option flex items-center gap-3 p-3 hover:bg-base-200 cursor-pointer transition-colors"
                                             data-value="{{ $type->value }}"
                                             data-label="{{ $type->label() }}"
                                             data-icon="{{ $type->icon() }}"
                                             data-search="{{ strtolower($type->label()) }}">
                                            <span class="icon-[{{ $type->icon() }}] size-5 text-base-content/70"></span>
                                            <span class="flex-1 text-sm">{{ $type->label() }}</span>
                                            <span class="tasktype-check icon-[tabler--check] size-5 text-primary hidden"></span>
                                        </div>
                                    @endforeach
                                    <div id="no-tasktype-results" class="p-3 text-center text-base-content/50 text-sm hidden">No task types found</div>
                                </div>
                            </div>
                            <!-- Hidden inputs for form submission -->
                            <div id="tasktype-hidden-inputs"></div>
                        </div>

                        <!-- Status (Dynamic based on workspace) -->
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium">Status</span>
                            </label>
                            <select name="status_id" id="status-select" class="select select-bordered w-full">
                                <option value="">Select workspace first</option>
                            </select>
                            <p id="status-hint" class="text-xs text-base-content/50 mt-1">Select a workspace to see available statuses</p>
                        </div>

                        <!-- Tags (Optional) -->
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium">Tags <span class="font-normal text-base-content/50">(Optional)</span></span>
                            </label>
                            <div id="tags-container" class="flex flex-wrap gap-2 p-3 border border-base-300 rounded-lg min-h-[3rem] focus-within:ring-2 focus-within:ring-primary focus-within:ring-offset-2">
                                <input type="text" id="tag-input" placeholder="Type and press Enter to add tags..." class="flex-1 min-w-[150px] outline-none bg-transparent text-sm">
                            </div>
                            <input type="hidden" name="tags" id="tags-hidden">
                            <label class="label">
                                <span class="label-text-alt text-base-content/50">Press Enter or comma to add a tag</span>
                            </label>
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
                        <!-- Assignee (Optional) - Multi-select -->
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium">Assignee <span class="font-normal text-base-content/50">(Optional)</span></span>
                            </label>
                            <div class="relative">
                                <div id="assignee-select" class="min-h-12 p-2 border border-base-300 rounded-lg cursor-pointer flex flex-wrap gap-2 items-center">
                                    <div id="selected-assignees" class="flex flex-wrap gap-2">
                                        <!-- Selected assignees will be shown here -->
                                    </div>
                                    <input type="text" id="assignee-search" class="flex-1 min-w-32 bg-transparent border-0 outline-none text-sm" placeholder="Search and select assignees..." autocomplete="off">
                                </div>
                                <div id="assignee-dropdown" class="absolute z-50 w-full mt-1 bg-base-100 border border-base-300 rounded-lg shadow-lg max-h-60 overflow-y-auto hidden">
                                    @foreach($users as $user)
                                        <div class="assignee-option flex items-center gap-3 p-3 hover:bg-base-200 cursor-pointer transition-colors" data-id="{{ $user->id }}" data-name="{{ $user->name }}" data-search="{{ strtolower($user->name) }}">
                                            <div class="avatar placeholder">
                                                <div class="bg-primary text-primary-content w-8 rounded-full">
                                                    <span class="text-sm">{{ substr($user->name, 0, 1) }}</span>
                                                </div>
                                            </div>
                                            <div class="flex-1">
                                                <p class="font-medium text-sm">{{ $user->name }}</p>
                                                <p class="text-xs text-base-content/50">{{ $user->email ?? '' }}</p>
                                            </div>
                                            <span class="assignee-check icon-[tabler--check] size-5 text-primary hidden"></span>
                                        </div>
                                    @endforeach
                                    <div id="no-assignee-results" class="p-3 text-center text-base-content/50 text-sm hidden">No members found</div>
                                </div>
                            </div>
                            <!-- Hidden inputs for form submission -->
                            <div id="assignee-hidden-inputs"></div>
                        </div>

                        <!-- Notification Settings -->
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium">When I post this, notify...</span>
                            </label>

                            <!-- Notification Options -->
                            <div class="space-y-3 p-4 border border-base-300 rounded-lg">
                                <!-- Option 1: All workspace members (only for public tasks) -->
                                <label id="notify-all-option" class="flex items-start gap-3 cursor-pointer p-2 rounded-lg hover:bg-base-200 transition-colors">
                                    <input type="radio" name="notify_option" value="all" class="radio radio-primary mt-0.5" {{ old('notify_option', 'all') === 'all' ? 'checked' : '' }}>
                                    <div class="flex-1">
                                        <span class="font-medium text-sm">All people who can see this workspace</span>
                                        <p class="text-xs text-base-content/50 mb-2">Everyone in the workspace will be notified about this task</p>
                                        <!-- Avatar stack for all team members -->
                                        <div class="flex items-center gap-1">
                                            <div class="avatar-group -space-x-3">
                                                @foreach($users->take(8) as $user)
                                                    <div class="avatar placeholder border-2 border-base-100" title="{{ $user->name }}">
                                                        <div class="bg-primary text-primary-content w-8 rounded-full">
                                                            <span class="text-xs">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                                                        </div>
                                                    </div>
                                                @endforeach
                                                @if($users->count() > 8)
                                                    <div class="avatar placeholder border-2 border-base-100">
                                                        <div class="bg-neutral text-neutral-content w-8 rounded-full">
                                                            <span class="text-xs">+{{ $users->count() - 8 }}</span>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                            <span class="text-xs text-base-content/50 ml-2">{{ $users->count() }} people</span>
                                        </div>
                                    </div>
                                </label>

                                <!-- Option 2: Select specific people -->
                                <div id="notify-selected-option" class="flex items-start gap-3 p-2 rounded-lg hover:bg-base-200 transition-colors">
                                    <input type="radio" name="notify_option" value="selected" id="notify-selected-radio" class="radio radio-primary mt-0.5 cursor-pointer" {{ old('notify_option') === 'selected' ? 'checked' : '' }}>
                                    <div class="flex-1">
                                        <label for="notify-selected-radio" class="font-medium text-sm cursor-pointer">Only the people I select...</label>
                                        <p class="text-xs text-base-content/50 mb-2" id="selected-people-hint">Choose specific team members to notify</p>
                                        <!-- Selected people avatars (clickable to open modal) -->
                                        <div id="selected-watchers-preview" class="hidden">
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <div id="selected-avatars-container" class="avatar-group -space-x-3 cursor-pointer" onclick="openWatcherModal()">
                                                    <!-- Avatars will be added here dynamically -->
                                                </div>
                                                <button type="button" onclick="openWatcherModal()" class="btn btn-ghost btn-xs gap-1">
                                                    <span class="icon-[tabler--edit] size-4"></span>
                                                    Edit
                                                </button>
                                            </div>
                                        </div>
                                        <!-- Button to open modal when no one selected -->
                                        <button type="button" id="select-people-btn" onclick="openWatcherModal()" class="btn btn-outline btn-sm btn-primary gap-2 mt-1">
                                            <span class="icon-[tabler--user-plus] size-4"></span>
                                            Select People
                                        </button>
                                    </div>
                                </div>

                                <!-- Option 3: No one -->
                                <label class="flex items-start gap-3 cursor-pointer p-2 rounded-lg hover:bg-base-200 transition-colors">
                                    <input type="radio" name="notify_option" value="none" class="radio radio-primary mt-0.5" {{ old('notify_option') === 'none' ? 'checked' : '' }}>
                                    <div class="flex-1">
                                        <span class="font-medium text-sm">No one</span>
                                        <p class="text-xs text-base-content/50">Don't send any notifications for this task</p>
                                    </div>
                                    <span class="icon-[tabler--bell-off] size-5 text-base-content/50"></span>
                                </label>
                            </div>

                            <!-- Hidden inputs for watchers -->
                            <div id="watcher-hidden-inputs">
                                @foreach($users as $user)
                                    @if(in_array($user->id, old('watcher_ids', [])))
                                        <input type="hidden" name="watcher_ids[]" value="{{ $user->id }}" class="watcher-hidden-input" data-id="{{ $user->id }}">
                                    @endif
                                @endforeach
                            </div>

                            <!-- Private task notice -->
                            <div id="private-task-notice" class="mt-3 p-3 bg-warning/10 border border-warning/30 rounded-lg hidden">
                                <div class="flex items-start gap-2">
                                    <span class="icon-[tabler--lock] size-5 text-warning shrink-0 mt-0.5"></span>
                                    <div>
                                        <p class="text-sm font-medium text-warning">Private Task</p>
                                        <p class="text-xs text-base-content/60">Only assignees and selected people can see and be notified about this task. "All workspace members" option is not available for private tasks.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
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
                            <label class="label">
                                <span class="label-text font-medium">Due Date <span class="font-normal text-base-content/50">(Optional)</span></span>
                            </label>
                            <!-- Date Display Input -->
                            <div class="relative w-full">
                                <input type="text" id="due-date-display" readonly
                                       class="input input-bordered w-full cursor-pointer" placeholder="Click to select date & time"
                                       onclick="toggleDueDatePicker()">
                                <span class="icon-[tabler--calendar] absolute right-3 top-1/2 -translate-y-1/2 size-5 text-base-content/50 pointer-events-none"></span>
                                <!-- Hidden input for form submission -->
                                <input type="hidden" name="due_date" id="due-date-value" value="{{ old('due_date') }}">

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
                            <label class="label">
                                <span class="label-text font-medium">Estimated Hours <span class="font-normal text-base-content/50">(Optional)</span></span>
                            </label>
                            <input type="number" name="estimated_hours" value="{{ old('estimated_hours') }}"
                                   class="input input-bordered w-full" min="0" step="0.5" placeholder="Enter estimated hours">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="card bg-base-100 shadow">
                <div class="card-body">
                    <div class="flex flex-col sm:flex-row gap-3 justify-start">
                        <button type="submit" name="action" value="create" class="btn btn-primary">
                            <span class="icon-[tabler--check] size-5"></span>
                            Create Task
                        </button>
                        <button type="submit" name="action" value="create_and_add_more" class="btn btn-outline btn-primary">
                            <span class="icon-[tabler--plus] size-5"></span>
                            Create & Add More
                        </button>
                        <a href="{{ route('tasks.index') }}" class="btn btn-ghost">
                            Cancel
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Watcher Selection Modal -->
<div id="watcher-modal" class="modal">
    <div class="modal-box max-w-lg bg-white">
        <!-- Header with gradient accent -->
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

        <!-- Search with icon -->
        <div class="relative mb-4">
            <input type="text" id="watcher-modal-search" placeholder="Search team members..." class="input input-bordered w-full pl-11 focus:input-primary transition-all">
            <span class="icon-[tabler--search] size-5 absolute left-4 top-1/2 -translate-y-1/2 text-base-content/40"></span>
        </div>

        <!-- Quick Actions -->
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

        <!-- Team Members List -->
        <div id="watcher-modal-list" class="space-y-1 max-h-72 overflow-y-auto border border-base-200 rounded-xl p-2 bg-base-100/50">
            @php
                $avatarColors = ['bg-primary', 'bg-secondary', 'bg-accent', 'bg-info', 'bg-success', 'bg-warning'];
            @endphp
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

        <!-- Modal Actions -->
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

@push('scripts')
<style>
    /* Modal styles for programmatic open */
    .modal {
        pointer-events: none;
        opacity: 0;
        visibility: hidden;
        position: fixed;
        inset: 0;
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 9998;
        transition: opacity 0.2s ease-out, visibility 0.2s ease-out;
    }
    .modal.modal-open {
        pointer-events: auto;
        opacity: 1;
        visibility: visible;
    }
    .modal .modal-box {
        position: relative;
        z-index: 9999;
        max-height: calc(100vh - 5em);
        overflow-y: auto;
        border-radius: 1rem;
        padding: 1.5rem;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    }
    .modal .modal-box.bg-white {
        background-color: #ffffff !important;
    }
    /* Show check icon when checkbox is checked */
    .watcher-modal-item:has(.watcher-modal-checkbox:checked) .watcher-check-icon {
        opacity: 1;
    }
    .watcher-modal-item:has(.watcher-modal-checkbox:checked) {
        background-color: hsl(var(--p) / 0.08);
        border-color: hsl(var(--p) / 0.3);
    }
    .watcher-modal-item:has(.watcher-modal-checkbox:checked) .avatar > div {
        ring-color: hsl(var(--p));
    }
    .modal .modal-backdrop {
        position: fixed;
        inset: 0;
        background-color: rgba(0, 0, 0, 0.5);
        z-index: 9997;
        cursor: pointer;
    }

    /* Custom Date Picker Styles */
    .date-day-btn {
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 500;
        transition: all 0.15s ease;
        cursor: pointer;
        border: none;
        background: transparent;
        color: #374151;
    }
    .date-day-btn:hover:not(:disabled):not(.selected) {
        background: hsl(var(--p) / 0.1);
    }
    .date-day-btn.selected {
        background: hsl(var(--p));
        color: white;
        box-shadow: 0 4px 12px hsl(var(--p) / 0.4);
    }
    .date-day-btn.today:not(.selected) {
        border: 2px solid hsl(var(--p));
        background: hsl(var(--p) / 0.05);
    }
    .date-day-btn:disabled {
        color: #d1d5db;
        cursor: not-allowed;
    }
    .date-day-btn.other-month {
        color: #9ca3af;
    }
</style>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Custom Date Picker
    const dueDateDisplay = document.getElementById('due-date-display');
    const dueDateValue = document.getElementById('due-date-value');
    const dueDatePickerDropdown = document.getElementById('due-date-picker-dropdown');
    const dueDateYear = document.getElementById('due-date-year');
    const dueDateMonth = document.getElementById('due-date-month');
    const dueDateDays = document.getElementById('due-date-days');
    const dueDateHour = document.getElementById('due-date-hour');
    const dueDateMinute = document.getElementById('due-date-minute');
    const dueDateAmpm = document.getElementById('due-date-ampm');

    let selectedDate = null;
    let currentViewYear = new Date().getFullYear();
    let currentViewMonth = new Date().getMonth();

    // Populate year dropdown (current year + next 10 years)
    function populateYears() {
        const currentYear = new Date().getFullYear();
        dueDateYear.innerHTML = '';
        for (let year = currentYear; year <= currentYear + 10; year++) {
            const option = document.createElement('option');
            option.value = year;
            option.textContent = year;
            dueDateYear.appendChild(option);
        }
        dueDateYear.value = currentViewYear;
    }

    // Render calendar grid
    function renderCalendar() {
        const today = new Date();
        today.setHours(0, 0, 0, 0);

        const year = currentViewYear;
        const month = currentViewMonth;

        // Update selects
        dueDateYear.value = year;
        dueDateMonth.value = month;

        // First day of the month
        const firstDay = new Date(year, month, 1);
        const startDayOfWeek = firstDay.getDay();

        // Last day of the month
        const lastDay = new Date(year, month + 1, 0);
        const daysInMonth = lastDay.getDate();

        // Days from previous month
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
            // Capture day in closure
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

        // Next month days to fill the grid
        const totalCells = Math.ceil((startDayOfWeek + daysInMonth) / 7) * 7;
        const nextMonthDays = totalCells - (startDayOfWeek + daysInMonth);
        for (let day = 1; day <= nextMonthDays; day++) {
            const btn = createDayButton(day, true, false, false);
            dueDateDays.appendChild(btn);
        }
    }

    function createDayButton(day, isOtherMonth, isDisabled, isSelected, isToday = false) {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.textContent = day;
        btn.className = 'date-day-btn';

        if (isOtherMonth) {
            btn.classList.add('other-month');
            btn.disabled = true;
        }
        if (isDisabled) {
            btn.disabled = true;
        }
        if (isSelected) {
            btn.classList.add('selected');
        }
        if (isToday) {
            btn.classList.add('today');
        }

        return btn;
    }

    function selectDay(day) {
        console.log('selectDay called with day:', day, 'year:', currentViewYear, 'month:', currentViewMonth);
        selectedDate = new Date(currentViewYear, currentViewMonth, day);
        console.log('selectedDate set to:', selectedDate);
        renderCalendar();
    }

    // Toggle date picker visibility
    window.toggleDueDatePicker = function() {
        if (dueDatePickerDropdown.classList.contains('hidden')) {
            dueDatePickerDropdown.classList.remove('hidden');
            if (!selectedDate && dueDateValue.value) {
                // Parse existing date
                const parsed = new Date(dueDateValue.value);
                if (!isNaN(parsed)) {
                    selectedDate = parsed;
                    currentViewYear = parsed.getFullYear();
                    currentViewMonth = parsed.getMonth();

                    // Set time fields
                    let hours = parsed.getHours();
                    const minutes = parsed.getMinutes();
                    const ampm = hours >= 12 ? 'PM' : 'AM';
                    hours = hours % 12 || 12;

                    dueDateHour.value = hours;
                    dueDateMinute.value = String(minutes).padStart(2, '0');
                    dueDateAmpm.value = ampm;
                }
            }
            populateYears();
            renderCalendar();
        } else {
            dueDatePickerDropdown.classList.add('hidden');
        }
    };

    // Close date picker
    window.closeDueDatePicker = function() {
        dueDatePickerDropdown.classList.add('hidden');
    };

    // Clear due date
    window.clearDueDate = function() {
        selectedDate = null;
        dueDateDisplay.value = '';
        dueDateValue.value = '';
        dueDateHour.value = '12';
        dueDateMinute.value = '00';
        dueDateAmpm.value = 'AM';
        // Remove visual indicator
        dueDateDisplay.classList.remove('input-primary', 'border-primary');
        renderCalendar();
        closeDueDatePicker();
    };

    // Apply due date
    window.applyDueDate = function() {
        if (!selectedDate) {
            closeDueDatePicker();
            return;
        }

        // Get time values
        let hours = parseInt(dueDateHour.value) || 12;
        const minutes = parseInt(dueDateMinute.value) || 0;
        const ampm = dueDateAmpm.value;

        // Validate hour
        if (hours < 1) hours = 1;
        if (hours > 12) hours = 12;

        // Convert to 24-hour format
        if (ampm === 'PM' && hours !== 12) {
            hours += 12;
        } else if (ampm === 'AM' && hours === 12) {
            hours = 0;
        }

        // Set the datetime
        selectedDate.setHours(hours, minutes, 0, 0);

        // Format for display: "December 4, 2025 2:30 PM"
        const options = {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: 'numeric',
            minute: '2-digit',
            hour12: true
        };
        dueDateDisplay.value = selectedDate.toLocaleDateString('en-US', options);

        // Format for form submission: "YYYY-MM-DD HH:MM"
        const year = selectedDate.getFullYear();
        const month = String(selectedDate.getMonth() + 1).padStart(2, '0');
        const day = String(selectedDate.getDate()).padStart(2, '0');
        const hour24 = String(selectedDate.getHours()).padStart(2, '0');
        const min = String(selectedDate.getMinutes()).padStart(2, '0');
        dueDateValue.value = `${year}-${month}-${day} ${hour24}:${min}`;

        // Add visual indicator that date is selected
        dueDateDisplay.classList.add('input-primary', 'border-primary');

        closeDueDatePicker();
    };

    // Year/Month change handlers
    dueDateYear.addEventListener('change', function() {
        currentViewYear = parseInt(this.value);
        renderCalendar();
    });

    dueDateMonth.addEventListener('change', function() {
        currentViewMonth = parseInt(this.value);
        renderCalendar();
    });

    // Close picker when clicking outside
    document.addEventListener('click', function(e) {
        // Don't close if clicking inside the picker or on the display input
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

            // Set time fields
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
            // Add visual indicator
            dueDateDisplay.classList.add('input-primary', 'border-primary');
        }
    }

    // Searchable Workspace Select
    const workspaceSelectContainer = document.getElementById('workspace-select-container');
    const workspaceDropdown = document.getElementById('workspace-dropdown');
    const workspaceSearch = document.getElementById('workspace-search');
    const workspaceHiddenInput = document.getElementById('workspace-hidden-input');
    const workspaceClear = document.getElementById('workspace-clear');
    const workspaceOptions = document.querySelectorAll('.workspace-option');
    const noWorkspaceResults = document.getElementById('no-workspace-results');
    let selectedWorkspace = null;
    let workspaceHighlightIndex = -1;

    // Show dropdown
    function showWorkspaceDropdown() {
        workspaceDropdown.classList.remove('hidden');
        workspaceSelectContainer.classList.add('ring-2', 'ring-primary', 'ring-offset-2');
        workspaceHighlightIndex = -1;
        updateWorkspaceHighlight();
    }

    // Hide dropdown
    function hideWorkspaceDropdown() {
        workspaceDropdown.classList.add('hidden');
        workspaceSelectContainer.classList.remove('ring-2', 'ring-primary', 'ring-offset-2');
        workspaceHighlightIndex = -1;
        // Restore selected workspace name if exists
        if (selectedWorkspace) {
            workspaceSearch.value = selectedWorkspace.name;
        }
    }

    // Toggle dropdown
    function toggleWorkspaceDropdown() {
        if (workspaceDropdown.classList.contains('hidden')) {
            showWorkspaceDropdown();
        } else {
            hideWorkspaceDropdown();
        }
    }

    // Get visible workspace options
    function getVisibleWorkspaceOptions() {
        return Array.from(workspaceOptions).filter(opt => !opt.classList.contains('hidden'));
    }

    // Update highlight for keyboard navigation
    function updateWorkspaceHighlight() {
        const visibleOptions = getVisibleWorkspaceOptions();
        visibleOptions.forEach((opt, i) => {
            if (i === workspaceHighlightIndex) {
                opt.classList.add('bg-base-200');
                opt.scrollIntoView({ block: 'nearest' });
            } else if (!opt.classList.contains('bg-primary/10')) {
                opt.classList.remove('bg-base-200');
            }
        });
    }

    // Click on container (but not input) opens dropdown and focuses input
    workspaceSelectContainer.addEventListener('click', function(e) {
        if (e.target === workspaceSearch) {
            // Click was on the input itself, just show dropdown
            if (workspaceDropdown.classList.contains('hidden')) {
                showWorkspaceDropdown();
            }
        } else if (e.target.id !== 'workspace-clear' && !e.target.closest('#workspace-clear')) {
            // Click was on container or other elements (not clear button)
            toggleWorkspaceDropdown();
            workspaceSearch.focus();
        }
    });

    // Focus on input shows dropdown
    workspaceSearch.addEventListener('focus', function() {
        if (workspaceDropdown.classList.contains('hidden')) {
            showWorkspaceDropdown();
        }
    });

    // Hide dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!workspaceSelectContainer.contains(e.target) && !workspaceDropdown.contains(e.target)) {
            hideWorkspaceDropdown();
        }
    });

    // Search functionality
    workspaceSearch.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        let visibleCount = 0;

        workspaceOptions.forEach(option => {
            const name = option.dataset.search;
            if (name.includes(searchTerm)) {
                option.classList.remove('hidden');
                visibleCount++;
            } else {
                option.classList.add('hidden');
            }
        });

        noWorkspaceResults.classList.toggle('hidden', visibleCount > 0);

        // Reset highlight when searching
        workspaceHighlightIndex = -1;
        updateWorkspaceHighlight();

        // Show dropdown if hidden while typing
        if (workspaceDropdown.classList.contains('hidden')) {
            showWorkspaceDropdown();
        }
    });

    // Keyboard navigation for workspace
    workspaceSearch.addEventListener('keydown', function(e) {
        const visibleOptions = getVisibleWorkspaceOptions();

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            if (workspaceDropdown.classList.contains('hidden')) {
                showWorkspaceDropdown();
            } else {
                workspaceHighlightIndex = Math.min(workspaceHighlightIndex + 1, visibleOptions.length - 1);
                updateWorkspaceHighlight();
            }
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            if (!workspaceDropdown.classList.contains('hidden')) {
                workspaceHighlightIndex = Math.max(workspaceHighlightIndex - 1, 0);
                updateWorkspaceHighlight();
            }
        } else if (e.key === 'Enter') {
            e.preventDefault();
            if (workspaceHighlightIndex >= 0 && visibleOptions[workspaceHighlightIndex]) {
                visibleOptions[workspaceHighlightIndex].click();
            }
        } else if (e.key === 'Escape') {
            e.preventDefault();
            hideWorkspaceDropdown();
            workspaceSearch.blur();
        } else if (e.key === 'Tab') {
            hideWorkspaceDropdown();
        }
    });

    // Select workspace
    workspaceOptions.forEach(option => {
        option.addEventListener('click', function(e) {
            e.stopPropagation();
            const id = this.dataset.id;
            const name = this.dataset.name;
            const statuses = this.dataset.statuses;

            // Deselect previous
            workspaceOptions.forEach(opt => {
                opt.querySelector('.workspace-check').classList.add('hidden');
                opt.classList.remove('bg-primary/10');
                opt.classList.remove('bg-base-200');
            });

            // Select this one
            this.querySelector('.workspace-check').classList.remove('hidden');
            this.classList.add('bg-primary/10');

            selectedWorkspace = { id, name, statuses };
            workspaceSearch.value = name;
            workspaceHiddenInput.value = id;
            workspaceClear.classList.remove('hidden');

            // Close dropdown
            hideWorkspaceDropdown();

            // Update status dropdown
            updateStatusDropdown(statuses);
        });
    });

    // Clear workspace
    window.clearWorkspace = function(event) {
        event.stopPropagation();
        selectedWorkspace = null;
        workspaceSearch.value = '';
        workspaceHiddenInput.value = '';
        workspaceClear.classList.add('hidden');

        // Deselect all options
        workspaceOptions.forEach(opt => {
            opt.querySelector('.workspace-check').classList.add('hidden');
            opt.classList.remove('bg-primary/10');
        });

        // Reset status dropdown
        updateStatusDropdown(null);
    };

    // Status dropdown
    const statusSelect = document.getElementById('status-select');
    const statusHint = document.getElementById('status-hint');

    function updateStatusDropdown(statusesJson) {
        // Clear current options
        statusSelect.innerHTML = '';

        if (!statusesJson) {
            statusSelect.innerHTML = '<option value="">Select workspace first</option>';
            statusHint.textContent = 'Select a workspace to see available statuses';
            statusHint.classList.remove('hidden');
            return;
        }

        // Get statuses from data attribute
        let statuses = [];
        try {
            statuses = JSON.parse(statusesJson || '[]');
        } catch (e) {
            statuses = [];
        }

        // Add workspace statuses only
        if (statuses.length > 0) {
            // Select first status by default
            statuses.forEach((status, index) => {
                const option = document.createElement('option');
                option.value = status.id;
                option.textContent = status.name;
                if (index === 0) option.selected = true;
                statusSelect.appendChild(option);
            });
            statusHint.classList.add('hidden');
        } else {
            statusSelect.innerHTML = '<option value="">No statuses available</option>';
            statusHint.textContent = 'This workspace has no statuses configured';
            statusHint.classList.remove('hidden');
        }
    }

    // Initialize - check for pre-selected workspace
    const preSelectedWorkspace = '{{ old('workspace_id', $selectedWorkspace) }}';
    if (preSelectedWorkspace) {
        const preSelectedOption = document.querySelector(`.workspace-option[data-id="${preSelectedWorkspace}"]`);
        if (preSelectedOption) {
            preSelectedOption.click();
        }
    }

    // Task Visibility Toggle
    const visibilityToggle = document.querySelector('input[name="is_private"]');
    const visibilityLabel = document.getElementById('visibility-label');
    const visibilityDescription = document.getElementById('visibility-description');
    const visibilityIcon = document.getElementById('visibility-icon');

    // Notification settings elements
    const notifyOptions = document.querySelectorAll('input[name="notify_option"]');
    const notifyAllOption = document.getElementById('notify-all-option');
    const notifyAllRadio = notifyAllOption ? notifyAllOption.querySelector('input[type="radio"]') : null;
    const notifySelectedRadio = document.getElementById('notify-selected-radio');
    const privateTaskNotice = document.getElementById('private-task-notice');
    const selectedPeopleHint = document.getElementById('selected-people-hint');
    const selectedWatchersPreview = document.getElementById('selected-watchers-preview');
    const selectedAvatarsContainer = document.getElementById('selected-avatars-container');
    const selectPeopleBtn = document.getElementById('select-people-btn');
    const watcherHiddenInputs = document.getElementById('watcher-hidden-inputs');

    // Watcher Modal elements
    const watcherModal = document.getElementById('watcher-modal');
    const watcherModalSearch = document.getElementById('watcher-modal-search');
    const watcherModalList = document.getElementById('watcher-modal-list');
    const watcherModalItems = document.querySelectorAll('.watcher-modal-item');
    const watcherModalCheckboxes = document.querySelectorAll('.watcher-modal-checkbox');
    const modalSelectedCount = document.getElementById('modal-selected-count');
    const modalSelectAll = document.getElementById('modal-select-all');
    const modalClearAll = document.getElementById('modal-clear-all');
    const watcherModalEmpty = document.getElementById('watcher-modal-empty');

    // Store selected watchers
    let selectedWatchers = [];

    // Initialize from existing hidden inputs
    document.querySelectorAll('.watcher-hidden-input').forEach(input => {
        const item = document.querySelector(`.watcher-modal-item[data-id="${input.dataset.id}"]`);
        if (item) {
            selectedWatchers.push({
                id: input.dataset.id,
                name: item.dataset.name
            });
        }
    });

    // Open watcher modal
    window.openWatcherModal = function() {
        console.log('Opening watcher modal', watcherModal);
        if (!watcherModal) {
            console.error('Watcher modal not found!');
            return;
        }

        // Sync modal checkboxes with selected watchers
        watcherModalCheckboxes.forEach(checkbox => {
            checkbox.checked = selectedWatchers.some(w => w.id === checkbox.value);
        });
        updateModalCount();

        // Open modal using class
        watcherModal.classList.add('modal-open');
        document.body.classList.add('overflow-hidden');

        setTimeout(() => {
            if (watcherModalSearch) watcherModalSearch.focus();
        }, 100);

        // Auto-select the "Only people I select" option
        if (notifySelectedRadio) notifySelectedRadio.checked = true;
    };

    // Close watcher modal
    window.closeWatcherModal = function() {
        if (!watcherModal) return;
        watcherModal.classList.remove('modal-open');
        document.body.classList.remove('overflow-hidden');
        if (watcherModalSearch) watcherModalSearch.value = '';
        // Reset search filter
        watcherModalItems.forEach(item => item.classList.remove('hidden'));
        if (watcherModalEmpty) watcherModalEmpty.classList.add('hidden');
    };

    // Apply watcher selection
    window.applyWatcherSelection = function() {
        selectedWatchers = [];

        watcherModalCheckboxes.forEach(checkbox => {
            if (checkbox.checked) {
                const item = checkbox.closest('.watcher-modal-item');
                selectedWatchers.push({
                    id: checkbox.value,
                    name: item.dataset.name
                });
            }
        });

        updateWatcherUI();
        closeWatcherModal();
    };

    // Update modal selected count
    function updateModalCount() {
        const count = document.querySelectorAll('.watcher-modal-checkbox:checked').length;
        if (modalSelectedCount) modalSelectedCount.textContent = count;
    }

    // Update watcher UI (avatars, hidden inputs, hints)
    function updateWatcherUI() {
        if (!watcherHiddenInputs) return;

        // Update hidden inputs
        watcherHiddenInputs.innerHTML = selectedWatchers.map(w =>
            `<input type="hidden" name="watcher_ids[]" value="${w.id}" class="watcher-hidden-input" data-id="${w.id}">`
        ).join('');

        // Update avatar preview
        if (selectedWatchers.length > 0) {
            if (selectedWatchersPreview) selectedWatchersPreview.classList.remove('hidden');
            if (selectPeopleBtn) selectPeopleBtn.classList.add('hidden');

            // Build avatar HTML
            let avatarHtml = '';
            const maxAvatars = 6;
            const displayWatchers = selectedWatchers.slice(0, maxAvatars);

            displayWatchers.forEach(w => {
                avatarHtml += `
                    <div class="avatar placeholder border-2 border-base-100" title="${w.name}">
                        <div class="bg-primary text-primary-content w-8 rounded-full">
                            <span class="text-xs">${w.name.charAt(0).toUpperCase()}</span>
                        </div>
                    </div>
                `;
            });

            if (selectedWatchers.length > maxAvatars) {
                avatarHtml += `
                    <div class="avatar placeholder border-2 border-base-100">
                        <div class="bg-neutral text-neutral-content w-8 rounded-full">
                            <span class="text-xs">+${selectedWatchers.length - maxAvatars}</span>
                        </div>
                    </div>
                `;
            }

            if (selectedAvatarsContainer) selectedAvatarsContainer.innerHTML = avatarHtml;
            if (selectedPeopleHint) selectedPeopleHint.textContent = `${selectedWatchers.length} people will be notified`;
        } else {
            if (selectedWatchersPreview) selectedWatchersPreview.classList.add('hidden');
            if (selectPeopleBtn) selectPeopleBtn.classList.remove('hidden');
            if (selectedPeopleHint) selectedPeopleHint.textContent = 'Choose specific team members to notify';
        }
    }

    // Function to update notification UI based on private/public state
    function updateNotificationOptions(isPrivate) {
        if (!notifyAllOption || !notifyAllRadio || !privateTaskNotice) return;

        if (isPrivate) {
            // Private task: disable "All workspace members" option
            notifyAllOption.classList.add('opacity-50', 'pointer-events-none');
            notifyAllRadio.disabled = true;
            privateTaskNotice.classList.remove('hidden');

            // If "All" was selected, switch to "Selected"
            if (notifyAllRadio.checked && notifySelectedRadio) {
                notifySelectedRadio.checked = true;
            }
        } else {
            // Public task: enable all options
            notifyAllOption.classList.remove('opacity-50', 'pointer-events-none');
            notifyAllRadio.disabled = false;
            privateTaskNotice.classList.add('hidden');
        }
    }

    // Visibility toggle change handler
    visibilityToggle.addEventListener('change', function() {
        if (this.checked) {
            visibilityLabel.textContent = 'Private Task';
            visibilityDescription.textContent = 'Only you and assigned members can see this task';
            visibilityIcon.className = 'icon-[tabler--lock] size-6 text-base-content/50';
            updateNotificationOptions(true);
        } else {
            visibilityLabel.textContent = 'Public Task';
            visibilityDescription.textContent = 'All workspace members can see this task';
            visibilityIcon.className = 'icon-[tabler--world] size-6 text-base-content/50';
            updateNotificationOptions(false);
        }
    });

    // Modal search functionality
    if (watcherModalSearch) {
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

            if (watcherModalEmpty) watcherModalEmpty.classList.toggle('hidden', visibleCount > 0);
        });
    }

    // Modal select all
    if (modalSelectAll) {
        modalSelectAll.addEventListener('click', function() {
            watcherModalItems.forEach(item => {
                if (!item.classList.contains('hidden')) {
                    const checkbox = item.querySelector('.watcher-modal-checkbox');
                    if (checkbox) checkbox.checked = true;
                }
            });
            updateModalCount();
        });
    }

    // Modal clear all
    if (modalClearAll) {
        modalClearAll.addEventListener('click', function() {
            watcherModalCheckboxes.forEach(checkbox => {
                checkbox.checked = false;
            });
            updateModalCount();
        });
    }

    // Update count when checkbox changes
    watcherModalCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateModalCount);
    });

    // Initialize notification state based on current visibility
    if (visibilityToggle) updateNotificationOptions(visibilityToggle.checked);
    updateWatcherUI();

    // Multi-select Task Type (Select2-like)
    const tasktypeSelect = document.getElementById('tasktype-select');
    const tasktypeDropdown = document.getElementById('tasktype-dropdown');
    const tasktypeSearch = document.getElementById('tasktype-search');
    const selectedTasktypesContainer = document.getElementById('selected-tasktypes');
    const tasktypeHiddenInputs = document.getElementById('tasktype-hidden-inputs');
    const tasktypeOptions = document.querySelectorAll('.tasktype-option');
    const noTasktypeResults = document.getElementById('no-tasktype-results');
    let selectedTasktypes = [];
    let tasktypeHighlightIndex = -1;

    // Get visible tasktype options
    function getVisibleTasktypeOptions() {
        return Array.from(tasktypeOptions).filter(opt => !opt.classList.contains('hidden'));
    }

    // Update highlight for keyboard navigation
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

    // Show dropdown
    function showTasktypeDropdown() {
        tasktypeDropdown.classList.remove('hidden');
        tasktypeSelect.classList.add('ring-2', 'ring-primary', 'ring-offset-2');
        tasktypeHighlightIndex = -1;
        updateTasktypeHighlight();
    }

    // Hide dropdown
    function hideTasktypeDropdown() {
        tasktypeDropdown.classList.add('hidden');
        tasktypeSelect.classList.remove('ring-2', 'ring-primary', 'ring-offset-2');
        tasktypeHighlightIndex = -1;
        tasktypeSearch.value = '';
        tasktypeOptions.forEach(option => option.classList.remove('hidden'));
        noTasktypeResults.classList.add('hidden');
    }

    // Click on container
    tasktypeSelect.addEventListener('click', function(e) {
        if (e.target.closest('button')) return;
        if (tasktypeDropdown.classList.contains('hidden')) {
            showTasktypeDropdown();
        }
        tasktypeSearch.focus();
    });

    // Search functionality
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

    // Keyboard navigation
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

    // Hide dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!tasktypeSelect.contains(e.target) && !tasktypeDropdown.contains(e.target)) {
            hideTasktypeDropdown();
        }
    });

    // Select/deselect task type
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

    // Update UI for task types
    function updateSelectedTasktypes() {
        selectedTasktypesContainer.innerHTML = selectedTasktypes.map(t => `
            <span class="badge badge-primary gap-1">
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

        tasktypeSearch.placeholder = selectedTasktypes.length > 0 ? 'Add more types...' : 'Search and select task types...';
    }

    // Remove task type
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

    // Multi-select Assignee (Select2-like)
    const assigneeSelect = document.getElementById('assignee-select');
    const assigneeDropdown = document.getElementById('assignee-dropdown');
    const assigneeSearch = document.getElementById('assignee-search');
    const selectedAssigneesContainer = document.getElementById('selected-assignees');
    const assigneeHiddenInputs = document.getElementById('assignee-hidden-inputs');
    const assigneeOptions = document.querySelectorAll('.assignee-option');
    const noAssigneeResults = document.getElementById('no-assignee-results');
    let selectedAssignees = [];
    let assigneeHighlightIndex = -1;

    // Get visible assignee options
    function getVisibleAssigneeOptions() {
        return Array.from(assigneeOptions).filter(opt => !opt.classList.contains('hidden'));
    }

    // Update highlight for keyboard navigation
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

    // Show dropdown
    function showAssigneeDropdown() {
        assigneeDropdown.classList.remove('hidden');
        assigneeSelect.classList.add('ring-2', 'ring-primary', 'ring-offset-2');
        assigneeHighlightIndex = -1;
        updateAssigneeHighlight();
    }

    // Hide dropdown
    function hideAssigneeDropdown() {
        assigneeDropdown.classList.add('hidden');
        assigneeSelect.classList.remove('ring-2', 'ring-primary', 'ring-offset-2');
        assigneeHighlightIndex = -1;
    }

    // Toggle dropdown
    function toggleAssigneeDropdown() {
        if (assigneeDropdown.classList.contains('hidden')) {
            showAssigneeDropdown();
        } else {
            hideAssigneeDropdown();
        }
    }

    // Click on container (but not input) opens dropdown and focuses input
    assigneeSelect.addEventListener('click', function(e) {
        // Don't toggle if clicking on remove button in badge
        if (e.target.closest('button')) {
            return;
        }
        if (e.target === assigneeSearch) {
            // Click was on the input itself, just show dropdown
            if (assigneeDropdown.classList.contains('hidden')) {
                showAssigneeDropdown();
            }
        } else {
            // Click was on container or other elements
            toggleAssigneeDropdown();
            assigneeSearch.focus();
        }
    });

    // Focus on input shows dropdown
    assigneeSearch.addEventListener('focus', function() {
        if (assigneeDropdown.classList.contains('hidden')) {
            showAssigneeDropdown();
        }
    });

    // Hide dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!assigneeSelect.contains(e.target) && !assigneeDropdown.contains(e.target)) {
            hideAssigneeDropdown();
        }
    });

    // Search functionality
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

        // Reset highlight when searching
        assigneeHighlightIndex = -1;
        updateAssigneeHighlight();

        // Show dropdown if hidden while typing
        if (assigneeDropdown.classList.contains('hidden')) {
            showAssigneeDropdown();
        }
    });

    // Keyboard navigation for assignee
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

    // Select/deselect assignee
    assigneeOptions.forEach(option => {
        option.addEventListener('click', function(e) {
            e.stopPropagation();
            const id = this.dataset.id;
            const name = this.dataset.name;
            const checkIcon = this.querySelector('.assignee-check');

            const index = selectedAssignees.findIndex(a => a.id === id);
            if (index > -1) {
                // Deselect
                selectedAssignees.splice(index, 1);
                checkIcon.classList.add('hidden');
                this.classList.remove('bg-primary/10');
            } else {
                // Select
                selectedAssignees.push({ id, name });
                checkIcon.classList.remove('hidden');
                this.classList.add('bg-primary/10');
            }

            // Remove keyboard highlight after selection
            this.classList.remove('bg-base-200');
            updateSelectedAssignees();

            // Keep dropdown open for multi-select, but refocus the search
            assigneeSearch.focus();
        });
    });

    // Update UI
    function updateSelectedAssignees() {
        // Update visible tags
        selectedAssigneesContainer.innerHTML = selectedAssignees.map(a => `
            <span class="badge badge-primary gap-1">
                <span class="text-xs">${a.name.charAt(0)}</span>
                ${a.name}
                <button type="button" class="btn btn-ghost btn-xs btn-circle size-4" onclick="removeAssignee('${a.id}', event)">
                    <span class="icon-[tabler--x] size-3"></span>
                </button>
            </span>
        `).join('');

        // Update hidden inputs
        assigneeHiddenInputs.innerHTML = selectedAssignees.map(a =>
            `<input type="hidden" name="assignee_ids[]" value="${a.id}">`
        ).join('');

        // Update placeholder visibility
        assigneeSearch.placeholder = selectedAssignees.length > 0 ? 'Add more...' : 'Search and select assignees...';
    }

    // Remove assignee
    window.removeAssignee = function(id, event) {
        event.stopPropagation();
        const index = selectedAssignees.findIndex(a => a.id === id);
        if (index > -1) {
            selectedAssignees.splice(index, 1);
            // Update option UI
            const option = document.querySelector(`.assignee-option[data-id="${id}"]`);
            if (option) {
                option.querySelector('.assignee-check').classList.add('hidden');
                option.classList.remove('bg-primary/10');
            }
            updateSelectedAssignees();
        }
    };

    // Tags functionality - declare early so form submit can access
    const tagsContainer = document.getElementById('tags-container');
    const tagInput = document.getElementById('tag-input');
    const tagsHidden = document.getElementById('tags-hidden');
    let tags = [];

    // Get the main form - select by action attribute to be specific
    const mainForm = document.querySelector('form[action="{{ route('tasks.store') }}"]');

    // Update hidden input before form submit
    if (mainForm) {
        mainForm.addEventListener('submit', function(e) {
            // Ensure tags are in JSON format for processTagifyTags
            if (tags.length > 0) {
                const tagsJson = JSON.stringify(tags.map(tag => ({ value: tag })));
                tagsHidden.value = tagsJson;
                console.log('Tags JSON:', tagsJson);
            }
        });
    }

    // Tags event listeners and functions
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

    function renderTags() {
        const tagElements = tagsContainer.querySelectorAll('.tag-item');
        tagElements.forEach(el => el.remove());

        tags.forEach((tag, index) => {
            const tagEl = document.createElement('span');
            tagEl.className = 'tag-item badge badge-primary gap-1';
            tagEl.innerHTML = `
                ${tag}
                <button type="button" class="hover:text-primary-content/70" onclick="removeTag(${index})">
                    <span class="icon-[tabler--x] size-3"></span>
                </button>
            `;
            tagsContainer.insertBefore(tagEl, tagInput);
        });
    }

    // File Upload
    const fileInput = document.getElementById('file-input');
    const dropZone = document.getElementById('file-drop-zone');
    const filePreview = document.getElementById('file-preview');
    let selectedFiles = [];

    dropZone.addEventListener('click', () => fileInput.click());

    dropZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropZone.classList.add('border-primary', 'bg-primary/5');
    });

    dropZone.addEventListener('dragleave', () => {
        dropZone.classList.remove('border-primary', 'bg-primary/5');
    });

    dropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropZone.classList.remove('border-primary', 'bg-primary/5');
        handleFiles(e.dataTransfer.files);
    });

    fileInput.addEventListener('change', () => {
        handleFiles(fileInput.files);
    });

    function handleFiles(files) {
        for (let file of files) {
            if (file.size > 10 * 1024 * 1024) {
                alert(`File "${file.name}" is too large. Maximum size is 10MB.`);
                continue;
            }
            selectedFiles.push(file);
        }
        updateFilePreview();
    }

    function updateFilePreview() {
        if (selectedFiles.length === 0) {
            filePreview.classList.add('hidden');
            return;
        }

        filePreview.classList.remove('hidden');
        filePreview.innerHTML = selectedFiles.map((file, index) => `
            <div class="flex items-center gap-3 p-2 bg-base-200 rounded-lg">
                <span class="icon-[tabler--file] size-5 text-base-content/60"></span>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium truncate">${file.name}</p>
                    <p class="text-xs text-base-content/50">${formatFileSize(file.size)}</p>
                </div>
                <button type="button" class="btn btn-ghost btn-xs btn-circle text-error" onclick="removeFile(${index})">
                    <span class="icon-[tabler--x] size-4"></span>
                </button>
            </div>
        `).join('');

        // Update the file input with DataTransfer
        const dt = new DataTransfer();
        selectedFiles.forEach(file => dt.items.add(file));
        fileInput.files = dt.files;
    }

    window.removeFile = function(index) {
        selectedFiles.splice(index, 1);
        updateFilePreview();
    };

    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
});
</script>
@endpush
@endsection
