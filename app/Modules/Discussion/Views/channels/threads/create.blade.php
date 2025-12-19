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
                <span class="text-base-content">New Thread</span>
            </div>
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 rounded-lg {{ $channel->color_class }} flex items-center justify-center flex-shrink-0">
                    <span class="icon-[tabler--hash] size-5"></span>
                </div>
                <div>
                    <h1 class="text-lg font-bold text-base-content">New Thread</h1>
                    <p class="text-sm text-base-content/60">Post to {{ $channel->tag }}</p>
                </div>
            </div>
        </div>

        <!-- Form Content -->
        <div class="flex-1 p-4 md:p-6 pt-3">
            <div class="max-w-3xl mx-auto">
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

                <form action="{{ route('channels.threads.store', $channel) }}" method="POST" class="space-y-6">
                    @csrf

                    <!-- Thread Title -->
                    <div class="form-control">
                        <label class="label" for="thread-title">
                            <span class="label-text font-medium">Title <span class="text-error">*</span></span>
                        </label>
                        <input type="text" name="title" id="thread-title" value="{{ old('title') }}" placeholder="What do you want to discuss?" class="input input-bordered w-full text-lg @error('title') input-error @enderror" required maxlength="255" autofocus />
                        @error('title')
                            <div class="label"><span class="label-text-alt text-error">{{ $message }}</span></div>
                        @enderror
                    </div>

                    <!-- Thread Content (Rich Text Editor) -->
                    <x-quill-editor
                        name="content"
                        id="thread-content"
                        label="Details"
                        :value="old('content')"
                        placeholder="Add more context or details... You can drag & drop images here"
                        height="250px"
                    />

                    <!-- Task Attachment (Multi-Select Searchable) -->
                    <div class="form-control">
                        <label class="label justify-start gap-2">
                            <span class="label-text font-medium">Attach Tasks</span>
                            <span class="label-text-alt text-base-content/50">(Optional - Select multiple)</span>
                        </label>
                        <div class="relative">
                            <!-- Selected tasks chips -->
                            <div id="selected-tasks-container" class="flex flex-wrap gap-2 mb-2 empty:hidden"></div>

                            <!-- Search input container -->
                            <div id="task-select-container" class="min-h-12 p-2 border border-base-300 rounded-lg cursor-pointer flex items-center gap-2 bg-base-100 hover:border-primary transition-colors">
                                <span class="icon-[tabler--search] size-5 text-base-content/50"></span>
                                <input type="text" id="task-search" class="flex-1 bg-transparent border-0 outline-none text-sm text-base-content" placeholder="Search tasks by title or number..." autocomplete="off">
                                <span id="task-chevron" class="icon-[tabler--chevron-up] size-4 text-base-content/50 transition-transform"></span>
                            </div>

                            <!-- Dropdown (opens upward) -->
                            <div id="task-dropdown" class="absolute z-50 w-full bottom-full mb-1 bg-base-100 border border-base-300 rounded-lg shadow-lg max-h-72 overflow-y-auto hidden">
                                <!-- Select All / Clear All Header -->
                                <div class="sticky top-0 bg-base-100 border-b border-base-200 p-2 flex items-center justify-between z-10">
                                    <button type="button" id="select-all-tasks" class="btn btn-ghost btn-xs gap-1">
                                        <span class="icon-[tabler--checks] size-4"></span>
                                        Select All
                                    </button>
                                    <button type="button" id="clear-all-tasks" class="btn btn-ghost btn-xs gap-1 text-error">
                                        <span class="icon-[tabler--x] size-4"></span>
                                        Clear All
                                    </button>
                                </div>
                                @foreach($tasks as $task)
                                    @php
                                        $taskLabel = ($task->workspace?->prefix ?? 'T') . '-' . $task->task_number . ': ' . Str::limit($task->title, 40);
                                        $taskShortLabel = ($task->workspace?->prefix ?? 'T') . '-' . $task->task_number;
                                        $searchString = strtolower($task->task_number . ' ' . $task->title . ' ' . ($task->workspace?->prefix ?? '') . ' ' . ($task->workspace?->name ?? ''));
                                    @endphp
                                    <div class="task-option flex items-center gap-3 p-3 hover:bg-base-200 cursor-pointer transition-colors"
                                         data-id="{{ $task->id }}"
                                         data-text="{{ $taskLabel }}"
                                         data-short="{{ $taskShortLabel }}"
                                         data-search="{{ $searchString }}">
                                        <!-- Checkbox -->
                                        <div class="task-checkbox flex items-center justify-center w-5 h-5 border-2 border-base-300 rounded transition-colors flex-shrink-0">
                                            <span class="task-check icon-[tabler--check] size-4 text-white hidden"></span>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="font-medium text-sm truncate text-base-content">
                                                <span class="text-primary">{{ $task->workspace?->prefix ?? 'T' }}-{{ $task->task_number }}</span>
                                                <span>{{ Str::limit($task->title, 45) }}</span>
                                            </div>
                                            <div class="flex items-center gap-2 text-xs text-base-content/50">
                                                @if($task->status)
                                                    @php
                                                        $statusColor = $task->status->color ?? '#6b7280';
                                                        $r = hexdec(substr($statusColor, 1, 2));
                                                        $g = hexdec(substr($statusColor, 3, 2));
                                                        $b = hexdec(substr($statusColor, 5, 2));
                                                    @endphp
                                                    <span class="badge badge-xs font-medium" style="background-color: rgba({{ $r }}, {{ $g }}, {{ $b }}, 0.15); color: {{ $statusColor }}; border-color: rgba({{ $r }}, {{ $g }}, {{ $b }}, 0.3);">
                                                        {{ $task->status->name }}
                                                    </span>
                                                @endif
                                                @if($task->workspace)
                                                    <span>{{ $task->workspace->name }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                                <div id="no-task-results" class="p-3 text-center text-base-content/50 text-sm hidden">
                                    <span class="icon-[tabler--search-off] size-5 block mx-auto mb-1"></span>
                                    No tasks found
                                </div>
                            </div>

                            <!-- Hidden inputs container for form submission -->
                            <div id="task-hidden-inputs"></div>
                        </div>
                        <div class="label">
                            <span class="label-text-alt text-base-content/60">Link this thread to existing tasks for context</span>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex justify-end gap-3 pt-4 border-t border-base-200">
                        <a href="{{ route('channels.show', $channel) }}" class="btn btn-ghost">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <span class="icon-[tabler--send] size-4"></span>
                            Post Thread
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('task-select-container');
    const searchInput = document.getElementById('task-search');
    const dropdown = document.getElementById('task-dropdown');
    const chevron = document.getElementById('task-chevron');
    const selectedTasksContainer = document.getElementById('selected-tasks-container');
    const hiddenInputsContainer = document.getElementById('task-hidden-inputs');
    const noResults = document.getElementById('no-task-results');
    const options = document.querySelectorAll('.task-option');
    const selectAllBtn = document.getElementById('select-all-tasks');
    const clearAllBtn = document.getElementById('clear-all-tasks');

    if (!container || !searchInput || !dropdown) return;

    // Track selected tasks
    let selectedTasks = new Map();

    // Initialize with old values if exist
    @if(old('task_ids'))
        @foreach(old('task_ids') as $oldTaskId)
            const opt{{ $oldTaskId }} = document.querySelector('.task-option[data-id="{{ $oldTaskId }}"]');
            if (opt{{ $oldTaskId }}) {
                toggleTask('{{ $oldTaskId }}', opt{{ $oldTaskId }}.dataset.text, opt{{ $oldTaskId }}.dataset.short);
            }
        @endforeach
    @endif

    // Toggle dropdown
    container.addEventListener('click', function(e) {
        e.stopPropagation();
        toggleDropdown();
    });

    function toggleDropdown() {
        const isOpen = !dropdown.classList.contains('hidden');
        if (isOpen) {
            closeDropdown();
        } else {
            openDropdown();
        }
    }

    function openDropdown() {
        dropdown.classList.remove('hidden');
        chevron.classList.add('rotate-180');
        searchInput.focus();
        // Scroll dropdown to show content
        dropdown.scrollTop = 0;
    }

    function closeDropdown() {
        dropdown.classList.add('hidden');
        chevron.classList.remove('rotate-180');
        searchInput.value = '';
        filterOptions('');
    }

    // Search filtering
    searchInput.addEventListener('input', function(e) {
        e.stopPropagation();
        filterOptions(e.target.value);
    });

    searchInput.addEventListener('click', function(e) {
        e.stopPropagation();
        if (dropdown.classList.contains('hidden')) {
            openDropdown();
        }
    });

    function filterOptions(query) {
        const lowerQuery = query.toLowerCase().trim();
        let visibleCount = 0;

        options.forEach(opt => {
            const searchStr = opt.dataset.search || '';

            if (lowerQuery === '' || searchStr.includes(lowerQuery)) {
                opt.style.display = 'flex';
                visibleCount++;
            } else {
                opt.style.display = 'none';
            }
        });

        // Show/hide no results
        if (visibleCount === 0 && lowerQuery !== '') {
            noResults?.classList.remove('hidden');
        } else {
            noResults?.classList.add('hidden');
        }
    }

    // Option selection (toggle)
    options.forEach(opt => {
        opt.addEventListener('click', function(e) {
            e.stopPropagation();
            const taskId = this.dataset.id;
            const taskText = this.dataset.text;
            const taskShort = this.dataset.short;
            toggleTask(taskId, taskText, taskShort);
            updateOptionState(this, selectedTasks.has(taskId));
        });
    });

    function toggleTask(taskId, taskText, taskShort) {
        if (selectedTasks.has(taskId)) {
            // Deselect
            selectedTasks.delete(taskId);
        } else {
            // Select
            selectedTasks.set(taskId, { text: taskText, short: taskShort });
        }
        updateUI();
    }

    function updateOptionState(optionEl, isSelected) {
        const checkbox = optionEl.querySelector('.task-checkbox');
        const check = optionEl.querySelector('.task-check');

        if (isSelected) {
            checkbox.classList.add('bg-primary', 'border-primary');
            checkbox.classList.remove('border-base-300');
            check.classList.remove('hidden');
            optionEl.classList.add('bg-primary/5');
        } else {
            checkbox.classList.remove('bg-primary', 'border-primary');
            checkbox.classList.add('border-base-300');
            check.classList.add('hidden');
            optionEl.classList.remove('bg-primary/5');
        }
    }

    function updateUI() {
        // Update all option states
        options.forEach(opt => {
            updateOptionState(opt, selectedTasks.has(opt.dataset.id));
        });

        // Update selected chips
        selectedTasksContainer.innerHTML = '';
        selectedTasks.forEach((data, taskId) => {
            const chip = document.createElement('div');
            chip.className = 'badge badge-lg gap-2 pr-1 bg-primary/10 border-primary/20 text-base-content';
            chip.innerHTML = `
                <span class="icon-[tabler--subtask] size-4 text-primary"></span>
                <span class="text-sm">${data.short}</span>
                <button type="button" class="btn btn-ghost btn-xs btn-circle hover:bg-error/20 hover:text-error" onclick="removeTask('${taskId}')">
                    <span class="icon-[tabler--x] size-3"></span>
                </button>
            `;
            selectedTasksContainer.appendChild(chip);
        });

        // Update hidden inputs
        hiddenInputsContainer.innerHTML = '';
        selectedTasks.forEach((data, taskId) => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'task_ids[]';
            input.value = taskId;
            hiddenInputsContainer.appendChild(input);
        });

        // Update placeholder
        if (selectedTasks.size > 0) {
            searchInput.placeholder = `${selectedTasks.size} task(s) selected. Add more...`;
        } else {
            searchInput.placeholder = 'Search tasks by title or number...';
        }
    }

    // Select All (visible only)
    selectAllBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        options.forEach(opt => {
            if (opt.style.display !== 'none') {
                const taskId = opt.dataset.id;
                if (!selectedTasks.has(taskId)) {
                    selectedTasks.set(taskId, {
                        text: opt.dataset.text,
                        short: opt.dataset.short
                    });
                }
            }
        });
        updateUI();
    });

    // Clear All
    clearAllBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        selectedTasks.clear();
        updateUI();
    });

    // Global remove function
    window.removeTask = function(taskId) {
        selectedTasks.delete(taskId);
        updateUI();
    };

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!container.contains(e.target) && !dropdown.contains(e.target) && !selectedTasksContainer.contains(e.target)) {
            closeDropdown();
        }
    });

    // Keyboard navigation
    searchInput.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeDropdown();
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            const visibleOptions = Array.from(options).filter(opt => opt.style.display !== 'none');
            if (visibleOptions.length > 0) {
                visibleOptions[visibleOptions.length - 1].focus();
            }
        } else if (e.key === 'ArrowDown') {
            e.preventDefault();
            const visibleOptions = Array.from(options).filter(opt => opt.style.display !== 'none');
            if (visibleOptions.length > 0) {
                visibleOptions[0].focus();
            }
        }
    });

    options.forEach((opt, index) => {
        opt.setAttribute('tabindex', '0');
        opt.addEventListener('keydown', function(e) {
            const visibleOptions = Array.from(options).filter(o => o.style.display !== 'none');
            const currentIndex = visibleOptions.indexOf(this);

            if (e.key === 'ArrowDown') {
                e.preventDefault();
                if (currentIndex < visibleOptions.length - 1) {
                    visibleOptions[currentIndex + 1].focus();
                }
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                if (currentIndex > 0) {
                    visibleOptions[currentIndex - 1].focus();
                } else {
                    searchInput.focus();
                }
            } else if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                this.click();
            } else if (e.key === 'Escape') {
                closeDropdown();
            }
        });
    });
});
</script>
@endpush
