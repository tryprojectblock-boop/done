<!-- Global Confirmation Modal -->
<div id="global-confirm-modal" class="confirm-modal">
    <div class="confirm-modal-backdrop" id="confirm-modal-backdrop"></div>
    <div class="confirm-modal-box bg-base-100 rounded-lg shadow-xl p-6 max-w-md w-full mx-4">
        <h3 class="font-bold text-lg flex items-center gap-2" id="confirm-modal-header">
            <span id="confirm-modal-icon" class="icon-[tabler--info-circle] size-6 text-primary"></span>
            <span id="confirm-modal-title">Confirm Action</span>
        </h3>
        <div class="py-4" id="confirm-modal-content">
            <!-- Content will be inserted dynamically -->
        </div>
        <div class="flex justify-end gap-2 mt-2">
            <button type="button" class="btn btn-ghost" id="confirm-modal-cancel">Cancel</button>
            <!-- Link button (default) -->
            <a href="#" class="btn btn-primary" id="confirm-modal-confirm">
                <span id="confirm-modal-confirm-icon" class="icon-[tabler--check] size-5"></span>
                <span id="confirm-modal-confirm-text">Confirm</span>
            </a>
            <!-- Form button (for POST/DELETE requests) -->
            <form id="confirm-modal-form" method="POST" class="hidden">
                @csrf
                <input type="hidden" name="_method" id="confirm-modal-method" value="POST">
                <button type="submit" class="btn btn-primary" id="confirm-modal-form-btn">
                    <span id="confirm-modal-form-icon" class="icon-[tabler--check] size-5"></span>
                    <span id="confirm-modal-form-text">Confirm</span>
                </button>
            </form>
        </div>
    </div>
</div>

<style>
.confirm-modal {
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
.confirm-modal.open {
    display: flex !important;
}
.confirm-modal-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    z-index: 1;
}
.confirm-modal-box {
    position: relative;
    z-index: 2;
}
</style>

<script>
window.ConfirmModal = {
    modal: null,
    icon: null,
    title: null,
    content: null,
    confirmBtn: null,
    confirmIcon: null,
    confirmText: null,
    header: null,
    form: null,
    formBtn: null,
    formIcon: null,
    formText: null,
    formMethod: null,

    init: function() {
        this.modal = document.getElementById('global-confirm-modal');
        this.icon = document.getElementById('confirm-modal-icon');
        this.title = document.getElementById('confirm-modal-title');
        this.content = document.getElementById('confirm-modal-content');
        this.confirmBtn = document.getElementById('confirm-modal-confirm');
        this.confirmIcon = document.getElementById('confirm-modal-confirm-icon');
        this.confirmText = document.getElementById('confirm-modal-confirm-text');
        this.header = document.getElementById('confirm-modal-header');
        this.form = document.getElementById('confirm-modal-form');
        this.formBtn = document.getElementById('confirm-modal-form-btn');
        this.formIcon = document.getElementById('confirm-modal-form-icon');
        this.formText = document.getElementById('confirm-modal-form-text');
        this.formMethod = document.getElementById('confirm-modal-method');

        // Cancel button
        document.getElementById('confirm-modal-cancel').addEventListener('click', () => this.close());

        // Backdrop click
        document.getElementById('confirm-modal-backdrop').addEventListener('click', () => this.close());

        // Setup all confirm buttons on the page
        this.setupButtons();
    },

    setupButtons: function() {
        document.querySelectorAll('[data-confirm]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();

                const action = btn.dataset.confirmAction;
                const title = btn.dataset.confirmTitle || 'Confirm Action';
                const content = btn.dataset.confirmContent || 'Are you sure you want to proceed?';
                const buttonText = btn.dataset.confirmButton || 'Confirm';
                const buttonIcon = btn.dataset.confirmIcon || 'tabler--check';
                const buttonClass = btn.dataset.confirmClass || 'btn-primary';
                const iconClass = btn.dataset.confirmIconClass || 'text-primary';
                const titleIcon = btn.dataset.confirmTitleIcon || 'tabler--info-circle';
                const method = btn.dataset.confirmMethod || null; // POST, DELETE, PUT, PATCH

                this.open(action, title, content, buttonText, buttonIcon, buttonClass, iconClass, titleIcon, method);
            });
        });
    },

    open: function(action, title, content, buttonText, buttonIcon, buttonClass, iconClass, titleIcon, method) {
        this.title.textContent = title;
        this.content.innerHTML = content;

        // Update icon classes
        this.icon.className = `icon-[${titleIcon}] size-6 ${iconClass}`;

        if (method) {
            // Use form for POST/DELETE/PUT/PATCH requests
            this.confirmBtn.classList.add('hidden');
            this.form.classList.remove('hidden');
            this.form.action = action;
            this.formMethod.value = method;
            this.formText.textContent = buttonText;
            this.formIcon.className = `icon-[${buttonIcon}] size-5`;
            this.formBtn.className = `btn ${buttonClass}`;
        } else {
            // Use link for GET requests
            this.form.classList.add('hidden');
            this.confirmBtn.classList.remove('hidden');
            this.confirmBtn.href = action;
            this.confirmText.textContent = buttonText;
            this.confirmIcon.className = `icon-[${buttonIcon}] size-5`;
            this.confirmBtn.className = `btn ${buttonClass}`;
        }

        this.modal.classList.add('open');
    },

    close: function() {
        this.modal.classList.remove('open');
    }
};

document.addEventListener('DOMContentLoaded', function() {
    ConfirmModal.init();
});
</script>
