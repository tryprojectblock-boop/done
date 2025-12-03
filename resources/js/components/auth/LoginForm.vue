<template>
    <div class="w-full">
        <div class="card bg-base-100 shadow-xl">
            <div class="card-body">
                <h2 class="text-xl font-bold text-base-content mb-2">Welcome back</h2>
                <p class="text-base-content/60 mb-6">Sign in to your account</p>

                <!-- Error Alert -->
                <div v-if="error" class="alert alert-error mb-4">
                    <span class="icon-[tabler--alert-circle] size-5"></span>
                    <span>{{ error }}</span>
                    <button @click="error = null" class="btn btn-ghost btn-sm btn-circle">
                        <span class="icon-[tabler--x] size-4"></span>
                    </button>
                </div>

                <form @submit.prevent="handleSubmit">
                    <!-- Email -->
                    <div class="form-control mb-4">
                        <label class="label" for="email">
                            <span class="label-text">Email Address</span>
                        </label>
                        <div class="input flex items-center gap-2" :class="{ 'input-error': errors.email }">
                            <span class="icon-[tabler--mail] size-5 text-base-content/50"></span>
                            <input
                                id="email"
                                v-model="form.email"
                                type="email"
                                class="grow bg-transparent border-0 focus:outline-none"
                                placeholder="you@company.com"
                                required
                                autofocus
                            />
                        </div>
                        <label v-if="errors.email" class="label">
                            <span class="label-text-alt text-error">{{ errors.email[0] }}</span>
                        </label>
                    </div>

                    <!-- Password -->
                    <div class="form-control mb-4">
                        <label class="label" for="password">
                            <span class="label-text">Password</span>
                        </label>
                        <div class="input flex items-center gap-2" :class="{ 'input-error': errors.password }">
                            <span class="icon-[tabler--lock] size-5 text-base-content/50"></span>
                            <input
                                id="password"
                                v-model="form.password"
                                :type="showPassword ? 'text' : 'password'"
                                class="grow bg-transparent border-0 focus:outline-none"
                                placeholder="Enter your password"
                                required
                            />
                            <button
                                type="button"
                                class="btn btn-ghost btn-xs btn-circle"
                                @click="showPassword = !showPassword"
                            >
                                <span :class="showPassword ? 'icon-[tabler--eye-off]' : 'icon-[tabler--eye]'" class="size-4"></span>
                            </button>
                        </div>
                        <label v-if="errors.password" class="label">
                            <span class="label-text-alt text-error">{{ errors.password[0] }}</span>
                        </label>
                    </div>

                    <!-- Remember Me & Forgot Password -->
                    <div class="flex items-center justify-between mb-6">
                        <label class="label cursor-pointer gap-2">
                            <input
                                v-model="form.remember"
                                type="checkbox"
                                class="checkbox checkbox-sm checkbox-primary"
                            />
                            <span class="label-text">Remember me</span>
                        </label>
                        <a href="/forgot-password" class="text-sm text-primary hover:underline">
                            Forgot password?
                        </a>
                    </div>

                    <button
                        type="submit"
                        class="btn btn-primary w-full"
                        :disabled="loading"
                    >
                        <span v-if="loading" class="loading loading-spinner loading-sm"></span>
                        <span v-else>Sign In</span>
                    </button>
                </form>
            </div>
        </div>

        <!-- Footer -->
        <p class="mt-6 text-center text-sm text-base-content/60">
            Don't have an account?
            <a href="/register" class="text-primary font-medium hover:underline">Create one</a>
        </p>
    </div>
</template>

<script setup>
import { ref, reactive } from 'vue';
import api from '@/services/api';

const form = reactive({
    email: '',
    password: '',
    remember: false,
});

const showPassword = ref(false);
const loading = ref(false);
const error = ref(null);
const errors = ref({});

async function handleSubmit() {
    loading.value = true;
    error.value = null;
    errors.value = {};

    try {
        const response = await api.post('/auth/login', form);

        if (response.data.success) {
            // Redirect to dashboard or workspace
            window.location.href = response.data.data.redirect_url || '/dashboard';
        }
    } catch (err) {
        error.value = err.message || 'Invalid credentials';
        if (err.errors) {
            errors.value = err.errors;
        }
    } finally {
        loading.value = false;
    }
}
</script>
