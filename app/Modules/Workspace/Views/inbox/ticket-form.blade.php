@extends('layouts.app')

@section('content')
<div class="p-4 md:p-6">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex items-center gap-2 text-sm text-base-content/60 mb-2">
                <a href="{{ route('dashboard') }}" class="hover:text-primary">Dashboard</a>
                <span class="icon-[tabler--chevron-right] size-4"></span>
                <a href="{{ route('workspace.show', $workspace) }}" class="hover:text-primary">{{ $workspace->name }}</a>
                <span class="icon-[tabler--chevron-right] size-4"></span>
                <span>Ticket Form</span>
            </div>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <a href="{{ route('workspace.show', $workspace) }}" class="btn btn-ghost btn-sm btn-square">
                        <span class="icon-[tabler--arrow-left] size-5"></span>
                    </a>
                    <div>
                        <h1 class="text-2xl font-bold text-base-content">Ticket Form Settings</h1>
                        <p class="text-sm text-base-content/60">Configure your public ticket submission form</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ $ticketForm->public_url }}" target="_blank" class="btn btn-outline btn-sm">
                        <span class="icon-[tabler--eye] size-4"></span>
                        Preview
                    </a>
                    @if($ticketForm->isPublished())
                        <form action="{{ route('workspace.ticket-form.unpublish', $workspace) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="btn btn-warning btn-sm" onclick="return confirm('Are you sure you want to unpublish this form? You will be able to edit it again.')">
                                <span class="icon-[tabler--lock-open] size-4"></span>
                                Unpublish
                            </button>
                        </form>
                        <span class="badge badge-success">Published</span>
                    @else
                        <form action="{{ route('workspace.ticket-form.publish', $workspace) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="btn btn-primary btn-sm" onclick="return confirm('Are you sure you want to publish this form? The form will be locked and cannot be edited until unpublished.')">
                                <span class="icon-[tabler--lock] size-4"></span>
                                Publish Form
                            </button>
                        </form>
                        <span class="badge badge-neutral">Draft</span>
                    @endif
                </div>
            </div>
        </div>

        @if($ticketForm->isPublished())
        <div class="alert alert-warning mb-6">
            <span class="icon-[tabler--lock] size-5"></span>
            <div>
                <h3 class="font-bold">Form is Published</h3>
                <p class="text-sm">This form is locked and cannot be edited. Unpublish to make changes.</p>
            </div>
        </div>
        @endif

        <form action="{{ route('workspace.save-ticket-form', $workspace) }}" method="POST" class="space-y-6">
            @csrf
            <fieldset {{ $ticketForm->isPublished() ? 'disabled' : '' }}>

            <!-- Form Info -->
            <div class="card bg-base-100 shadow">
                <div class="card-body">
                    <h3 class="font-semibold mb-4 flex items-center gap-2">
                        <span class="icon-[tabler--info-circle] size-5"></span>
                        Form Information
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium">Form Name</span>
                            </label>
                            <input type="text" name="name" value="{{ $ticketForm->name }}" class="input input-bordered" required>
                        </div>

                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium">Status</span>
                            </label>
                            <select name="is_active" class="select select-bordered">
                                <option value="1" {{ $ticketForm->is_active ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ !$ticketForm->is_active ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>

                        <div class="form-control md:col-span-2">
                            <label class="label">
                                <span class="label-text font-medium">Description</span>
                            </label>
                            <textarea name="description" class="textarea textarea-bordered" rows="2">{{ $ticketForm->description }}</textarea>
                        </div>

                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium">Submit Button Text</span>
                            </label>
                            <input type="text" name="submit_button_text" value="{{ $ticketForm->submit_button_text }}" class="input input-bordered">
                        </div>

                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium">Success Message</span>
                            </label>
                            <input type="text" name="success_message" value="{{ $ticketForm->success_message }}" class="input input-bordered">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Fields -->
            <div class="card bg-base-100 shadow">
                <div class="card-body">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-semibold flex items-center gap-2">
                            <span class="icon-[tabler--forms] size-5"></span>
                            Form Fields
                        </h3>
                        @if(!$ticketForm->isPublished())
                        <a href="{{ route('workspace.ticket-form.fields.create', $workspace) }}" class="btn btn-primary btn-sm">
                            <span class="icon-[tabler--plus] size-4"></span>
                            Add Field
                        </a>
                        @endif
                    </div>

                    @php
                        $orderedFields = $ticketForm->getOrderedFields();
                        $fieldTypes = \App\Modules\Workspace\Models\WorkspaceTicketFormField::getFieldTypes();
                    @endphp

                    <div id="fields-list" class="space-y-2">
                        @foreach($orderedFields as $fieldData)
                            @if($fieldData['type'] === 'standard')
                                @php
                                    $key = $fieldData['key'];
                                    $showKey = 'show_' . $key;
                                    $requiredKey = $key . '_required';
                                    $hasRequired = $key !== 'attachments';
                                @endphp
                                <div class="flex items-center justify-between p-3 bg-base-200 rounded-lg group" data-field-key="{{ $key }}">
                                    <div class="flex items-center gap-3">
                                        @if(!$ticketForm->isPublished())
                                        <span class="icon-[tabler--grip-vertical] size-4 text-base-content/30 cursor-grab drag-handle"></span>
                                        @endif
                                        <div class="w-8 h-8 rounded bg-base-300 flex items-center justify-center">
                                            <span class="icon-[{{ $fieldData['icon'] }}] size-4 text-base-content/70"></span>
                                        </div>
                                        <div class="flex items-center gap-3">
                                            <input type="checkbox" name="{{ $showKey }}" value="1" class="checkbox checkbox-primary checkbox-sm" {{ $ticketForm->$showKey ? 'checked' : '' }}>
                                            <span class="font-medium">{{ $fieldData['label'] }}</span>
                                        </div>
                                    </div>
                                    @if($hasRequired)
                                    <label class="flex items-center gap-2 text-sm">
                                        <input type="checkbox" name="{{ $requiredKey }}" value="1" class="checkbox checkbox-xs" {{ $ticketForm->$requiredKey ? 'checked' : '' }}>
                                        Required
                                    </label>
                                    @endif
                                </div>
                            @else
                                @php $field = $fieldData['field']; @endphp
                                <div class="flex items-center justify-between p-3 bg-base-200 rounded-lg group" data-field-key="custom_{{ $field->id }}">
                                    <div class="flex items-center gap-3">
                                        @if(!$ticketForm->isPublished())
                                        <span class="icon-[tabler--grip-vertical] size-4 text-base-content/30 cursor-grab drag-handle"></span>
                                        @endif
                                        <div class="w-8 h-8 rounded bg-primary/10 flex items-center justify-center">
                                            <span class="icon-[{{ $fieldTypes[$field->type]['icon'] ?? 'tabler--forms' }}] size-4 text-primary"></span>
                                        </div>
                                        <div>
                                            <span class="font-medium">{{ $field->label }}</span>
                                            <span class="text-xs text-base-content/50 ml-2">{{ $fieldTypes[$field->type]['label'] ?? ucfirst($field->type) }}</span>
                                        </div>
                                        @if($field->is_required)
                                        <span class="badge badge-sm badge-primary">Required</span>
                                        @endif
                                    </div>
                                    @if(!$ticketForm->isPublished())
                                    <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition">
                                        <a href="{{ route('workspace.ticket-form.fields.edit', [$workspace, $field]) }}" class="btn btn-ghost btn-sm btn-square">
                                            <span class="icon-[tabler--edit] size-4"></span>
                                        </a>
                                        <button type="button" onclick="deleteField({{ $field->id }})" class="btn btn-ghost btn-sm btn-square text-error">
                                            <span class="icon-[tabler--trash] size-4"></span>
                                        </button>
                                    </div>
                                    @endif
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Default Values -->
            <div class="card bg-base-100 shadow">
                <div class="card-body">
                    <h3 class="font-semibold mb-4 flex items-center gap-2">
                        <span class="icon-[tabler--settings] size-5"></span>
                        Default Values
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium">Default Department</span>
                            </label>
                            <select name="default_department_id" class="select select-bordered">
                                <option value="">None</option>
                                @foreach($workspace->departments as $department)
                                    <option value="{{ $department->id }}" {{ $ticketForm->default_department_id == $department->id ? 'selected' : '' }}>
                                        {{ $department->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium">Default Priority</span>
                            </label>
                            <select name="default_priority_id" class="select select-bordered">
                                <option value="">None</option>
                                @foreach($workspace->priorities as $priority)
                                    <option value="{{ $priority->id }}" {{ $ticketForm->default_priority_id == $priority->id ? 'selected' : '' }}>
                                        {{ $priority->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Branding -->
            <div class="card bg-base-100 shadow">
                <div class="card-body">
                    <h3 class="font-semibold mb-4 flex items-center gap-2">
                        <span class="icon-[tabler--palette] size-5"></span>
                        Branding
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium">Logo URL</span>
                            </label>
                            <input type="url" name="logo_url" value="{{ $ticketForm->logo_url }}" class="input input-bordered" placeholder="https://...">
                        </div>

                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium">Primary Color</span>
                            </label>
                            <input type="color" name="primary_color" value="{{ $ticketForm->primary_color ?? '#6366f1' }}" class="input input-bordered h-10 p-1">
                        </div>

                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium">Background Color</span>
                            </label>
                            <input type="color" name="background_color" value="{{ $ticketForm->background_color ?? '#f3f4f6' }}" class="input input-bordered h-10 p-1">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Confirmation Screen -->
            <div class="card bg-base-100 shadow">
                <div class="card-body">
                    <h3 class="font-semibold mb-4 flex items-center gap-2">
                        <span class="icon-[tabler--circle-check] size-5"></span>
                        Confirmation Screen
                    </h3>

                    <div class="space-y-4">
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium">Confirmation Type</span>
                            </label>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                <label class="cursor-pointer">
                                    <input type="radio" name="confirmation_type" value="inline" class="peer hidden" {{ ($ticketForm->confirmation_type ?? 'inline') === 'inline' ? 'checked' : '' }}>
                                    <div class="p-4 border-2 border-base-300 rounded-lg peer-checked:border-primary peer-checked:bg-primary/5 hover:border-base-content/30 transition text-center">
                                        <span class="icon-[tabler--replace] size-6 mb-2"></span>
                                        <div class="font-medium text-sm">Inline</div>
                                        <div class="text-xs text-base-content/50">Replace form with message</div>
                                    </div>
                                </label>
                                <label class="cursor-pointer">
                                    <input type="radio" name="confirmation_type" value="modal" class="peer hidden" {{ ($ticketForm->confirmation_type ?? '') === 'modal' ? 'checked' : '' }}>
                                    <div class="p-4 border-2 border-base-300 rounded-lg peer-checked:border-primary peer-checked:bg-primary/5 hover:border-base-content/30 transition text-center">
                                        <span class="icon-[tabler--app-window] size-6 mb-2"></span>
                                        <div class="font-medium text-sm">Modal Window</div>
                                        <div class="text-xs text-base-content/50">Show popup confirmation</div>
                                    </div>
                                </label>
                                <label class="cursor-pointer">
                                    <input type="radio" name="confirmation_type" value="redirect" class="peer hidden" {{ ($ticketForm->confirmation_type ?? '') === 'redirect' ? 'checked' : '' }}>
                                    <div class="p-4 border-2 border-base-300 rounded-lg peer-checked:border-primary peer-checked:bg-primary/5 hover:border-base-content/30 transition text-center">
                                        <span class="icon-[tabler--external-link] size-6 mb-2"></span>
                                        <div class="font-medium text-sm">Redirect</div>
                                        <div class="text-xs text-base-content/50">Go to thank you page</div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div id="confirmation-content" class="space-y-4">
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-medium">Headline</span>
                                </label>
                                <input type="text" name="confirmation_headline" value="{{ $ticketForm->confirmation_headline ?? 'Thank You!' }}" class="input input-bordered" placeholder="Thank You!">
                            </div>

                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-medium">Message</span>
                                </label>
                                <textarea name="confirmation_message" class="textarea textarea-bordered" rows="3" placeholder="Your ticket has been submitted successfully. We'll get back to you soon.">{{ $ticketForm->confirmation_message ?? 'Your ticket has been submitted successfully. We\'ll get back to you soon.' }}</textarea>
                            </div>
                        </div>

                        <div id="redirect-url-field" class="form-control" style="display: none;">
                            <label class="label">
                                <span class="label-text font-medium">Redirect URL</span>
                            </label>
                            <input type="url" name="redirect_url" value="{{ $ticketForm->redirect_url }}" class="input input-bordered" placeholder="https://yoursite.com/thank-you">
                            <label class="label">
                                <span class="label-text-alt text-base-content/50">Users will be redirected to this URL after submitting</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Spam Protection -->
            <div class="card bg-base-100 shadow">
                <div class="card-body">
                    <h3 class="font-semibold mb-4 flex items-center gap-2">
                        <span class="icon-[tabler--shield] size-5"></span>
                        Spam Protection
                    </h3>

                    <div class="space-y-4">
                        <label class="flex items-center gap-3">
                            <input type="checkbox" name="enable_honeypot" value="1" class="checkbox checkbox-primary checkbox-sm" {{ $ticketForm->enable_honeypot ? 'checked' : '' }}>
                            <div>
                                <span class="font-medium">Honeypot Field</span>
                                <p class="text-xs text-base-content/50">Hidden field that catches bots</p>
                            </div>
                        </label>

                        <label class="flex items-center gap-3">
                            <input type="checkbox" name="enable_rate_limiting" value="1" class="checkbox checkbox-primary checkbox-sm" {{ $ticketForm->enable_rate_limiting ?? true ? 'checked' : '' }}>
                            <div>
                                <span class="font-medium">Rate Limiting</span>
                                <p class="text-xs text-base-content/50">Limit submissions per IP address</p>
                            </div>
                        </label>

                        <div class="form-control ml-8" id="rate-limit-field">
                            <label class="label">
                                <span class="label-text">Max submissions per hour</span>
                            </label>
                            <input type="number" name="rate_limit_per_hour" value="{{ $ticketForm->rate_limit_per_hour ?? 10 }}" class="input input-bordered input-sm w-32" min="1" max="100">
                        </div>

                        <label class="flex items-center gap-3">
                            <input type="checkbox" name="block_disposable_emails" value="1" class="checkbox checkbox-primary checkbox-sm" {{ $ticketForm->block_disposable_emails ? 'checked' : '' }}>
                            <div>
                                <span class="font-medium">Block Disposable Emails</span>
                                <p class="text-xs text-base-content/50">Reject temporary/disposable email addresses</p>
                            </div>
                        </label>

                        <div class="divider text-xs text-base-content/50">Block Lists</div>

                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium">Blocked Email Addresses</span>
                            </label>
                            <textarea name="blocked_emails" class="textarea textarea-bordered textarea-sm" rows="2" placeholder="spam@example.com&#10;another@spam.com">{{ $ticketForm->blocked_emails }}</textarea>
                            <label class="label">
                                <span class="label-text-alt text-base-content/50">One email per line</span>
                            </label>
                        </div>

                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium">Blocked Domains</span>
                            </label>
                            <textarea name="blocked_domains" class="textarea textarea-bordered textarea-sm" rows="2" placeholder="spamdomain.com&#10;tempmail.org">{{ $ticketForm->blocked_domains }}</textarea>
                            <label class="label">
                                <span class="label-text-alt text-base-content/50">One domain per line (blocks all emails from these domains)</span>
                            </label>
                        </div>

                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium">Blocked IP Addresses</span>
                            </label>
                            <textarea name="blocked_ips" class="textarea textarea-bordered textarea-sm" rows="2" placeholder="192.168.1.1&#10;10.0.0.1">{{ $ticketForm->blocked_ips }}</textarea>
                            <label class="label">
                                <span class="label-text-alt text-base-content/50">One IP per line</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Embed Code -->
            <div class="card bg-base-100 shadow">
                <div class="card-body">
                    <h3 class="font-semibold mb-4 flex items-center gap-2">
                        <span class="icon-[tabler--code] size-5"></span>
                        Embed Code
                    </h3>

                    <p class="text-sm text-base-content/60 mb-4">Embed this form on your website using one of the options below.</p>

                    <!-- Tabs -->
                    <div class="tabs tabs-boxed mb-4">
                        <button type="button" class="tab tab-active" data-embed-tab="direct">Direct Link</button>
                        <button type="button" class="tab" data-embed-tab="iframe">iFrame</button>
                        <button type="button" class="tab" data-embed-tab="popup">Popup Button</button>
                    </div>

                    @php
                        $displayDomain = 'yourdomain.com';
                        $displayUrl = $displayDomain . '/form/' . $ticketForm->slug;
                    @endphp

                    <!-- Direct Link -->
                    <div id="embed-direct" class="embed-content">
                        <label class="label">
                            <span class="label-text font-medium">Direct URL</span>
                        </label>
                        <div class="flex items-center gap-2">
                            <input type="text" value="{{ $displayUrl }}" class="input input-bordered flex-1 font-mono text-sm" readonly id="embed-url">
                            <button type="button" onclick="copyEmbed('embed-url')" class="btn btn-ghost btn-square">
                                <span class="icon-[tabler--copy] size-5"></span>
                            </button>
                            <a href="{{ $ticketForm->public_url }}" target="_blank" class="btn btn-ghost btn-square">
                                <span class="icon-[tabler--external-link] size-5"></span>
                            </a>
                        </div>
                        <p class="text-xs text-base-content/50 mt-2">Replace "yourdomain.com" with your actual domain. Share this link directly with your customers.</p>
                    </div>

                    <!-- iFrame -->
                    <div id="embed-iframe" class="embed-content hidden">
                        <label class="label">
                            <span class="label-text font-medium">iFrame Code</span>
                        </label>
                        <div class="relative">
                            <textarea id="embed-iframe-code" class="textarea textarea-bordered w-full font-mono text-xs" rows="4" readonly>&lt;iframe src="https://{{ $displayUrl }}" width="100%" height="600" frameborder="0" style="border: none; border-radius: 8px;"&gt;&lt;/iframe&gt;</textarea>
                            <button type="button" onclick="copyEmbed('embed-iframe-code')" class="btn btn-sm btn-ghost absolute top-2 right-2">
                                <span class="icon-[tabler--copy] size-4"></span>
                                Copy
                            </button>
                        </div>
                        <p class="text-xs text-base-content/50 mt-2">Replace "yourdomain.com" with your actual domain. Adjust width and height as needed.</p>
                    </div>

                    <!-- Popup Button -->
                    <div id="embed-popup" class="embed-content hidden">
                        <label class="label">
                            <span class="label-text font-medium">Popup Button Code</span>
                        </label>
                        <div class="relative">
                            <textarea id="embed-popup-code" class="textarea textarea-bordered w-full font-mono text-xs" rows="6" readonly>&lt;!-- Ticket Form Popup --&gt;
&lt;script&gt;
function openTicketForm() {
    window.open('https://{{ $displayUrl }}', 'ticketForm', 'width=600,height=700,scrollbars=yes');
}
&lt;/script&gt;
&lt;button onclick="openTicketForm()" style="background: {{ $ticketForm->primary_color ?? '#6366f1' }}; color: white; padding: 12px 24px; border: none; border-radius: 8px; cursor: pointer; font-weight: 500;"&gt;Contact Support&lt;/button&gt;</textarea>
                            <button type="button" onclick="copyEmbed('embed-popup-code')" class="btn btn-sm btn-ghost absolute top-2 right-2">
                                <span class="icon-[tabler--copy] size-4"></span>
                                Copy
                            </button>
                        </div>
                        <p class="text-xs text-base-content/50 mt-2">Replace "yourdomain.com" with your actual domain. Opens the form in a popup window when clicked.</p>
                    </div>
                </div>
            </div>

            </fieldset>

            <!-- Submit -->
            @if(!$ticketForm->isPublished())
            <div class="flex justify-end">
                <button type="submit" class="btn btn-primary">
                    <span class="icon-[tabler--device-floppy] size-5"></span>
                    Save Settings
                </button>
            </div>
            @endif
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
// Copy to clipboard helper
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        alert('Copied to clipboard!');
    });
}

// Copy embed code
function copyEmbed(elementId) {
    const element = document.getElementById(elementId);
    const text = element.value || element.textContent;
    navigator.clipboard.writeText(text).then(() => {
        alert('Copied to clipboard!');
    });
}

// Initialize sortable for all fields (only if not published)
const fieldsList = document.getElementById('fields-list');
const isPublished = {{ $ticketForm->isPublished() ? 'true' : 'false' }};
if (fieldsList && !isPublished) {
    new Sortable(fieldsList, {
        animation: 150,
        handle: '.drag-handle',
        onEnd: function(evt) {
            const fieldKeys = Array.from(fieldsList.querySelectorAll('[data-field-key]'))
                .map(el => el.dataset.fieldKey);

            fetch('{{ route("workspace.ticket-form.fields.reorder", $workspace) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ field_order: fieldKeys })
            });
        }
    });
}

function deleteField(fieldId) {
    if (!confirm('Are you sure you want to delete this field?')) return;

    fetch(`{{ url('workspace/' . $workspace->uuid . '/ticket-form/fields') }}/${fieldId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
        }
    }).then(response => {
        if (response.ok) {
            location.reload();
        } else {
            alert('Failed to delete field');
        }
    });
}

// Confirmation type toggle
document.querySelectorAll('input[name="confirmation_type"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const confirmationContent = document.getElementById('confirmation-content');
        const redirectField = document.getElementById('redirect-url-field');

        if (this.value === 'redirect') {
            confirmationContent.style.display = 'none';
            redirectField.style.display = 'block';
        } else {
            confirmationContent.style.display = 'block';
            redirectField.style.display = 'none';
        }
    });
});

// Initial confirmation type state
const checkedConfirmationType = document.querySelector('input[name="confirmation_type"]:checked');
if (checkedConfirmationType && checkedConfirmationType.value === 'redirect') {
    document.getElementById('confirmation-content').style.display = 'none';
    document.getElementById('redirect-url-field').style.display = 'block';
}

// Embed tabs
document.querySelectorAll('[data-embed-tab]').forEach(tab => {
    tab.addEventListener('click', function() {
        // Update active tab
        document.querySelectorAll('[data-embed-tab]').forEach(t => t.classList.remove('tab-active'));
        this.classList.add('tab-active');

        // Show corresponding content
        const tabName = this.dataset.embedTab;
        document.querySelectorAll('.embed-content').forEach(content => {
            content.classList.add('hidden');
        });
        document.getElementById('embed-' + tabName).classList.remove('hidden');
    });
});

// Rate limiting field toggle
const rateLimitCheckbox = document.querySelector('input[name="enable_rate_limiting"]');
const rateLimitField = document.getElementById('rate-limit-field');
if (rateLimitCheckbox && rateLimitField) {
    rateLimitCheckbox.addEventListener('change', function() {
        rateLimitField.style.display = this.checked ? 'block' : 'none';
    });
    // Initial state
    rateLimitField.style.display = rateLimitCheckbox.checked ? 'block' : 'none';
}
</script>
@endsection
