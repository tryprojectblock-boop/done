@extends('layouts.app')

@section('content')
<div class="p-4 md:p-6">
    <div class="max-w-2xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
            <a href="{{ route('guests.index') }}" class="btn btn-ghost btn-sm gap-1 mb-4">
                <span class="icon-[tabler--arrow-left] size-4"></span>
                Back to Guests
            </a>
            <h1 class="text-2xl font-bold text-base-content">Invite Guest</h1>
            <p class="text-base-content/60">Invite an external consultant or client to collaborate</p>
        </div>

        <!-- Form -->
        <form id="add-guest-form" enctype="multipart/form-data">
            @csrf
            <div class="card bg-base-100 shadow">
                <div class="card-body">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- First Name -->
                        <div class="form-control">
                            <label class="label" for="guest-first-name">
                                <span class="label-text font-medium">First Name <span class="text-error">*</span></span>
                            </label>
                            <input type="text" name="first_name" id="guest-first-name" placeholder="John" class="input input-bordered" required pattern="[A-Za-z\s\-']+" title="Only letters, spaces, hyphens and apostrophes allowed">
                            <span class="label">
                                <span class="label-text-alt text-base-content/50">Letters only</span>
                            </span>
                        </div>

                        <!-- Last Name -->
                        <div class="form-control">
                            <label class="label" for="guest-last-name">
                                <span class="label-text font-medium">Last Name <span class="text-base-content/50 font-normal">(Optional)</span></span>
                            </label>
                            <input type="text" name="last_name" id="guest-last-name" placeholder="Doe" class="input input-bordered" pattern="[A-Za-z\s\-']*" title="Only letters, spaces, hyphens and apostrophes allowed">
                            <span class="label">
                                <span class="label-text-alt text-base-content/50">Letters only</span>
                            </span>
                        </div>
                    </div>

                    <!-- Email -->
                    <div class="form-control">
                        <label class="label" for="guest-email">
                            <span class="label-text font-medium">Email Address <span class="text-error">*</span></span>
                        </label>
                        <input type="email" name="email" id="guest-email" placeholder="john@example.com" class="input input-bordered" required>
                    </div>

                    <!-- Type -->
                    <div class="form-control">
                        <label class="label" for="guest-type">
                            <span class="label-text font-medium">Type <span class="text-error">*</span></span>
                        </label>
                        <select name="type" id="guest-type" class="select select-bordered" required>
                            <option value="" disabled selected>Select type</option>
                            @foreach($types as $key => $type)
                                <option value="{{ $key }}">{{ $type['label'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Client Portal Access -->
                    <div class="form-control">
                        <label class="label" for="guest-portal-access">
                            <span class="label-text font-medium">Client Portal Access <span class="text-error">*</span></span>
                        </label>
                        <select name="client_portal_access" id="guest-portal-access" class="select select-bordered" required>
                            <option value="" disabled selected>Select option</option>
                            <option value="0">No</option>
                            <option value="1">Yes</option>
                        </select>
                    </div>

                    <!-- Tags -->
                    <div class="form-control">
                        <label class="label" for="tag-input">
                            <span class="label-text font-medium">Tags <span class="text-base-content/50 font-normal">(Optional)</span></span>
                        </label>
                        <div id="tags-container" class="flex flex-wrap gap-2 p-3 border border-base-300 rounded-lg min-h-[3rem] focus-within:border-primary">
                            <input type="text" id="tag-input" placeholder="Type and press Enter to add tags..." class="flex-1 min-w-[150px] outline-none bg-transparent text-sm">
                        </div>
                        <input type="hidden" name="tags" id="tags-hidden">
                        <span class="label">
                            <span class="label-text-alt text-base-content/50">Press Enter or comma to add a tag</span>
                        </span>
                    </div>

                    <div class="divider">Additional Information</div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Phone -->
                        <div class="form-control">
                            <label class="label" for="guest-phone">
                                <span class="label-text font-medium">Phone</span>
                            </label>
                            <input type="tel" name="phone" id="guest-phone" placeholder="+1 (555) 123-4567" class="input input-bordered">
                        </div>

                        <!-- Company Name -->
                        <div class="form-control">
                            <label class="label" for="guest-company">
                                <span class="label-text font-medium">Company Name</span>
                            </label>
                            <input type="text" name="company_name" id="guest-company" placeholder="Acme Inc." class="input input-bordered">
                        </div>
                    </div>

                    <!-- Position -->
                    <div class="form-control">
                        <label class="label" for="guest-position">
                            <span class="label-text font-medium">Position / Title</span>
                        </label>
                        <input type="text" name="position" id="guest-position" placeholder="CEO, Designer, etc." class="input input-bordered">
                    </div>

                    <!-- Notes -->
                    <div class="form-control">
                        <label class="label" for="guest-notes">
                            <span class="label-text font-medium">Notes</span>
                        </label>
                        <textarea name="notes" id="guest-notes" rows="3" placeholder="Any additional notes about this guest..." class="textarea textarea-bordered"></textarea>
                    </div>
                </div>

                <div class="card-body border-t border-base-200 pt-4">
                    <div class="flex justify gap-3">
                        <button type="submit" id="submit-btn" class="btn btn-primary">
                            <span class="icon-[tabler--send] size-5"></span>
                            Send Invitation
                        </button>
                        <a href="{{ route('guests.index') }}" class="btn btn-ghost">Cancel</a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Tags functionality
const tagsContainer = document.getElementById('tags-container');
const tagInput = document.getElementById('tag-input');
const tagsHidden = document.getElementById('tags-hidden');
let tags = [];

tagInput.addEventListener('keydown', function(e) {
    if (e.key === 'Enter' || e.key === ',') {
        e.preventDefault();
        addTag(this.value.trim());
        this.value = '';
    }
    if (e.key === 'Backspace' && this.value === '' && tags.length > 0) {
        removeTag(tags.length - 1);
    }
});

function addTag(tag) {
    if (tag && !tags.includes(tag)) {
        tags.push(tag);
        renderTags();
        updateHiddenInput();
    }
}

function removeTag(index) {
    tags.splice(index, 1);
    renderTags();
    updateHiddenInput();
}

function renderTags() {
    const tagElements = tagsContainer.querySelectorAll('.tag-item');
    tagElements.forEach(el => el.remove());

    tags.forEach((tag, index) => {
        const tagEl = document.createElement('span');
        tagEl.className = 'tag-item badge badge-primary gap-1';
        tagEl.innerHTML = `
            ${tag}
            <button type="button" class="hover:text-primary-content/70" onclick="removeTag(${index})">
                <span class="icon-[tabler--x] size-3"></span>
            </button>
        `;
        tagsContainer.insertBefore(tagEl, tagInput);
    });
}

function updateHiddenInput() {
    tagsHidden.value = tags.join(',');
}

// Form submission
document.getElementById('add-guest-form').addEventListener('submit', function(e) {
    e.preventDefault();

    const submitBtn = document.getElementById('submit-btn');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="loading loading-spinner loading-sm"></span> Sending...';

    const formData = new FormData(this);

    fetch('{{ route("guests.store") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message || 'Invitation sent successfully!', 'success');
            setTimeout(() => {
                window.location.href = data.redirect || '{{ route("guests.index") }}';
            }, 1500);
        } else if (data.errors) {
            // Show validation errors
            let errorMessages = [];
            for (const [field, messages] of Object.entries(data.errors)) {
                errorMessages.push(...messages);
            }
            showToast(errorMessages.join(', '), 'error');
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        } else {
            showToast(data.error || 'An error occurred', 'error');
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred. Please try again.', 'error');
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    });
});
</script>
@endpush
