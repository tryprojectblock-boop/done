import "./bootstrap";
import "flyonui/flyonui";
import { createApp } from "vue";
import flatpickr from "flatpickr";
import "flatpickr/dist/flatpickr.min.css";

// Make flatpickr available globally
window.flatpickr = flatpickr;

// Import components
import SignUpWizard from "./components/auth/SignUpWizard.vue";
import LoginForm from "./components/auth/LoginForm.vue";

// Mount signup app if element exists
const signupApp = document.getElementById("signup-app");
if (signupApp) {
    const app = createApp(SignUpWizard, {
        options: JSON.parse(signupApp.dataset.options || "{}"),
    });
    app.mount("#signup-app");
}

// Mount login app if element exists
const loginApp = document.getElementById("login-app");
if (loginApp) {
    const app = createApp(LoginForm);
    app.mount("#login-app");
}

// Auto-dismiss alerts after timeout
document.addEventListener("DOMContentLoaded", function () {
    const alerts = document.querySelectorAll(".alert[data-auto-dismiss]");

    alerts.forEach((alert) => {
        const timeout = parseInt(alert.dataset.autoDismiss) || 5000;

        setTimeout(() => {
            // Add fade-out animation
            alert.style.transition = "opacity 0.3s ease-out";
            alert.style.opacity = "0";

            // Remove element after animation
            setTimeout(() => {
                alert.remove();
            }, 300);
        }, timeout);
    });
});

/**
 * Live Notification Polling System
 */
(function initNotificationPolling() {
    // Check if user is authenticated by looking for CSRF token and auth indicator
    const csrfToken = document.querySelector(
        'meta[name="csrf-token"]'
    )?.content;

    // Only poll if there's a way to determine authentication
    // We'll make the first request and if it fails with 401/403, we stop
    let lastTs = "";
    const POLL_INTERVAL = 5000;
    let isPolling = false;

    async function pollNotifications() {
        if (isPolling) return;
        isPolling = true;

        try {
            const url = `/notifications/poll?last_ts=${encodeURIComponent(
                lastTs
            )}`;
            const response = await fetch(url, {
                credentials: "same-origin",
                headers: {
                    Accept: "application/json",
                    "X-Requested-With": "XMLHttpRequest",
                    "X-CSRF-TOKEN": csrfToken || "",
                },
            });

            // If unauthorized, stop polling
            if (response.status === 401 || response.status === 403) {
                return;
            }

            if (!response.ok) {
                isPolling = false;
                return;
            }

            const data = await response.json();

            // Update last timestamp
            if (data.last_ts) lastTs = data.last_ts;

            // Update badge
            const badge = document.querySelector(".notification-badge");
            if (badge && data.unread_count !== undefined) {
                badge.textContent =
                    data.unread_count > 9 ? "9+" : data.unread_count;
                badge.style.display = data.unread_count > 0 ? "" : "none";
            }

            // Show toasts for new notifications
            if (data.notifications && data.notifications.length > 0) {
                data.notifications.forEach((notification) => {
                    showLiveToast(notification);
                });
            }

            // Schedule next poll
            setTimeout(pollNotifications, POLL_INTERVAL);
        } catch (error) {
            // Retry after interval even on error
            setTimeout(pollNotifications, POLL_INTERVAL);
        } finally {
            isPolling = false;
        }
    }

    function showLiveToast(notification) {
        const container = document.getElementById("toast-container");
        if (!container) return;

        const colors = {
            task_comment: "info",
            task_assigned: "success",
            task_created: "success",
            mention: "info",
            task_on_hold: "warning",
            task_resumed: "success",
            discussion_comment: "info",
            discussion_added: "success",
        };
        const type = colors[notification.type] || "info";

        const toast = document.createElement("div");
        toast.className = `alert alert-${type} shadow-lg max-w-sm cursor-pointer`;
        toast.innerHTML = `
            <div class="flex items-start gap-3 w-full">
                <span class="icon-[tabler--bell-ringing] size-5 shrink-0 mt-0.5 animate-pulse"></span>
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-sm">${notification.title}</p>
                    <p class="text-xs opacity-80">${notification.message}</p>
                </div>
                <button type="button" class="btn btn-ghost btn-xs btn-circle" onclick="event.stopPropagation(); this.closest('.alert').remove();">
                    <span class="icon-[tabler--x] size-4"></span>
                </button>
            </div>
        `;

        if (notification.url) {
            toast.onclick = () => (window.location.href = notification.url);
        }

        container.appendChild(toast);

        // Auto remove after 15 seconds
        setTimeout(() => {
            toast.style.opacity = "0";
            toast.style.transition = "opacity 0.3s";
            setTimeout(() => toast.remove(), 300);
        }, 15000);
    }

    // Start polling when DOM is ready
    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", () =>
            pollNotifications()
        );
    } else {
        pollNotifications();
    }
})();
