<!-- On Hold Modal -->
<div id="on-hold-modal" class="custom-modal">
    <div class="custom-modal-box max-w-lg bg-base-100">
        <!-- Header -->
        <div class="flex items-center justify-between mb-5">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-full bg-warning/20 flex items-center justify-center">
                    <span class="icon-[tabler--player-pause] size-6 text-warning"></span>
                </div>
                <div>
                    <h3 class="font-bold text-lg">Put Task On Hold</h3>
                    <p class="text-xs text-base-content/50">This will pause the task until resumed</p>
                </div>
            </div>
            <button type="button" onclick="closeOnHoldModal()" class="btn btn-ghost btn-sm btn-circle hover:bg-error/10 hover:text-error transition-colors">
                <span class="icon-[tabler--x] size-5"></span>
            </button>
        </div>

        <form action="{{ route('tasks.hold', $task) }}" method="POST" id="on-hold-form">
            @csrf

            <!-- Reason for Hold -->
            <div class="form-control mb-4">
                <label class="label" for="hold-reason">
                    <span class="label-text font-medium">Reason for Hold <span class="text-error">*</span></span>
                </label>
                <textarea
                    name="hold_reason"
                    id="hold-reason"
                    class="textarea textarea-bordered h-24"
                    placeholder="Enter the reason why this task is being put on hold..."
                    required
                ></textarea>
                <label class="label">
                    <span class="label-text-alt text-base-content/50">This will be visible to all task viewers</span>
                </label>
            </div>

            <!-- Notify People -->
            <div class="form-control mb-6">
                <label class="label">
                    <span class="label-text font-medium">Notify People</span>
                    <span class="label-text-alt text-base-content/50">Optional</span>
                </label>
                <div class="space-y-2 max-h-40 overflow-y-auto p-3 border border-base-300 rounded-lg bg-base-50">
                    <!-- Task Creator -->
                    @if($task->creator && $task->creator->id !== auth()->id())
                    <label class="flex items-center gap-3 cursor-pointer hover:bg-base-200 p-2 rounded-lg transition-colors">
                        <input type="checkbox" name="notify_users[]" value="{{ $task->creator->id }}" class="checkbox checkbox-sm checkbox-primary" checked>
                        <div class="avatar">
                            <div class="w-8 rounded-full">
                                <img src="{{ $task->creator->avatar_url }}" alt="{{ $task->creator->name }}" />
                            </div>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium">{{ $task->creator->name }}</p>
                            <p class="text-xs text-base-content/50">Creator</p>
                        </div>
                    </label>
                    @endif

                    <!-- Task Assignee -->
                    @if($task->assignee && $task->assignee->id !== auth()->id() && $task->assignee->id !== $task->creator?->id)
                    <label class="flex items-center gap-3 cursor-pointer hover:bg-base-200 p-2 rounded-lg transition-colors">
                        <input type="checkbox" name="notify_users[]" value="{{ $task->assignee->id }}" class="checkbox checkbox-sm checkbox-primary" checked>
                        <div class="avatar">
                            <div class="w-8 rounded-full">
                                <img src="{{ $task->assignee->avatar_url }}" alt="{{ $task->assignee->name }}" />
                            </div>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium">{{ $task->assignee->name }}</p>
                            <p class="text-xs text-base-content/50">Assignee</p>
                        </div>
                    </label>
                    @endif

                    <!-- Task Watchers -->
                    @foreach($task->watchers as $watcher)
                        @if($watcher->id !== auth()->id() && $watcher->id !== $task->creator?->id && $watcher->id !== $task->assignee?->id)
                        <label class="flex items-center gap-3 cursor-pointer hover:bg-base-200 p-2 rounded-lg transition-colors">
                            <input type="checkbox" name="notify_users[]" value="{{ $watcher->id }}" class="checkbox checkbox-sm checkbox-primary">
                            <div class="avatar">
                                <div class="w-8 rounded-full">
                                    <img src="{{ $watcher->avatar_url }}" alt="{{ $watcher->name }}" />
                                </div>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-medium">{{ $watcher->name }}</p>
                                <p class="text-xs text-base-content/50">Watcher</p>
                            </div>
                        </label>
                        @endif
                    @endforeach

                    @if((!$task->creator || $task->creator->id === auth()->id()) && (!$task->assignee || $task->assignee->id === auth()->id()) && $task->watchers->filter(fn($w) => $w->id !== auth()->id())->isEmpty())
                    <p class="text-sm text-base-content/50 text-center py-2">No other people to notify</p>
                    @endif
                </div>
            </div>

            <!-- Info Box -->
            <div class="p-4 bg-warning/10 border border-warning/20 rounded-lg mb-6">
                <div class="flex items-start gap-3">
                    <span class="icon-[tabler--info-circle] size-5 text-warning mt-0.5"></span>
                    <div class="text-sm text-base-content/70">
                        <p class="mb-1"><strong>What happens when a task is on hold:</strong></p>
                        <ul class="list-disc list-inside space-y-1 text-xs">
                            <li>Task cannot be edited or closed</li>
                            <li>A hold banner will be displayed on the task</li>
                            <li>Selected people will be notified</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex justify-end gap-3 pt-4 border-t border-base-200">
                <button type="button" class="btn btn-ghost" onclick="closeOnHoldModal()">Cancel</button>
                <button type="submit" class="btn btn-warning gap-2">
                    <span class="icon-[tabler--player-pause] size-5"></span>
                    Put On Hold
                </button>
            </div>
        </form>
    </div>
    <div class="custom-modal-backdrop" onclick="closeOnHoldModal()"></div>
