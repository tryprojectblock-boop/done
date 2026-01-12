@extends('layouts.app')

@section('content')
<div class="p-4 md:p-6">
    <div class="max-w mx-auto">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex items-center gap-2 text-sm text-base-content/60 mb-2">
                <a href="{{ route('dashboard') }}" class="hover:text-primary">Dashboard</a>
                <span class="icon-[tabler--chevron-right] size-4"></span>
                <a href="{{ route('workspace.show', $workspace) }}" class="hover:text-primary">{{ $workspace->name }}</a>
                <span class="icon-[tabler--chevron-right] size-4"></span>
                <span>Email Templates</span>
            </div>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <a href="{{ route('workspace.show', $workspace) }}" class="btn btn-ghost btn-sm btn-square">
                        <span class="icon-[tabler--arrow-left] size-5"></span>
                    </a>
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-lg bg-info/10 flex items-center justify-center">
                            <span class="icon-[tabler--mail] size-6 text-info"></span>
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold text-base-content">Email Templates</h1>
                            <p class="text-sm text-base-content/60">Customize automated email notifications</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Success/Error Messages -->
        @if(session('success'))
            <div class="alert alert-success mb-4">
                <span class="icon-[tabler--check] size-5"></span>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-error mb-4">
                <span class="icon-[tabler--x] size-5"></span>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <!-- Category Tabs -->
        <div class="inline-flex p-1 bg-base-200 rounded-xl mb-6">
            @foreach($templateCategories as $categoryKey => $category)
                <button type="button"
                        class="flex items-center gap-2 px-5 py-2.5 rounded-lg font-medium transition-all duration-200 {{ $loop->first ? 'bg-primary text-primary-content shadow-sm' : 'text-base-content/60 hover:text-primary hover:bg-primary/10' }}"
                        onclick="showCategory('{{ $categoryKey }}')"
                        id="tab-{{ $categoryKey }}">
                    <span class="icon-[{{ $category['icon'] }}] size-5"></span>
                    <span>{{ $category['name'] }}</span>
                    <span class="badge badge-sm {{ $loop->first ? 'bg-primary-content/20 text-primary-content border-0' : 'badge-ghost' }}">{{ count($typesByCategory[$categoryKey] ?? []) }}</span>
                </button>
            @endforeach
        </div>

        <!-- Category Content -->
        @foreach($templateCategories as $categoryKey => $category)
            <div id="category-{{ $categoryKey }}" class="category-content {{ !$loop->first ? 'hidden' : '' }}">
                <!-- Templates Table -->
                <div class="card bg-base-100 shadow-sm border border-base-200">
                    <div class="overflow-x-auto">
                        <table class="table">
                            <thead>
                                <tr class="bg-base-200/50">
                                    <th class="w-12"></th>
                                    <th>Template</th>
                                    <th>Subject</th>
                                    <th class="w-24 text-center">Status</th>
                                    <th class="w-32 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($typesByCategory[$categoryKey] ?? [] as $type => $typeInfo)
                                    @php
                                        $template = $templates[$type]->first() ?? null;
                                    @endphp
                                    <tr class="hover:bg-base-200/30">
                                        <td>
                                            <div class="w-9 h-9 rounded-lg bg-{{ $typeInfo['color'] }}/10 flex items-center justify-center">
                                                <span class="icon-[{{ $typeInfo['icon'] }}] size-4 text-{{ $typeInfo['color'] }}"></span>
                                            </div>
                                        </td>
                                        <td>
                                            <div>
                                                <div class="font-medium">{{ $typeInfo['name'] }}</div>
                                                <div class="text-xs text-base-content/50">{{ $typeInfo['description'] }}</div>
                                            </div>
                                        </td>
                                        <td>
                                            @if($template)
                                                <code class="text-xs bg-base-200 px-2 py-1 rounded font-mono">{{ Str::limit($template->subject, 40) }}</code>
                                            @else
                                                <span class="text-base-content/40 text-sm">Not configured</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($template)
                                                <label class="swap swap-flip">
                                                    <input type="checkbox" {{ $template->is_active ? 'checked' : '' }} onchange="toggleTemplate({{ $template->id }}, this)">
                                                    <span class="swap-on badge badge-success badge-sm gap-1">
                                                        <span class="icon-[tabler--check] size-3"></span> Active
                                                    </span>
                                                    <span class="swap-off badge badge-ghost badge-sm gap-1">
                                                        <span class="icon-[tabler--x] size-3"></span> Off
                                                    </span>
                                                </label>
                                            @else
                                                <span class="badge badge-ghost badge-sm">—</span>
                                            @endif
                                        </td>
                                        <td class="text-right">
                                            @if($template)
                                                <div class="flex justify-end gap-1">
                                                    <button class="btn btn-ghost btn-xs btn-square" onclick="resetTemplate({{ $template->id }}, '{{ $typeInfo['name'] }}')" title="Reset to default">
                                                        <span class="icon-[tabler--refresh] size-4"></span>
                                                    </button>
                                                    <button class="btn btn-primary btn-xs gap-1" onclick="editTemplate({{ $template->id }})">
                                                        <span class="icon-[tabler--edit] size-3"></span>
                                                        Edit
                                                    </button>
                                                </div>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endforeach

        <!-- Placeholders Reference (Collapsible) -->
        <div class="mt-8">
            <div class="collapse collapse-arrow bg-base-200 rounded-lg">
                <input type="checkbox">
                <div class="collapse-title font-medium flex items-center gap-2">
                    <span class="icon-[tabler--code] size-5"></span>
                    Available Placeholders
                </div>
                <div class="collapse-content">
                    <p class="text-sm text-base-content/60 mb-3">Use these placeholders in your templates. They will be replaced with actual values when emails are sent.</p>
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2">
                        @foreach($placeholders as $placeholder => $description)
                            <div class="flex items-center gap-2 p-2 bg-base-100 rounded">
                                <code class="text-xs bg-primary/10 text-primary px-1.5 py-0.5 rounded font-mono">{{ $placeholder }}</code>
                                <span class="text-base-content/60 text-xs truncate" title="{{ $description }}">{{ $description }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Template Modal -->
<div id="editTemplateModal" class="hidden fixed inset-0 z-50 flex items-start justify-center pt-20 pb-4 px-4">
    <div class="fixed inset-0 bg-black/50 transition-opacity" onclick="closeEditModal()"></div>
    <div class="relative bg-base-100 rounded-xl shadow-xl w-full max-w-3xl max-h-[calc(100vh-6rem)] overflow-y-auto">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold">Edit Email Template</h3>
                <button type="button" class="btn btn-ghost btn-sm btn-square" onclick="closeEditModal()">
                    <span class="icon-[tabler--x] size-5"></span>
                </button>
            </div>

            <form id="editTemplateForm" onsubmit="saveTemplate(event)">
                <input type="hidden" name="template_id" id="edit-template-id">
                <input type="hidden" name="action" value="edit">

                <div class="space-y-4">
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Template Name</span>
                        </label>
                        <input type="text" name="name" id="edit-template-name" class="input input-bordered" required>
                    </div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Email Subject</span>
                        </label>
                        <input type="text" name="subject" id="edit-template-subject" class="input input-bordered font-mono text-sm" required>
                        <label class="label">
                            <span class="label-text-alt text-base-content/50">You can use placeholders like @{{ticket_id}}, @{{customer_name}}, etc.</span>
                        </label>
                    </div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Email Body</span>
                        </label>
                        <textarea name="body" id="edit-template-body" class="textarea textarea-bordered h-40 font-mono text-sm" required></textarea>
                        <label class="label">
                            <span class="label-text-alt text-base-content/50">Supports Markdown formatting and placeholders</span>
                        </label>
                    </div>

                    <div class="form-control">
                        <label class="cursor-pointer flex items-center gap-3">
                            <input type="checkbox" name="is_active" id="edit-template-active" class="checkbox checkbox-primary" checked>
                            <span class="label-text">Enable this template</span>
                        </label>
                    </div>

                    <!-- Placeholders Reference -->
                    <div class="bg-base-200 rounded-lg p-4">
                        <div class="font-medium text-sm mb-3">Available Placeholders</div>
                        <div class="grid grid-cols-2 gap-2 text-sm">
                            @foreach($placeholders as $placeholder => $description)
                                <div class="flex items-center gap-2">
                                    <code class="text-xs bg-primary/10 text-primary px-1.5 py-0.5 rounded font-mono">{{ $placeholder }}</code>
                                    <span class="text-base-content/60 text-xs">{{ $description }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div id="edit-form-error" class="alert alert-error mt-4 hidden">
                    <span class="icon-[tabler--alert-circle] size-5"></span>
                    <span id="edit-form-error-text"></span>
                </div>

                <div class="flex justify-end gap-2 mt-6">
                    <button type="button" class="btn btn-ghost" onclick="closeEditModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary gap-2">
                        <span class="icon-[tabler--device-floppy] size-5"></span>
                        Save Template
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reset Confirmation Modal -->
<div id="resetTemplateModal" class="hidden fixed inset-0 z-50 flex items-start justify-center pt-20 pb-4 px-4">
    <div class="fixed inset-0 bg-black/50 transition-opacity" onclick="closeResetModal()"></div>
    <div class="relative bg-base-100 rounded-xl shadow-xl w-full max-w-md p-6">
        <h3 class="text-lg font-bold mb-4">Reset Template</h3>
        <p class="text-base-content/70">Are you sure you want to reset the <strong id="reset-template-name"></strong> template to its default content?</p>
        <p class="text-sm text-base-content/50 mt-2">This will overwrite your current customizations.</p>
        <div class="flex justify-end gap-2 mt-6">
            <button class="btn btn-ghost" onclick="closeResetModal()">Cancel</button>
            <button class="btn btn-warning gap-2" onclick="confirmResetTemplate()">
                <span class="icon-[tabler--refresh] size-5"></span>
                Reset to Default
            </button>
        </div>
    </div>
</div>

<script>
const templatesEndpoint = '{{ route('workspace.save-email-template', $workspace) }}';
const csrfToken = '{{ csrf_token() }}';
let resetTemplateId = null;

// Template data for editing
const templateData = @json($templates->flatten()->keyBy('id'));

// Tab switching
function showCategory(category) {
    // Hide all category content
    document.querySelectorAll('.category-content').forEach(el => el.classList.add('hidden'));

    // Reset all tabs to inactive state
    document.querySelectorAll('[id^="tab-"]').forEach(el => {
        el.classList.remove('bg-primary', 'text-primary-content', 'shadow-sm');
        el.classList.add('text-base-content/60', 'hover:text-primary', 'hover:bg-primary/10');
        // Update badge styling
        const badge = el.querySelector('.badge');
        if (badge) {
            badge.classList.remove('bg-primary-content/20', 'text-primary-content', 'border-0');
            badge.classList.add('badge-ghost');
        }
    });

    // Show selected category and activate tab
    document.getElementById('category-' + category).classList.remove('hidden');
    const activeTab = document.getElementById('tab-' + category);
    activeTab.classList.add('bg-primary', 'text-primary-content', 'shadow-sm');
    activeTab.classList.remove('text-base-content/60', 'hover:text-primary', 'hover:bg-primary/10');
    // Update badge styling for active tab
    const activeBadge = activeTab.querySelector('.badge');
    if (activeBadge) {
        activeBadge.classList.add('bg-primary-content/20', 'text-primary-content', 'border-0');
        activeBadge.classList.remove('badge-ghost');
    }
}

function editTemplate(templateId) {
    const template = templateData[String(templateId)];
    if (!template) {
        console.error('Template not found:', templateId, templateData);
        return;
    }

    document.getElementById('edit-template-id').value = templateId;
    document.getElementById('edit-template-name').value = template.name;
    document.getElementById('edit-template-subject').value = template.subject;
    document.getElementById('edit-template-body').value = template.body;
    document.getElementById('edit-template-active').checked = template.is_active;
    document.getElementById('edit-form-error').classList.add('hidden');

    document.getElementById('editTemplateModal').classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
}

function closeEditModal() {
    document.getElementById('editTemplateModal').classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
}

async function saveTemplate(event) {
    event.preventDefault();

    const form = document.getElementById('editTemplateForm');
    const formData = new FormData(form);
    formData.append('_token', csrfToken);
    formData.append('is_active', document.getElementById('edit-template-active').checked ? '1' : '0');

    try {
        const response = await fetch(templatesEndpoint, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            }
        });

        const data = await response.json();

        if (data.success) {
            showToast(data.message || 'Template saved successfully.', 'success');
            setTimeout(() => window.location.reload(), 500);
        } else {
            document.getElementById('edit-form-error-text').textContent = data.message || 'An error occurred.';
            document.getElementById('edit-form-error').classList.remove('hidden');
        }
    } catch (error) {
        document.getElementById('edit-form-error-text').textContent = 'An error occurred. Please try again.';
        document.getElementById('edit-form-error').classList.remove('hidden');
    }
}

