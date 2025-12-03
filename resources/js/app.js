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
