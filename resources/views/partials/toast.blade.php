<!-- Toast Notification Container -->
<div id="toast-container" class="fixed top-20 right-4 z-[9999] flex flex-col gap-2"></div>

<!-- Auto-show session flash messages -->
@if(session('success') || session('error') || session('warning') || session('info'))
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    @if(session('success'))
    showToast(@json(session('success')), 'success');
    @endif
    @if(session('error'))
    showToast(@json(session('error')), 'error');
    @endif
    @if(session('warning'))
    showToast(@json(session('warning')), 'warning');
    @endif
    @if(session('info'))
    showToast(@json(session('info')), 'info');
    @endif
});
</script>
@endpush
@endif

@push('scripts')
<script>
/**
 * Show a toast notification
 * @param {string} message - The message to display
 * @param {string} type - The type of toast: 'success', 'error', 'warning', 'info'
 * @param {number} duration - Duration in milliseconds before auto-dismiss (default: 5000)
 */
function showToast(message, type = 'success', duration = 5000) {
    const container = document.getElementById('toast-container');
    if (!container) {
        console.warn('Toast container not found');
        return;
    }

    const alertClasses = {
        success: 'alert-success',
        error: 'alert-error',
        warning: 'alert-warning',
        info: 'alert-info'
    };

    const iconClasses = {
        success: 'icon-[tabler--circle-check]',
        error: 'icon-[tabler--alert-circle]',
        warning: 'icon-[tabler--alert-triangle]',
        info: 'icon-[tabler--info-circle]'
    };

    const alertClass = alertClasses[type] || alertClasses.success;
    const iconClass = iconClasses[type] || iconClasses.success;

    const toast = document.createElement('div');
    toast.className = `alert ${alertClass} shadow-lg animate-fade-in-up`;
    toast.innerHTML = `
        <span class="${iconClass} size-5 shrink-0"></span>
        <span>${message}</span>
        <button type="button" class="btn btn-ghost btn-xs btn-circle" onclick="this.parentElement.remove()">
            <span class="icon-[tabler--x] size-4"></span>
        </button>
    `;

    container.appendChild(toast);

    // Auto remove after duration
    if (duration > 0) {
        setTimeout(() => {
            toast.classList.add('animate-fade-out');
            setTimeout(() => toast.remove(), 300);
        }, duration);
    }
}

// Make it available globally
window.showToast = showToast;
</script>

<style>
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes fadeOut {
    from {
        opacity: 1;
        transform: translateY(0);
    }
    to {
        opacity: 0;
        transform: translateY(-10px);
    }
}

.animate-fade-in-up {
    animation: fadeInUp 0.3s ease-out;
}

.animate-fade-out {
    animation: fadeOut 0.3s ease-out forwards;
}
</style>
@endpush