</div>

<!-- Resume Task Modal -->
<div id="resume-task-modal" class="custom-modal">
    <div class="custom-modal-box max-w-md bg-base-100">
        <!-- Header -->
        <div class="text-center mb-6">
            <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-success/20 flex items-center justify-center">
                <span class="icon-[tabler--player-play] size-8 text-success"></span>
            </div>
            <h3 class="text-xl font-bold text-base-content mb-2">Resume Task</h3>
            <p class="text-base-content/70">Are you sure you want to resume this task?</p>
        </div>

        @if($task->hold_reason)
        <div class="p-4 bg-base-200 rounded-lg mb-6">
            <p class="text-xs text-base-content/50 mb-1">Current hold reason:</p>
            <p class="text-sm">{{ $task->hold_reason }}</p>
        </div>
        @endif

        <form action="{{ route('tasks.resume', $task) }}" method="POST">
            @csrf

            <!-- Notify People -->
            <div class="form-control mb-6">
                <label class="label">
                    <span class="label-text font-medium">Notify People</span>
                </label>
                <div class="space-y-2 max-h-32 overflow-y-auto p-3 border border-base-300 rounded-lg bg-base-50">
                    @if($task->creator && $task->creator->id !== auth()->id())
                    <label class="flex items-center gap-3 cursor-pointer hover:bg-base-200 p-2 rounded-lg transition-colors">
                        <input type="checkbox" name="notify_users[]" value="{{ $task->creator->id }}" class="checkbox checkbox-sm checkbox-success" checked>
                        <div class="avatar">
                            <div class="w-6 rounded-full">
                                <img src="{{ $task->creator->avatar_url }}" alt="{{ $task->creator->name }}" />
                            </div>
                        </div>
                        <span class="text-sm">{{ $task->creator->name }}</span>
                    </label>
                    @endif

                    @if($task->assignee && $task->assignee->id !== auth()->id() && $task->assignee->id !== $task->creator?->id)
                    <label class="flex items-center gap-3 cursor-pointer hover:bg-base-200 p-2 rounded-lg transition-colors">
                        <input type="checkbox" name="notify_users[]" value="{{ $task->assignee->id }}" class="checkbox checkbox-sm checkbox-success" checked>
                        <div class="avatar">
                            <div class="w-6 rounded-full">
                                <img src="{{ $task->assignee->avatar_url }}" alt="{{ $task->assignee->name }}" />
                            </div>
                        </div>
                        <span class="text-sm">{{ $task->assignee->name }}</span>
                    </label>
                    @endif
                </div>
            </div>

            <!-- Actions -->
            <div class="flex justify-end gap-3">
                <button type="button" class="btn btn-ghost" onclick="closeResumeTaskModal()">Cancel</button>
                <button type="submit" class="btn btn-success gap-2">
                    <span class="icon-[tabler--player-play] size-5"></span>
                    Resume Task
                </button>
            </div>
        </form>
    </div>
    <div class="custom-modal-backdrop" onclick="closeResumeTaskModal()"></div>
</div>

<style>
/* Custom Modal Styles */
.custom-modal {
    pointer-events: none;
    opacity: 0;
    visibility: hidden;
    position: fixed;
    inset: 0;
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 9999;
    transition: opacity 0.2s ease-out, visibility 0.2s ease-out;
}

.custom-modal.modal-open {
    pointer-events: auto;
    opacity: 1;
    visibility: visible;
}

.custom-modal .custom-modal-box {
    position: relative;
    z-index: 10000;
    padding: 1.5rem;
    border-radius: 1rem;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    max-height: 90vh;
    overflow-y: auto;
    transform: scale(0.95);
    transition: transform 0.2s ease-out;
}

.custom-modal.modal-open .custom-modal-box {
    transform: scale(1);
}

.custom-modal .custom-modal-backdrop {
    position: fixed;
    inset: 0;
    background-color: rgba(0, 0, 0, 0.5);
    z-index: 9998;
}
</style>

<script>
// On Hold Modal Functions
function openOnHoldModal() {
    document.getElementById('on-hold-modal').classList.add('modal-open');
    document.body.style.overflow = 'hidden';
    // Focus on reason textarea
    setTimeout(() => {
        document.getElementById('hold-reason')?.focus();
    }, 100);
}

function closeOnHoldModal() {
    document.getElementById('on-hold-modal').classList.remove('modal-open');
    document.body.style.overflow = '';
    // Reset form
    document.getElementById('on-hold-form')?.reset();
}

// Resume Task Modal Functions
function openResumeTaskModal() {
    document.getElementById('resume-task-modal').classList.add('modal-open');
    document.body.style.overflow = 'hidden';
}

function closeResumeTaskModal() {
    document.getElementById('resume-task-modal').classList.remove('modal-open');
    document.body.style.overflow = '';
}

// Close modals on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeOnHoldModal();
        closeResumeTaskModal();
    }
});
</script>
