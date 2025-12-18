<!-- Delete User Confirmation Modal -->
<div id="delete-user-modal" class="user-delete-modal">
    <div class="user-delete-modal-backdrop" onclick="closeDeleteModal()"></div>
    <div class="user-delete-modal-box bg-base-100 rounded-2xl shadow-2xl max-w-lg w-full mx-4 overflow-hidden flex flex-col">

        <!-- Header -->
        <div class="bg-gradient-to-r from-error/10 to-error/5 px-6 py-5 border-b border-error/20">
            <button type="button" class="btn btn-sm btn-circle btn-ghost absolute right-3 top-3 hover:bg-error/10" onclick="closeDeleteModal()">
                <span class="icon-[tabler--x] size-5"></span>
            </button>
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-full bg-error/10 flex items-center justify-center ring-4 ring-error/20">
                    <span class="icon-[tabler--user-minus] size-7 text-error"></span>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-base-content">Remove User</h3>
                    <p class="text-base-content/60 mt-0.5">
                        <span id="delete-user-name" class="font-medium text-error">{{ $user->full_name ?? '' }}</span>
                    </p>
                </div>
            </div>
        </div>

        <!-- Loading State -->
        <div id="delete-modal-loading" class="flex-1 flex flex-col items-center justify-center py-12">
            <span class="loading loading-spinner loading-lg text-primary"></span>
            <p class="mt-4 text-base-content/60 font-medium">Loading user data...</p>
        </div>

        <!-- Content (hidden until loaded) -->
        <div id="delete-modal-content" class="hidden flex-1 px-6 py-5">

            <!-- Warning Banner -->
            <div class="p-4 bg-warning/10 border border-warning/30 rounded-xl flex items-start gap-3 mb-5">
                <span class="icon-[tabler--alert-triangle] size-5 text-warning shrink-0 mt-0.5"></span>
                <div>
                    <p class="font-medium text-warning-content">This action cannot be undone</p>
                    <p class="text-sm text-base-content/60 mt-1">Review the user's work below and optionally reassign before removing.</p>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-3 gap-3 mb-5">
                <div class="bg-base-200/50 rounded-xl p-4 text-center border border-base-300/50">
                    <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center mx-auto mb-2">
                        <span class="icon-[tabler--folder] size-5 text-primary"></span>
                    </div>
                    <div id="summary-workspaces-count" class="text-2xl font-bold text-base-content">0</div>
                    <div class="text-xs text-base-content/60 uppercase tracking-wide">Workspaces</div>
                </div>
                <div class="bg-base-200/50 rounded-xl p-4 text-center border border-base-300/50">
                    <div class="w-10 h-10 rounded-full bg-secondary/10 flex items-center justify-center mx-auto mb-2">
                        <span class="icon-[tabler--checkbox] size-5 text-secondary"></span>
                    </div>
                    <div id="summary-tasks-count" class="text-2xl font-bold text-base-content">0</div>
                    <div class="text-xs text-base-content/60 uppercase tracking-wide">Open Tasks</div>
                </div>
                <div class="bg-base-200/50 rounded-xl p-4 text-center border border-base-300/50">
                    <div class="w-10 h-10 rounded-full bg-accent/10 flex items-center justify-center mx-auto mb-2">
                        <span class="icon-[tabler--message-circle] size-5 text-accent"></span>
                    </div>
                    <div id="summary-discussions-count" class="text-2xl font-bold text-base-content">0</div>
                    <div class="text-xs text-base-content/60 uppercase tracking-wide">Discussions</div>
                </div>
            </div>

            <!-- Reassignment Section -->
            <div class="bg-gradient-to-br from-base-200/80 to-base-200/40 rounded-xl p-5 border border-base-300/50">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center">
                        <span class="icon-[tabler--replace] size-4 text-primary"></span>
                    </div>
                    <div>
                        <h4 class="font-semibold text-base-content">Reassign Work</h4>
                        <p class="text-xs text-base-content/60">Transfer tasks and discussions to another team member</p>
                    </div>
                </div>

                <div class="form-control">
                    <select id="reassign-to" class="select select-bordered w-full bg-base-100">
                        <option value="">Don't reassign (leave tasks unassigned)</option>
                        <!-- Team members will be loaded here -->
                    </select>
                </div>

                <div id="reassign-options" class="mt-4 space-y-3 hidden">
                    <label class="flex items-center gap-3 p-3 rounded-lg bg-base-100 border border-base-300/50 cursor-pointer hover:border-primary/30 transition-colors">
                        <input type="checkbox" id="reassign-tasks" class="checkbox checkbox-primary checkbox-sm" checked>
                        <div class="flex-1">
                            <span class="font-medium text-sm">Reassign open tasks</span>
                            <span class="text-xs text-base-content/60 ml-1">(<span id="reassign-tasks-count">0</span> tasks)</span>
                        </div>
                        <span class="icon-[tabler--checkbox] size-5 text-primary/50"></span>
                    </label>
                    <label class="flex items-center gap-3 p-3 rounded-lg bg-base-100 border border-base-300/50 cursor-pointer hover:border-primary/30 transition-colors">
                        <input type="checkbox" id="reassign-discussions" class="checkbox checkbox-primary checkbox-sm" checked>
                        <div class="flex-1">
                            <span class="font-medium text-sm">Transfer discussion ownership</span>
                            <span class="text-xs text-base-content/60 ml-1">(<span id="reassign-discussions-count">0</span> discussions)</span>
                        </div>
                        <span class="icon-[tabler--message-circle] size-5 text-primary/50"></span>
                    </label>
                </div>
            </div>
        </div>

        <!-- Error State -->
        <div id="delete-modal-error" class="hidden flex-1 flex flex-col items-center justify-center py-12">
            <div class="w-16 h-16 rounded-full bg-error/10 flex items-center justify-center mb-4">
                <span class="icon-[tabler--alert-circle] size-8 text-error"></span>
            </div>
            <p class="font-medium text-error">Failed to load user data</p>
            <p class="text-sm text-base-content/60 mt-1">Please check your connection and try again</p>
            <button type="button" class="btn btn-sm btn-ghost mt-4 gap-2" onclick="loadUserWorkData()">
                <span class="icon-[tabler--refresh] size-4"></span>
                Retry
            </button>
        </div>

        <!-- Footer Actions -->
        <div class="px-6 py-4 bg-base-200/30 border-t border-base-300/50 flex justify-end gap-3">
            <button type="button" class="btn btn-ghost" onclick="closeDeleteModal()">Cancel</button>
            <button type="button" id="confirm-delete-btn" class="btn btn-error gap-2" disabled>
                <span class="icon-[tabler--trash] size-4"></span>
                Remove User
            </button>
        </div>
    </div>
