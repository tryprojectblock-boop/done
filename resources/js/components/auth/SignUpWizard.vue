<template>
    <div class="w-full">
        <!-- Progress Steps -->
        <div class="mb-8" v-if="currentStep >= 2">
            <div class="flex items-center justify-between">
                <template v-for="step in 4" :key="step">
                    <div class="flex items-center">
                        <div
                            :class="[
                                'size-8 rounded-full flex items-center justify-center text-sm font-medium transition-colors',
                                currentStep > step + 1 ? ' completed-step ' : '',
                                currentStep >= step + 1
                                    ? 'bg-primary text-primary-content'
                                    : currentStep === step + 1 - 0.5
                                    ? 'bg-primary/20 text-primary'
                                    : 'circle-border text-base-content/50'
                            ]"
                        >
                            <span v-if="currentStep > step + 1" class="icon-[tabler--check] size-4"></span>
                            <span v-else>{{ step }}</span>
                        </div>
                    </div>
                    <div
                        v-if="step < 4"
                        :class="[
                            'flex-1 h-0.5 mx-2',
                            currentStep > step + 1 ? 'bg-primary' : 'border-bg-line'
                        ]"
                    ></div>
                </template>
            </div>
            <div class="flex justify-between mt-2  font-semibold text-sm text-base-content/60 text-secondary-color">
                <span>Profile</span>
                <span>Company</span>
                <span>Team</span>
                <span>Done</span>
            </div>
        </div>

        <!-- Card Container -->
        <div class="card bg-base-100 shadow-xl">
            <div class="card-body">
                <!-- Error Alert -->
                <div v-if="state.error" class="alert alert-error mb-4">
                    <span class="icon-[tabler--alert-circle] size-5"></span>
                    <span>{{ state.error }}</span>
                    <button @click="clearErrors" class="btn btn-ghost btn-sm btn-circle">
                        <span class="icon-[tabler--x] size-4"></span>
                    </button>
                </div>

                <!-- Step Components -->
                <Transition name="fade" mode="out-in">
                    <component
                        :is="currentStepComponent"
                        :state="state"
                        :options="options"
                        :password-validation="passwordValidation"
                        :is-password-valid="isPasswordValid"
                        @submit="handleStepSubmit"
                        @resend="resendCode"
                        @validate-password="validatePassword"
                    />
                </Transition>
            </div>
        </div>

    </div>
</template>

<script setup>
import { computed, onMounted, markRaw } from 'vue';
import { useRegistration } from '@/composables/useRegistration';
import EmailStep from './steps/EmailStep.vue';
import VerifyCodeStep from './steps/VerifyCodeStep.vue';
import ProfileStep from './steps/ProfileStep.vue';
import CompanyStep from './steps/CompanyStep.vue';
import InviteStep from './steps/InviteStep.vue';

const {
    state,
    options,
    passwordValidation,
    currentStep,
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
} = useRegistration();

// Step components mapping
const stepComponents = {
    1: markRaw(EmailStep),
    1.5: markRaw(VerifyCodeStep),
    2: markRaw(ProfileStep),
    3: markRaw(CompanyStep),
    4: markRaw(InviteStep),
};

const currentStepComponent = computed(() => {
    return stepComponents[currentStep.value] || stepComponents[1];
});

// Handle step submissions
async function handleStepSubmit(data) {
    switch (currentStep.value) {
        case 1:
            await registerEmail(data.email);
            break;
        case 1.5:
            await verifyCode(data.code);
            break;
        case 2:
            await completeProfile(data);
            break;
        case 3:
            await completeCompany(data);
            break;
        case 4:
            await completeRegistration(data.emails);
            break;
    }
}

onMounted(() => {
    loadOptions();
});
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s ease, transform 0.2s ease;
}

.fade-enter-from {
    opacity: 0;
    transform: translateX(10px);
}

.fade-leave-to {
    opacity: 0;
    transform: translateX(-10px);
}
</style>
