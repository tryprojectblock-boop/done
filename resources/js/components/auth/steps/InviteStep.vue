<template>
    <div>
        <h2 class="text-xl font-bold text-base-content mb-2">Invite your team</h2>
        <p class="text-base-content/60 mb-6">Add team members to collaborate with you</p>

        <form @submit.prevent="handleSubmit">
            <!-- Email Inputs -->
            <div class="space-y-3 mb-6">
                <div
                    v-for="(email, index) in emails"
                    :key="index"
                    class="form-control"
                >
                    <div class="flex gap-2">
                        <div class="input flex items-center gap-2 flex-1" :class="{ 'input-error': emailErrors[index] }">
                            <span class="icon-[tabler--mail] size-5 text-base-content/50"></span>
                            <input
                                v-model="emails[index]"
                                type="email"
                                class="grow bg-transparent border-0 focus:outline-none"
                                :placeholder="index === 0 ? 'colleague@company.com' : 'Add another teammate'"
                                @blur="validateEmail(index)"
                            />
                            <span
                                v-if="emails[index] && isValidEmail(emails[index])"
                                class="icon-[tabler--check] size-4 text-success"
                            ></span>
                        </div>
                        <button
                            v-if="index > 0"
                            type="button"
                            class="btn btn-ghost btn-square"
                            @click="removeEmail(index)"
                        >
                            <span class="icon-[tabler--trash] size-4 text-base-content/50"></span>
                        </button>
                    </div>
                    <label v-if="emailErrors[index]" class="label">
                        <span class="label-text-alt text-error">{{ emailErrors[index] }}</span>
                    </label>
                </div>
            </div>

            <!-- Add More Button -->
            <button
                v-if="emails.length < maxEmails"
                type="button"
                class="btn btn-ghost btn-sm mb-6"
                @click="addEmail"
            >
                <span class="icon-[tabler--plus] size-4"></span>
                Add another teammate
            </button>

            <div class="divider text-sm text-base-content/50 my-6">
                {{ validEmailCount }} invitation{{ validEmailCount !== 1 ? 's' : '' }} ready to send
            </div>

            <!-- Action Buttons -->
            <div class="space-y-3">
                <button
                    type="submit"
                    class="btn btn-primary w-full"
                    :disabled="state.loading"
                >
                    <span v-if="state.loading" class="loading loading-spinner loading-sm"></span>
                    <span v-else-if="validEmailCount > 0">
                        Send Invitations & Continue
                    </span>
                    <span v-else>
                        Skip & Continue
                    </span>
                </button>

                <button
                    v-if="validEmailCount > 0"
                    type="button"
                    class="btn btn-ghost w-full"
                    :disabled="state.loading"
                    @click="handleSkip"
                >
                    Skip for now
                </button>
            </div>
        </form>

        <p class="text-xs text-base-content/50 text-center mt-6">
            You can always invite more team members later from your workspace settings.
        </p>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
    state: Object,
});

const emit = defineEmits(['submit']);

const maxEmails = 20;
const defaultEmailCount = 5;

// Initialize with 5 empty email slots
const emails = ref(Array(defaultEmailCount).fill(''));
const emailErrors = ref({});

const validEmailCount = computed(() => {
    return emails.value.filter(email => isValidEmail(email)).length;
});

function isValidEmail(email) {
    if (!email) return false;
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

function validateEmail(index) {
    const email = emails.value[index];
    if (email && !isValidEmail(email)) {
        emailErrors.value[index] = 'Please enter a valid email address';
    } else {
        delete emailErrors.value[index];
    }
}

function addEmail() {
    if (emails.value.length < maxEmails) {
        emails.value.push('');
    }
}

function removeEmail(index) {
    emails.value.splice(index, 1);
    delete emailErrors.value[index];
}

function handleSubmit() {
    // Validate all emails
    let hasErrors = false;
    emails.value.forEach((email, index) => {
        if (email && !isValidEmail(email)) {
            emailErrors.value[index] = 'Please enter a valid email address';
            hasErrors = true;
        }
    });

    if (hasErrors) return;

    const validEmails = emails.value.filter(email => isValidEmail(email));
    emit('submit', { emails: validEmails });
}

function handleSkip() {
    emit('submit', { emails: [] });
}
</script>