</div>

<style>
.user-delete-modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 9999;
    justify-content: center;
    align-items: center;
    padding: 1rem;
}
.user-delete-modal.open {
    display: flex !important;
    animation: fadeIn 0.2s ease-out;
}
.user-delete-modal-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.6);
    backdrop-filter: blur(4px);
    z-index: 1;
}
.user-delete-modal-box {
    position: relative;
    z-index: 2;
    animation: slideUp 0.3s ease-out;
}
@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}
@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(20px) scale(0.98);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}
</style>

<script>
// Delete modal state
let deleteUserModal = {
    userId: null,
    workDataUrl: null,
    deleteUrl: null,
    redirectUrl: null,
    userWorkData: null
};

function openDeleteModal(userId, userName, workDataUrl, deleteUrl, redirectUrl) {
    deleteUserModal.userId = userId;
    deleteUserModal.workDataUrl = workDataUrl;
    deleteUserModal.deleteUrl = deleteUrl;
    deleteUserModal.redirectUrl = redirectUrl || '{{ route("users.index") }}';

    // Update modal title
    document.getElementById('delete-user-name').textContent = userName;

    // Reset modal state
    document.getElementById('delete-modal-loading').classList.remove('hidden');
    document.getElementById('delete-modal-content').classList.add('hidden');
    document.getElementById('delete-modal-error').classList.add('hidden');
    document.getElementById('confirm-delete-btn').disabled = true;
    document.getElementById('reassign-to').value = '';
    document.getElementById('reassign-options').classList.add('hidden');

    // Open modal
    const modal = document.getElementById('delete-user-modal');
    if (modal) {
        modal.classList.add('open');
        document.body.style.overflow = 'hidden';
        loadUserWorkData();
    }
}

