<template>
    <div>
        <h2 class="text-xl font-bold text-base-content mb-2">Set up your profile</h2>
        <p class="text-base-content/60 mb-6">Tell us about yourself</p>

        <form @submit.prevent="handleSubmit">
            <!-- Email (readonly) -->
            <div class="form-control mb-4">
                <label class="label">
                    <span class="label-text">Email Address</span>
                </label>
                <div class="input flex items-center gap-2 bg-base-200">
                    <span class="icon-[tabler--mail] size-5 text-base-content/50"></span>
                    <input
                        type="email"
                        class="grow bg-transparent border-0 cursor-not-allowed"
                        :value="state.email"
                        disabled
                    />
                    <span class="icon-[tabler--lock] size-4 text-base-content/40"></span>
                </div>
            </div>

            <!-- First Name -->
            <div class="form-control mb-4">
                <label class="label" for="first_name">
                    <span class="label-text">First Name</span>
                </label>
                <input
                    id="first_name"
                    v-model="form.first_name"
                    type="text"
                    class="input"
                    :class="{ 'input-error': errors.first_name }"
                    placeholder="John"
                    required
                    autofocus
                />
                <label v-if="errors.first_name" class="label">
                    <span class="label-text-alt text-error">{{ errors.first_name[0] }}</span>
                </label>
            </div>

            <!-- Last Name -->
            <div class="form-control mb-4">
                <label class="label" for="last_name">
                    <span class="label-text">Last Name</span>
                </label>
                <input
                    id="last_name"
                    v-model="form.last_name"
                    type="text"
                    class="input"
                    :class="{ 'input-error': errors.last_name }"
                    placeholder="Doe"
                    required
                />
                <label v-if="errors.last_name" class="label">
                    <span class="label-text-alt text-error">{{ errors.last_name[0] }}</span>
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
                        placeholder="Create a strong password"
                        required
                        @input="handlePasswordInput"
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

                <!-- Password Strength Indicator -->
                <div class="mt-2">
                    <div class="flex gap-1 mb-2">
                        <div
                            v-for="n in 5"
                            :key="n"
                            class="h-1 flex-1 rounded-full transition-colors"
                            :class="getStrengthBarColor(n)"
                        ></div>
                    </div>
                    <p class="text-xs" :class="strengthTextColor">
                        {{ strengthText }}
                    </p>
                </div>

                <!-- Password Requirements -->
                <div class="mt-3 space-y-1">
                    <div
                        v-for="req in requirements"
                        :key="req.key"
                        class="flex items-center gap-2 text-xs"
                        :class="passwordValidation[req.key] ? 'text-success' : 'text-base-content/50'"
                    >
                        <span
                            :class="passwordValidation[req.key] ? 'icon-[tabler--circle-check]' : 'icon-[tabler--circle]'"
                            class="size-4"
                        ></span>
                        {{ req.label }}
                    </div>
                </div>
            </div>

            <!-- Confirm Password -->
            <div class="form-control mb-6">
                <label class="label" for="password_confirmation">
                    <span class="label-text">Confirm Password</span>
                </label>
                <div class="input flex items-center gap-2" :class="{ 'input-error': passwordMismatch }">
                    <span class="icon-[tabler--lock-check] size-5 text-base-content/50"></span>
                    <input
                        id="password_confirmation"
                        v-model="form.password_confirmation"
                        :type="showConfirmPassword ? 'text' : 'password'"
                        class="grow bg-transparent border-0 focus:outline-none"
                        placeholder="Confirm your password"
                        required
                    />
                    <button
                        type="button"
                        class="btn btn-ghost btn-xs btn-circle"
                        @click="showConfirmPassword = !showConfirmPassword"
                    >
                        <span :class="showConfirmPassword ? 'icon-[tabler--eye-off]' : 'icon-[tabler--eye]'" class="size-4"></span>
                    </button>
                </div>
                <label v-if="passwordMismatch" class="label">
                    <span class="label-text-alt text-error">Passwords do not match</span>
                </label>
            </div>

            <button
                type="submit"
                class="btn btn-primary w-full"
                :disabled="state.loading || !isFormValid"
            >
                <span v-if="state.loading" class="loading loading-spinner loading-sm"></span>
                <span v-else>Continue</span>
            </button>
        </form>
    </div>
</template>

<script setup>
import { ref, reactive, computed } from 'vue';

const props = defineProps({
    state: Object,
    passwordValidation: Object,
    isPasswordValid: Boolean,
});

const emit = defineEmits(['submit', 'validate-password']);

const form = reactive({
    first_name: '',
    last_name: '',
    password: '',
    password_confirmation: '',
});

const showPassword = ref(false);
const showConfirmPassword = ref(false);

const errors = computed(() => props.state.fieldErrors || {});

const requirements = [
    { key: 'minLength', label: 'At least 8 characters' },
    { key: 'uppercase', label: 'One uppercase letter' },
    { key: 'lowercase', label: 'One lowercase letter' },
    { key: 'number', label: 'One number' },
    { key: 'special', label: 'One special character' },
];

const passwordMismatch = computed(() => {
    return form.password_confirmation && form.password !== form.password_confirmation;
});

const passwordsMatch = computed(() => {
    return form.password && form.password === form.password_confirmation;
});

const strengthLevel = computed(() => {
    const checks = Object.values(props.passwordValidation);
    return checks.filter(Boolean).length;
});

const strengthText = computed(() => {
    const texts = ['Very Weak', 'Weak', 'Fair', 'Good', 'Strong'];
    return texts[Math.max(0, strengthLevel.value - 1)] || 'Enter a password';
});

const strengthTextColor = computed(() => {
    const colors = ['text-error', 'text-error', 'text-warning', 'text-info', 'text-success'];
    return strengthLevel.value > 0 ? colors[strengthLevel.value - 1] : 'text-base-content/50';
});

function getStrengthBarColor(index) {
    if (index <= strengthLevel.value) {
        const colors = ['bg-error', 'bg-error', 'bg-warning', 'bg-info', 'bg-success'];
        return colors[strengthLevel.value - 1];
    }
    return 'bg-base-300';
}

const isFormValid = computed(() => {
    return form.first_name &&
        form.last_name &&
        props.isPasswordValid &&
        passwordsMatch.value;
});

function handlePasswordInput() {
    emit('validate-password', form.password);
}

function handleSubmit() {
    if (!isFormValid.value) return;

    emit('submit', {
        first_name: form.first_name,
        last_name: form.last_name,
        password: form.password,
        password_confirmation: form.password_confirmation,
    });
}
</script>
