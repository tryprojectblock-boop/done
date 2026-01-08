<template>
    <div>
        <h2 class="text-2xl text-center font-bold text-base-content mb-2">Create your account</h2>
        <p class="text-center text-base-content/60 mb-6 text-secondary-color">Enter your work email to get started</p>

        <form @submit.prevent="handleSubmit">
            <div class="form-control">
                <label class="label" for="email">
                    <span class="label-text label-text-alt">Work Email Address</span> 
                </label>
                <div class="input flex items-center gap-2" :class="{ 'input-error': errors.email }">
                    <span class="icon-[tabler--mail] size-5 text-base-content/50"></span>
                    <input
                        id="email"
                        v-model="email"
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

            <!-- Invite Code Input (always visible) -->
            <div class="form-control mt-4">
                <label class="label" for="inviteCode">
                    <span class="label-text label-text-alt">Invite Code <span class="text-error">*</span></span>
                </label>
                <div class="input flex items-center gap-2" :class="{ 'input-error': inviteCode && !isValidInviteCode, 'input-success': isValidInviteCode }">
                    <span class="icon-[tabler--ticket] size-5 text-base-content/50"></span>
                    <input
                        id="inviteCode"
                        v-model="inviteCode"
                        type="text"
                        class="grow bg-transparent border-0 focus:outline-none"
                        placeholder="Enter the code you received"
                    />
                    <span v-if="isValidInviteCode" class="icon-[tabler--check] size-5 text-success"></span>
                </div>
                <label v-if="inviteCode" class="label">
                    <span v-if="!isValidInviteCode" class="label-text-alt text-error">Invalid invite code</span>
                    <span v-else class="label-text-alt text-success">Valid invite code</span>
                </label>
            </div>

            <!-- Info message -->
            <div class="info-div mt-2">
                <span class="icon-[tabler--info-circle] size-5"></span>
                <span class="text-xs">An invite code is required to create an account.</span>
            </div>

            <p class="text-base text-base-content/50 mt-3 terms-text">
            By creating an account, you agree to our
            <a href="#" class="text-primary hover:underline">Terms of Service</a>
            and
            <a href="#" class="text-primary hover:underline">Privacy Policy</a>
        </p>

            <button
                type="submit"
                class="btn btn-primary-color w-full mt-6 border-0"
                :disabled="!canSubmit"
            >
                <span v-if="state.loading" class="loading loading-spinner loading-sm"></span>
                <span v-else>Continue</span>
            </button>
        </form>

        <div class="text-sm text-base-content/50 mt-2 text-center"><i class="fa-solid fa-credit-card"></i> No credit card required</div>
                <!-- Footer -->
        <p class="mt-6 text-center text-sm text-base-content/60 text-secondary-color">
            Already have an account?
            <a href="/login" class="text-primary text-link-color font-medium hover:underline">Sign in</a>
        </p>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
    state: Object,
});

const emit = defineEmits(['submit']);

const email = ref('');
const inviteCode = ref('');

// Valid invite codes for register/signup
const validCodes = ['1000', '2000', '3000', '4000'];

const errors = computed(() => props.state.fieldErrors || {});

// Check if entered invite code is valid
const isValidInviteCode = computed(() => {
    return validCodes.includes(inviteCode.value.trim());
});

// Determine if form can be submitted
const canSubmit = computed(() => {
    // Must have email, not be loading, and invite code must be valid
    if (props.state.loading || !email.value) {
        return false;
    }
    return isValidInviteCode.value;
});

function handleSubmit() {
    emit('submit', {
        email: email.value,
        inviteCode: inviteCode.value
    });
}
</script>