function closeDeleteModal() {
    const modal = document.getElementById('delete-user-modal');
    if (modal) {
        modal.classList.remove('open');
        document.body.style.overflow = '';
    }
}

async function loadUserWorkData() {
    const loadingEl = document.getElementById('delete-modal-loading');
    const contentEl = document.getElementById('delete-modal-content');
    const errorEl = document.getElementById('delete-modal-error');
    const confirmBtn = document.getElementById('confirm-delete-btn');

    // Show loading, hide others
    loadingEl.classList.remove('hidden');
    contentEl.classList.add('hidden');
    errorEl.classList.add('hidden');
    confirmBtn.disabled = true;

    try {
        const response = await fetch(deleteUserModal.workDataUrl, {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });

        if (!response.ok) throw new Error('Failed to load data');

        deleteUserModal.userWorkData = await response.json();
        renderWorkData(deleteUserModal.userWorkData);

        // Show content
        loadingEl.classList.add('hidden');
        contentEl.classList.remove('hidden');
        confirmBtn.disabled = false;
    } catch (error) {
        console.error('Error loading work data:', error);
        loadingEl.classList.add('hidden');
        errorEl.classList.remove('hidden');
    }
}

function renderWorkData(data) {
    // Update summary cards
    document.getElementById('summary-workspaces-count').textContent = data.workspaces?.length || 0;
    document.getElementById('summary-tasks-count').textContent = data.assigned_tasks_count || 0;
    document.getElementById('summary-discussions-count').textContent = data.discussions_count || 0;

    // Update reassign counts
    document.getElementById('reassign-tasks-count').textContent = data.assigned_tasks_count || 0;
    document.getElementById('reassign-discussions-count').textContent = data.discussions_count || 0;

    // Render team members dropdown
    const reassignSelect = document.getElementById('reassign-to');
    reassignSelect.innerHTML = '<option value="">Don\'t reassign (leave tasks unassigned)</option>';

    if (data.team_members && data.team_members.length > 0) {
        data.team_members.forEach(member => {
            const option = document.createElement('option');
            option.value = member.id;
            option.textContent = `${member.name} (${member.role})`;
            reassignSelect.appendChild(option);
        });
    }
}

// Show/hide reassign options based on selection
document.getElementById('reassign-to').addEventListener('change', function() {
    const reassignOptions = document.getElementById('reassign-options');
    if (this.value) {
        reassignOptions.classList.remove('hidden');
        reassignOptions.style.animation = 'fadeIn 0.2s ease-out';
    } else {
        reassignOptions.classList.add('hidden');
    }
});

// Handle delete confirmation
document.getElementById('confirm-delete-btn').addEventListener('click', async function() {
    const btn = this;
    const originalHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="loading loading-spinner loading-sm"></span> Removing...';

    const reassignTo = document.getElementById('reassign-to').value;
    const reassignTasks = document.getElementById('reassign-tasks').checked;
    const reassignDiscussions = document.getElementById('reassign-discussions').checked;

    try {
        const response = await fetch(deleteUserModal.deleteUrl, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                reassign_to: reassignTo || null,
                reassign_tasks: reassignTasks,
                reassign_discussions: reassignDiscussions
            })
        });

        const data = await response.json();

        if (data.success) {
            window.location.href = deleteUserModal.redirectUrl;
        } else {
            alert(data.error || 'Failed to remove user');
            btn.disabled = false;
            btn.innerHTML = originalHtml;
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
        btn.disabled = false;
        btn.innerHTML = originalHtml;
    }
});

// Close on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeDeleteModal();
    }
});
</script>
