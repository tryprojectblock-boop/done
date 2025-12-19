{{-- Unlink Task Confirmation Modal --}}
<div id="unlink-task-modal" class="fixed inset-0 z-50 hidden">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-black/50 transition-opacity" onclick="closeUnlinkTaskModal()"></div>

    <!-- Modal Container -->
    <div class="fixed inset-0 flex items-center justify-center p-4 pointer-events-none">
        <div class="bg-base-100 rounded-xl shadow-2xl w-full max-w-md pointer-events-auto transform transition-all">
            <div class="p-6">
                <!-- Icon -->
                <div class="flex items-center justify-center w-12 h-12 mx-auto mb-4 rounded-full bg-error/10">
                    <span class="icon-[tabler--link-off] size-6 text-error"></span>
                </div>

                <!-- Title -->
                <h3 class="text-lg font-bold text-center mb-2">Unlink Task</h3>

                <!-- Message -->
                <p class="text-center text-base-content/70 mb-2">
                    Are you sure you want to unlink this task from the discussion?
                </p>
                <p class="text-center font-medium text-base-content mb-6" id="unlink-task-title"></p>

                <!-- Actions -->
                <div class="flex items-center justify-center gap-3">
                    <button type="button" class="btn btn-ghost" onclick="closeUnlinkTaskModal()">
                        Cancel
                    </button>
                    <button type="button" id="confirm-unlink-btn" class="btn btn-error" onclick="confirmUnlinkTask()">
                        <span class="icon-[tabler--link-off] size-4"></span>
                        Unlink Task
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Unlink Task Modal State
const unlinkTaskState = {
    taskId: null,
    taskUuid: null,
    taskTitle: ''
};

function openUnlinkTaskModal(taskId, taskUuid, taskTitle) {
    unlinkTaskState.taskId = taskId;
    unlinkTaskState.taskUuid = taskUuid;
    unlinkTaskState.taskTitle = taskTitle;

    document.getElementById('unlink-task-title').textContent = taskTitle;

    const modal = document.getElementById('unlink-task-modal');
    if (modal) {
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
}

function closeUnlinkTaskModal() {
    const modal = document.getElementById('unlink-task-modal');
    if (modal) {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
    }

    // Reset state
    unlinkTaskState.taskId = null;
    unlinkTaskState.taskUuid = null;
    unlinkTaskState.taskTitle = '';
}

async function confirmUnlinkTask() {
    if (!unlinkTaskState.taskUuid) return;

    const btn = document.getElementById('confirm-unlink-btn');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="loading loading-spinner loading-xs"></span> Unlinking...';

    try {
        const response = await fetch(`/discussions/{{ $discussion->uuid }}/unlink-task/${unlinkTaskState.taskUuid}`, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        const data = await response.json();

        if (data.success) {
            // Remove the task from the list
            const taskEl = document.getElementById(`linked-task-${unlinkTaskState.taskId}`);
            if (taskEl) {
                taskEl.style.transition = 'opacity 0.3s ease-out';
                taskEl.style.opacity = '0';
                setTimeout(() => taskEl.remove(), 300);
            }

            // Update count badge
            const countBadge = document.getElementById('linked-tasks-count');
            if (countBadge) {
                const currentCount = parseInt(countBadge.textContent) || 0;
                countBadge.textContent = Math.max(0, currentCount - 1);
            }

            // Show empty message if no more tasks
            setTimeout(() => {
                const listContainer = document.getElementById('linked-tasks-list');
                if (listContainer && listContainer.children.length === 0) {
                    listContainer.innerHTML = `
                        <div id="no-linked-tasks-message" class="text-center py-8 text-base-content/60">
                            <span class="icon-[tabler--subtask] size-12 mx-auto mb-3 opacity-30"></span>
                            <p>No tasks linked to this discussion yet.</p>
                            <p class="text-sm mt-1">Click "Link Tasks" to add existing tasks.</p>
                        </div>
                    `;
                }
            }, 350);

            // Close modal
            closeUnlinkTaskModal();
        } else {
            alert(data.error || 'Failed to unlink task');
        }
    } catch (error) {
        console.error('Error unlinking task:', error);
        alert('An error occurred while unlinking the task');
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
}

// Close modal on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeUnlinkTaskModal();
    }
});
</script>
