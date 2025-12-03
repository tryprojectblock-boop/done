<template>
    <div>
        <div class="text-center mb-6">
            <div class="size-16 rounded-full bg-primary/10 flex items-center justify-center mx-auto mb-4">
                <span class="icon-[tabler--mail-check] size-8 text-primary"></span>
            </div>
            <h2 class="text-xl font-bold text-base-content mb-2">Check your email</h2>
            <p class="text-base-content/60">
                We sent an activation code to<br />
                <strong class="text-base-content">{{ state.email }}</strong>
            </p>
        </div>

        <form @submit.prevent="handleSubmit">
            <div class="form-control">
                <label class="label" for="code">
                    <span class="label-text">Activation Code</span>
                </label>
                <input
                    id="code"
                    v-model="code"
                    type="text"
                    class="input input-lg text-center tracking-[0.5em] font-mono uppercase"
                    :class="{ 'input-error': hasError }"
                    placeholder="______"
                    maxlength="6"
                    required
                    autofocus
                    @input="handleCodeInput"
                />
                <label v-if="hasError" class="label">
                    <span class="label-text-alt text-error">{{ state.error }}</span>
                </label>
            </div>

            <button
                type="submit"
                class="btn btn-primary w-full mt-6"
                :disabled="state.loading || code.length !== 6"
            >
                <span v-if="state.loading" class="loading loading-spinner loading-sm"></span>
                <span v-else>Verify Email</span>
            </button>
        </form>

        <div class="mt-6 text-center">
            <p class="text-sm text-base-content/60 mb-2">Didn't receive the code?</p>
            <button
                type="button"
                class="btn btn-ghost btn-sm"
                :disabled="resendCooldown > 0 || state.loading"
                @click="handleResend"
            >
                <span v-if="resendCooldown > 0">Resend in {{ resendCooldown }}s</span>
                <span v-else>Resend Code</span>
            </button>
        </div>

        <p class="text-xs text-base-content/50 text-center mt-4">
            The code expires in 72 hours
        </p>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    state: Object,
});

const emit = defineEmits(['submit', 'resend']);

const code = ref('');
const resendCooldown = ref(0);
let cooldownInterval = null;

const hasError = computed(() => !!props.state.error);

function handleCodeInput(event) {
    // Only allow alphanumeric characters
    code.value = event.target.value.replace(/[^a-zA-Z0-9]/g, '').toUpperCase();
}

function handleSubmit() {
    emit('submit', { code: code.value });
}

async function handleResend() {
    emit('resend');
    startCooldown();
}

function startCooldown() {
    resendCooldown.value = 60;
    cooldownInterval = setInterval(() => {
        resendCooldown.value--;
        if (resendCooldown.value <= 0) {
            clearInterval(cooldownInterval);
        }
    }, 1000);
}

onUnmounted(() => {
    if (cooldownInterval) {
        clearInterval(cooldownInterval);
    }
});
</script>
