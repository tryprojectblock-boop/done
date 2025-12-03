<!-- Global Delete Confirmation Modal -->
<div id="global-delete-modal" class="delete-modal">
    <div class="delete-modal-backdrop" id="delete-modal-backdrop"></div>
    <div class="delete-modal-box bg-base-100 rounded-lg shadow-xl p-6 max-w-md w-full mx-4">
        <h3 class="font-bold text-lg text-error flex items-center gap-2">
            <span class="icon-[tabler--alert-triangle] size-6"></span>
            <span id="delete-modal-title">Delete Item</span>
        </h3>
        <p class="py-4" id="delete-modal-message">Are you sure you want to delete this item?</p>
        <p class="text-sm text-base-content/60" id="delete-modal-warning">This action cannot be undone.</p>
        <div class="flex justify-end gap-2 mt-6">
            <button type="button" class="btn btn-ghost" id="delete-modal-cancel">Cancel</button>
            <form id="delete-modal-form" method="POST">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-error">
                    <span class="icon-[tabler--trash] size-5"></span>
                    Delete
                </button>
            </form>
        </div>
    </div>
</div>

<style>
.delete-modal {
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
.delete-modal.open {
    display: flex !important;
}
.delete-modal-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    z-index: 1;
}
.delete-modal-box {
    position: relative;
    z-index: 2;
}
</style>

<script>
window.DeleteModal = {
    modal: null,
    form: null,
    title: null,
    message: null,
    warning: null,

    init: function() {
        this.modal = document.getElementById('global-delete-modal');
        this.form = document.getElementById('delete-modal-form');
        this.title = document.getElementById('delete-modal-title');
        this.message = document.getElementById('delete-modal-message');
        this.warning = document.getElementById('delete-modal-warning');

        // Cancel button
        document.getElementById('delete-modal-cancel').addEventListener('click', () => this.close());

        // Backdrop click
        document.getElementById('delete-modal-backdrop').addEventListener('click', () => this.close());

        // Setup all delete buttons on the page
        this.setupButtons();
    },

    setupButtons: function() {
        document.querySelectorAll('[data-delete]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();

                const action = btn.dataset.deleteAction;
                const title = btn.dataset.deleteTitle || 'Delete Item';
                const name = btn.dataset.deleteName || 'this item';
                const warning = btn.dataset.deleteWarning || 'This action cannot be undone.';

                this.open(action, title, name, warning);
            });
        });
    },

    open: function(action, title, name, warning) {
        this.form.action = action;
        this.title.textContent = title;
        this.message.innerHTML = `Are you sure you want to delete "<strong>${name}</strong>"?`;
        this.warning.textContent = warning;
        this.modal.classList.add('open');
    },

    close: function() {
        this.modal.classList.remove('open');
    }
};

document.addEventListener('DOMContentLoaded', function() {
    DeleteModal.init();
});
</script>
