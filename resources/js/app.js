import './bootstrap';
import 'flyonui/flyonui';
import { createApp } from 'vue';

// Import components
import SignUpWizard from './components/auth/SignUpWizard.vue';
import LoginForm from './components/auth/LoginForm.vue';

// Mount signup app if element exists
const signupApp = document.getElementById('signup-app');
if (signupApp) {
    const app = createApp(SignUpWizard, {
        options: JSON.parse(signupApp.dataset.options || '{}'),
    });
    app.mount('#signup-app');
}

// Mount login app if element exists
const loginApp = document.getElementById('login-app');
if (loginApp) {
    const app = createApp(LoginForm);
    app.mount('#login-app');
}

// Auto-dismiss alerts after timeout
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert[data-auto-dismiss]');

    alerts.forEach(alert => {
        const timeout = parseInt(alert.dataset.autoDismiss) || 5000;

        setTimeout(() => {
            // Add fade-out animation
            alert.style.transition = 'opacity 0.3s ease-out';
            alert.style.opacity = '0';

            // Remove element after animation
            setTimeout(() => {
                alert.remove();
            }, 300);
        }, timeout);
    });
});
