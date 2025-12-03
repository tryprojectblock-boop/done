import axios from 'axios';

const api = axios.create({
    baseURL: '/api/v1',
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
    },
});

// Add CSRF token to requests
api.interceptors.request.use((config) => {
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (token) {
        config.headers['X-CSRF-TOKEN'] = token;
    }
    return config;
});

// Handle responses
api.interceptors.response.use(
    (response) => response,
    (error) => {
        const message = error.response?.data?.message || 'An error occurred';

        // Handle validation errors
        if (error.response?.status === 422) {
            const errors = error.response.data.errors || {};
            return Promise.reject({ message, errors, status: 422 });
        }

        // Handle other errors
        return Promise.reject({ message, status: error.response?.status || 500 });
    }
);

export default api;

// Auth API endpoints
export const authApi = {
    // Registration
    registerEmail: (email) => api.post('/auth/registration/email', { email }),
    verifyCode: (email, code) => api.post('/auth/registration/verify-code', { email, code }),
    resendCode: (email) => api.post('/auth/registration/resend-code', { email }),
    getStatus: (uuid) => api.get(`/auth/registration/${uuid}/status`),
    completeProfile: (uuid, data) => api.post(`/auth/registration/${uuid}/profile`, data),
    completeCompany: (uuid, data) => api.post(`/auth/registration/${uuid}/company`, data),
    completeRegistration: (uuid, data) => api.post(`/auth/registration/${uuid}/complete`, data),
    getOptions: () => api.get('/auth/registration/options'),
};
