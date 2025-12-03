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

            <button
                type="submit"
                class="btn btn-primary w-full mt-6"
                :disabled="state.loading || !email"
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

const errors = computed(() => props.state.fieldErrors || {});

function handleSubmit() {
    emit('submit', { email: email.value });
}
</script>