async function toggleTemplate(templateId, checkbox) {
    const formData = new FormData();
    formData.append('_token', csrfToken);
    formData.append('action', 'toggle');
    formData.append('template_id', templateId);

    try {
        const response = await fetch(templatesEndpoint, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            }
        });

        const data = await response.json();

        if (data.success) {
            showToast(data.message, 'success');
        } else {
            checkbox.checked = !checkbox.checked;
            showToast(data.message || 'Failed to update template.', 'error');
        }
    } catch (error) {
        checkbox.checked = !checkbox.checked;
        showToast('An error occurred.', 'error');
    }
}

function resetTemplate(templateId, templateName) {
    resetTemplateId = templateId;
    document.getElementById('reset-template-name').textContent = templateName;
    document.getElementById('resetTemplateModal').classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
}

function closeResetModal() {
    document.getElementById('resetTemplateModal').classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
    resetTemplateId = null;
}

async function confirmResetTemplate() {
    if (!resetTemplateId) return;

    const formData = new FormData();
    formData.append('_token', csrfToken);
    formData.append('action', 'reset');
    formData.append('template_id', resetTemplateId);

    try {
        const response = await fetch(templatesEndpoint, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            }
        });

        const data = await response.json();

        if (data.success) {
            showToast(data.message || 'Template reset successfully.', 'success');
            setTimeout(() => window.location.reload(), 500);
        } else {
            showToast(data.message || 'Failed to reset template.', 'error');
        }
    } catch (error) {
        showToast('An error occurred.', 'error');
    }

    closeResetModal();
}

function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = 'toast toast-top toast-end z-50';
    toast.innerHTML = `
        <div class="alert alert-${type}">
            <span class="icon-[tabler--${type === 'success' ? 'check' : 'alert-circle'}] size-5"></span>
            <span>${message}</span>
        </div>
    `;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}
</script>
@endsection
