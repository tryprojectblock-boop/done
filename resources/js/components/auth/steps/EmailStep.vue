<template>
    <div>
        <h2 class="text-xl font-bold text-base-content mb-2">Create your account</h2>
        <p class="text-base-content/60 mb-6">Enter your work email to get started</p>

        <form @submit.prevent="handleSubmit">
            <div class="form-control">
                <label class="label" for="email">
                    <span class="label-text">Work Email Address</span>
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

            <!-- Invite Code Toggle -->
            <div class="flex items-center justify-between mt-4 py-2">
                <span class="text-sm text-base-content">I have an invite code</span>
                <input
                    type="checkbox"
                    v-model="hasInviteCode"
                    class="switch switch-primary"
                />
            </div>

            <!-- Invite Code Input (shown when toggle is on) -->
            <div v-if="hasInviteCode" class="form-control mt-2">
                <label class="label" for="inviteCode">
                    <span class="label-text">Invite Code <span class="text-error">*</span></span>
                </label>
                <div class="input flex items-center gap-2" :class="{ 'input-error': inviteCode && !isValidInviteCode, 'input-success': isValidInviteCode }">
                    <span class="icon-[tabler--ticket] size-5 text-base-content/50"></span>
                    <input
                        id="inviteCode"
                        v-model="inviteCode"
                        type="text"
                        class="grow bg-transparent border-0 focus:outline-none"
                        placeholder="Enter your invite code"
                    />
                    <span v-if="isValidInviteCode" class="icon-[tabler--check] size-5 text-success"></span>
                </div>
                <label class="label">
                    <span v-if="inviteCode && !isValidInviteCode" class="label-text-alt text-error">Invalid invite code</span>
                    <span v-else-if="isValidInviteCode" class="label-text-alt text-success">Valid invite code</span>
                    <span v-else class="label-text-alt text-base-content/50">Enter the code you received</span>
                </label>
            </div>

            <!-- Message when toggle is off -->
            <div v-else class="alert alert-warning mt-4">
                <span class="icon-[tabler--info-circle] size-5"></span>
                <span class="text-sm">An invite code is required to create an account. Toggle on if you have one.</span>
            </div>

            <button
                type="submit"
                class="btn btn-primary w-full mt-6"
                :disabled="!canSubmit"
            >
                <span v-if="state.loading" class="loading loading-spinner loading-sm"></span>
                <span v-else>Continue</span>
            </button>
        </form>

        <div class="divider text-sm text-base-content/50 my-6">No credit card required</div>

        <p class="text-xs text-base-content/50 text-center">
            By creating an account, you agree to our
            <a href="#" class="text-primary hover:underline">Terms of Service</a>
            and
            <a href="#" class="text-primary hover:underline">Privacy Policy</a>
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
const hasInviteCode = ref(false);
const inviteCode = ref('');

// Valid invite codes
const validCodes = ['1000', '2000', '3000', '4000'];

const errors = computed(() => props.state.fieldErrors || {});

// Check if entered invite code is valid
const isValidInviteCode = computed(() => {
    return validCodes.includes(inviteCode.value.trim());
});

// Determine if form can be submitted
const canSubmit = computed(() => {
    // Must have email, not be loading, toggle must be on, and invite code must be valid
    if (props.state.loading || !email.value) {
        return false;
    }
    // Invite code is always required - toggle must be on and code must be valid
    return hasInviteCode.value && isValidInviteCode.value;
});

function handleSubmit() {
    emit('submit', {
        email: email.value,
        inviteCode: hasInviteCode.value ? inviteCode.value : null
    });
}
</script>
