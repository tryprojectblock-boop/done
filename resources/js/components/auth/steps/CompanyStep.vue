<template>
    <div>
        <h2 class="text-xl font-bold text-base-content mb-2">Tell us about your company</h2>
        <p class="text-base-content/60 mb-6">This helps us personalize your experience</p>

        <form @submit.prevent="handleSubmit">
            <!-- Company Name -->
            <div class="form-control mb-4">
                <label class="label" for="company_name">
                    <span class="label-text">Company Name</span>
                </label>
                <input
                    id="company_name"
                    v-model="form.company_name"
                    type="text"
                    class="input"
                    :class="{ 'input-error': errors.company_name }"
                    placeholder="Acme Inc."
                    required
                    autofocus
                />
                <label v-if="errors.company_name" class="label">
                    <span class="label-text-alt text-error">{{ errors.company_name[0] }}</span>
                </label>
            </div>

            <!-- Company Size -->
            <div class="form-control mb-4">
                <label class="label" for="company_size">
                    <span class="label-text">Company Size</span>
                </label>
                <select
                    id="company_size"
                    v-model="form.company_size"
                    class="select select-bordered w-full"
                    :class="{ 'select-error': errors.company_size }"
                    required
                >
                    <option value="" disabled>Select company size</option>
                    <option
                        v-for="size in options.companySizes"
                        :key="size.value"
                        :value="size.value"
                    >
                        {{ size.label }}
                    </option>
                </select>
                <label v-if="errors.company_size" class="label">
                    <span class="label-text-alt text-error">{{ errors.company_size[0] }}</span>
                </label>
            </div>

            <!-- Website URL -->
            <div class="form-control mb-4">
                <label class="label" for="website_url">
                    <span class="label-text">Website URL <span class="text-base-content/40">(optional)</span></span>
                </label>
                <div class="join w-full">
                    <select
                        v-model="form.website_protocol"
                        class="select select-bordered join-item w-24"
                    >
                        <option value="https">https://</option>
                        <option value="http">http://</option>
                    </select>
                    <input
                        id="website_url"
                        v-model="form.website_url"
                        type="text"
                        class="input input-bordered join-item flex-1"
                        :class="{ 'input-error': errors.website_url }"
                        placeholder="www.company.com"
                    />
                </div>
                <label v-if="errors.website_url" class="label">
                    <span class="label-text-alt text-error">{{ errors.website_url[0] }}</span>
                </label>
            </div>

            <!-- Industry Type -->
            <div class="form-control mb-6">
                <label class="label" for="industry_type">
                    <span class="label-text">Industry</span>
                </label>
                <div class="relative">
                    <input
                        id="industry_search"
                        v-model="industrySearch"
                        type="text"
                        class="input input-bordered w-full"
                        :class="{ 'input-error': errors.industry_type }"
                        placeholder="Search or select industry..."
                        @focus="showIndustryDropdown = true"
                        @blur="handleIndustryBlur"
                    />
                    <span class="icon-[tabler--chevron-down] size-4 absolute right-3 top-1/2 -translate-y-1/2 text-base-content/50"></span>

                    <!-- Dropdown -->
                    <div
                        v-show="showIndustryDropdown"
                        class="absolute z-50 mt-1 w-full bg-base-100 border border-base-300 rounded-lg shadow-lg max-h-60 overflow-auto"
                    >
                        <div
                            v-for="industry in filteredIndustries"
                            :key="industry.value"
                            class="px-4 py-2 cursor-pointer hover:bg-base-200 transition-colors"
                            :class="{ 'bg-primary/10 text-primary': form.industry_type === industry.value }"
                            @mousedown.prevent="selectIndustry(industry)"
                        >
                            {{ industry.label }}
                        </div>
                        <div
                            v-if="filteredIndustries.length === 0"
                            class="px-4 py-2 text-base-content/50"
                        >
                            No industries found
                        </div>
                    </div>
                </div>
                <label v-if="errors.industry_type" class="label">
                    <span class="label-text-alt text-error">{{ errors.industry_type[0] }}</span>
                </label>
                <label v-if="form.industry_type && !errors.industry_type" class="label">
                    <span class="label-text-alt text-success">
                        Selected: {{ selectedIndustryLabel }}
                    </span>
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
    options: Object,
});

const emit = defineEmits(['submit']);

const form = reactive({
    company_name: '',
    company_size: '',
    website_protocol: 'https',
    website_url: '',
    industry_type: '',
});

const industrySearch = ref('');
const showIndustryDropdown = ref(false);

const errors = computed(() => props.state.fieldErrors || {});

const filteredIndustries = computed(() => {
    const search = industrySearch.value.toLowerCase();
    if (!search) return props.options.industryTypes || [];

    return (props.options.industryTypes || []).filter(
        industry => industry.searchTerms.includes(search)
    );
});

const selectedIndustryLabel = computed(() => {
    const industry = (props.options.industryTypes || []).find(
        i => i.value === form.industry_type
    );
    return industry?.label || '';
});

const isFormValid = computed(() => {
    return form.company_name && form.company_size && form.industry_type;
});

function selectIndustry(industry) {
    form.industry_type = industry.value;
    industrySearch.value = industry.label;
    showIndustryDropdown.value = false;
}

function handleIndustryBlur() {
    // Delay to allow click on dropdown item
    setTimeout(() => {
        showIndustryDropdown.value = false;
    }, 200);
}

function handleSubmit() {
    if (!isFormValid.value) return;

    emit('submit', {
        company_name: form.company_name,
        company_size: form.company_size,
        website_protocol: form.website_protocol,
        website_url: form.website_url || null,
        industry_type: form.industry_type,
    });
}
</script>
