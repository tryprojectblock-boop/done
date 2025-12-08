{{--
    Reusable Confirmation Modal Component

    Usage: Add data attributes to any element to trigger the modal:

    For DELETE actions (form submission):
    <button type="button"
        data-confirm-modal
        data-confirm-action="{{ route('your.delete.route', $item) }}"
        data-confirm-method="DELETE"
        data-confirm-title="Delete Item"
        data-confirm-message="Are you sure you want to delete this item? This action cannot be undone."
        data-confirm-button="Delete"
        data-confirm-type="danger">
        Delete
    </button>

    For other actions (PATCH, POST):
    <button type="button"
        data-confirm-modal
        data-confirm-action="{{ route('your.action.route', $item) }}"
        data-confirm-method="PATCH"
        data-confirm-title="Deactivate User"
        data-confirm-message="Are you sure you want to deactivate this user?"
        data-confirm-button="Deactivate"
        data-confirm-type="warning">
        Deactivate
    </button>

    Types: danger (red), warning (yellow), info (blue), success (green)
--}}

<dialog id="confirmModal" class="modal modal-bottom sm:modal-middle">
    <div class="modal-box">
        <h3 class="font-bold text-lg" id="confirmModalTitle">Confirm Action</h3>
        <p class="py-4" id="confirmModalMessage">Are you sure you want to proceed?</p>
        <div class="modal-action">
            <form method="dialog">
                <button class="btn btn-ghost">Cancel</button>
            </form>
            <form id="confirmModalForm" method="POST" action="">
                @csrf
                <input type="hidden" name="_method" id="confirmModalMethod" value="DELETE">
                <button type="submit" id="confirmModalButton" class="btn btn-error">Confirm</button>
            </form>
        </div>
    </div>
    <form method="dialog" class="modal-backdrop">
        <button>close</button>
    </form>
</dialog>

<script>
(function() {
    // Initialize confirm modal functionality
    function initConfirmModal() {
        const modal = document.getElementById('confirmModal');
        const modalTitle = document.getElementById('confirmModalTitle');
        const modalMessage = document.getElementById('confirmModalMessage');
        const modalForm = document.getElementById('confirmModalForm');
        const modalMethod = document.getElementById('confirmModalMethod');
        const modalButton = document.getElementById('confirmModalButton');

        if (!modal) return;

        // Button type classes mapping
        const typeClasses = {
            danger: 'btn-error',
            warning: 'btn-warning',
            info: 'btn-info',
            success: 'btn-success',
            primary: 'btn-primary'
        };

        // Use event delegation on document body
        document.body.addEventListener('click', function(e) {
            const trigger = e.target.closest('[data-confirm-modal]');
            if (!trigger) return;

            e.preventDefault();

            // Get data attributes
            const action = trigger.dataset.confirmAction;
            const method = trigger.dataset.confirmMethod || 'DELETE';
            const title = trigger.dataset.confirmTitle || 'Confirm Action';
            const message = trigger.dataset.confirmMessage || 'Are you sure you want to proceed? This action cannot be undone.';
            const buttonText = trigger.dataset.confirmButton || 'Confirm';
            const type = trigger.dataset.confirmType || 'danger';

            // Update modal content
            modalTitle.textContent = title;
            modalMessage.textContent = message;
            modalForm.action = action;
            modalMethod.value = method;
            modalButton.textContent = buttonText;

            // Update button style
            modalButton.className = 'btn ' + (typeClasses[type] || 'btn-error');

            // Show modal
            modal.showModal();
        });
    }

    // Run when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initConfirmModal);
    } else {
        initConfirmModal();
    }
})();
</script>
