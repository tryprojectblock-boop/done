import { ref, computed, reactive } from 'vue';
import { authApi } from '@/services/api';

export function useRegistration() {
    const state = reactive({
        uuid: null,
        email: '',
        step: 1,
        loading: false,
        error: null,
        fieldErrors: {},
    });

    const options = reactive({
        companySizes: [],
        industryTypes: [],
        passwordRequirements: [],
        loaded: false,
    });

    // Password validation state
    const passwordValidation = reactive({
        minLength: false,
        uppercase: false,
        lowercase: false,
        number: false,
        special: false,
    });

    const isLoading = computed(() => state.loading);
    const currentStep = computed(() => state.step);
    const hasError = computed(() => !!state.error);

    // Load registration options
    async function loadOptions() {
        if (options.loaded) return;

        try {
            const response = await authApi.getOptions();
            options.companySizes = response.data.data.company_sizes;
            options.industryTypes = response.data.data.industry_types;
            options.passwordRequirements = response.data.data.password_requirements;
            options.loaded = true;
        } catch (error) {
            console.error('Failed to load options:', error);
        }
    }

    // Clear errors
    function clearErrors() {
        state.error = null;
        state.fieldErrors = {};
    }

    // Set field error
    function setFieldErrors(errors) {
        state.fieldErrors = errors;
    }

    // Step 1: Register email
    async function registerEmail(email) {
        clearErrors();
        state.loading = true;

        try {
            const response = await authApi.registerEmail(email);
            state.uuid = response.data.data.uuid;
            state.email = response.data.data.email;
            state.step = 1.5; // Go to verification step
            return { success: true };
        } catch (error) {
            state.error = error.message;
            if (error.errors) setFieldErrors(error.errors);
            return { success: false, error: error.message };
        } finally {
            state.loading = false;
        }
    }

    // Verify activation code
    async function verifyCode(code) {
        clearErrors();
        state.loading = true;

        try {
            const response = await authApi.verifyCode(state.email, code);
            state.uuid = response.data.data.uuid;
            state.step = 2;
            return { success: true };
        } catch (error) {
            state.error = error.message;
            return { success: false, error: error.message };
        } finally {
            state.loading = false;
        }
    }

    // Resend activation code
    async function resendCode() {
        clearErrors();
        state.loading = true;

        try {
            await authApi.resendCode(state.email);
            return { success: true, message: 'A new code has been sent to your email.' };
        } catch (error) {
            state.error = error.message;
            return { success: false, error: error.message };
        } finally {
            state.loading = false;
        }
    }

    // Step 2: Complete profile
    async function completeProfile(data) {
        clearErrors();
        state.loading = true;

        try {
            await authApi.completeProfile(state.uuid, data);
            state.step = 3;
            return { success: true };
        } catch (error) {
            state.error = error.message;
            if (error.errors) setFieldErrors(error.errors);
            return { success: false, error: error.message };
        } finally {
            state.loading = false;
        }
    }

    // Step 3: Complete company
    async function completeCompany(data) {
        clearErrors();
        state.loading = true;

        try {
            await authApi.completeCompany(state.uuid, data);
            state.step = 4;
            return { success: true };
        } catch (error) {
            state.error = error.message;
            if (error.errors) setFieldErrors(error.errors);
            return { success: false, error: error.message };
        } finally {
            state.loading = false;
        }
    }

    // Step 4: Complete registration
    async function completeRegistration(invitedEmails = []) {
        clearErrors();
        state.loading = true;

        try {
            const response = await authApi.completeRegistration(state.uuid, {
                invited_emails: invitedEmails,
            });

            // Redirect to workspace
            if (response.data.data.redirect_url) {
                window.location.href = response.data.data.redirect_url;
            }

            return { success: true };
        } catch (error) {
            state.error = error.message;
            if (error.errors) setFieldErrors(error.errors);
            return { success: false, error: error.message };
        } finally {
            state.loading = false;
        }
    }

    // Validate password in real-time
    function validatePassword(password) {
        passwordValidation.minLength = password.length >= 8;
        passwordValidation.uppercase = /[A-Z]/.test(password);
        passwordValidation.lowercase = /[a-z]/.test(password);
        passwordValidation.number = /[0-9]/.test(password);
        passwordValidation.special = /[!@#$%^&*()_+\-=\[\]{}|;:,.<>?]/.test(password);
    }

    const isPasswordValid = computed(() => {
        return passwordValidation.minLength &&
            passwordValidation.uppercase &&
            passwordValidation.lowercase &&
            passwordValidation.number &&
            passwordValidation.special;
    });

    // Go to specific step (for navigation)
    function goToStep(step) {
        if (step <= state.step) {
            // Can only go back, not forward
            state.step = step;
        }
    }

    return {
        state,
        options,
        passwordValidation,
        isLoading,
        currentStep,
        hasError,
        isPasswordValid,
        loadOptions,
        clearErrors,
        registerEmail,
        verifyCode,
        resendCode,
        completeProfile,
        completeCompany,
        completeRegistration,
        validatePassword,
        goToStep,
    };
}
