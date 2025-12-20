@extends('admin::layouts.app')

@section('title', 'Edit Funnel')
@section('page-title', 'Edit Funnel')

@section('content')
<div class="space-y-6">
    <!-- Tabs -->
    @include('admin::funnel.partials.tabs')

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-4">
            <a href="{{ route('backoffice.funnel.index') }}" class="btn btn-ghost btn-sm">
                <span class="icon-[tabler--arrow-left] size-5"></span>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-base-content">{{ $funnel->name }}</h1>
                <p class="text-base-content/60">
                    Trigger: <span class="text-primary">{{ $funnel->triggerTag?->display_name }}</span>
                </p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <form action="{{ route('backoffice.funnel.toggle', $funnel) }}" method="POST">
                @csrf
                <button type="submit" class="btn {{ $funnel->is_active ? 'btn-success' : 'btn-ghost' }} btn-sm">
                    <span class="icon-[tabler--{{ $funnel->is_active ? 'player-pause' : 'player-play' }}] size-4"></span>
                    {{ $funnel->is_active ? 'Active' : 'Inactive' }}
                </button>
            </form>
        </div>
    </div>

    @include('admin::partials.alerts')

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="card bg-base-100 shadow">
            <div class="card-body p-4 text-center">
                <div class="text-3xl font-bold text-primary">{{ $stats['total_subscribers'] }}</div>
                <div class="text-sm text-base-content/60">Total Subscribers</div>
            </div>
        </div>
        <div class="card bg-base-100 shadow">
            <div class="card-body p-4 text-center">
                <div class="text-3xl font-bold text-info">{{ $stats['emails_sent'] }}</div>
                <div class="text-sm text-base-content/60">Emails Sent</div>
            </div>
        </div>
        <div class="card bg-base-100 shadow">
            <div class="card-body p-4 text-center">
                <div class="text-3xl font-bold text-success">{{ $stats['open_rate'] }}%</div>
                <div class="text-sm text-base-content/60">Open Rate</div>
            </div>
        </div>
        <div class="card bg-base-100 shadow">
            <div class="card-body p-4 text-center">
                <div class="text-3xl font-bold text-secondary">{{ $stats['click_rate'] }}%</div>
                <div class="text-sm text-base-content/60">Click Rate</div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Funnel Settings -->
        <div class="lg:col-span-1">
            <div class="card bg-base-100 shadow">
                <div class="card-body">
                    <h3 class="card-title text-lg">Funnel Settings</h3>

                    <form action="{{ route('backoffice.funnel.update', $funnel) }}" method="POST" class="space-y-4">
                        @csrf
                        @method('PUT')

                        <div class="form-control">
                            <label class="label"><span class="label-text font-medium">Name</span></label>
                            <input type="text" name="name" value="{{ old('name', $funnel->name) }}"
                                   class="input input-bordered input-sm" required />
                        </div>

                        <div class="form-control">
                            <label class="label"><span class="label-text font-medium">Description</span></label>
                            <textarea name="description" rows="2" class="textarea textarea-bordered textarea-sm">{{ old('description', $funnel->description) }}</textarea>
                        </div>

                        <div class="form-control">
                            <label class="label"><span class="label-text font-medium">Trigger Tag</span></label>
                            <select name="trigger_tag_id" class="select select-bordered select-sm" required>
                                @foreach($tags as $tag)
                                    <option value="{{ $tag->id }}" {{ $funnel->trigger_tag_id == $tag->id ? 'selected' : '' }}>
                                        {{ $tag->display_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <input type="hidden" name="is_active" value="{{ $funnel->is_active ? '1' : '0' }}" />

                        <button type="submit" class="btn btn-primary btn-sm w-full">Save Settings</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Email Steps -->
        <div class="lg:col-span-2">
            <div class="card bg-base-100 shadow">
                <div class="card-body">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="card-title text-lg">Email Steps</h3>
                        <button type="button" class="btn btn-primary btn-sm" onclick="openAddStepModal()">
                            <span class="icon-[tabler--plus] size-4"></span>
                            Add Step
                        </button>
                    </div>

                    @if($funnel->steps->isEmpty())
                        <div class="text-center py-8">
                            <span class="icon-[tabler--mail-off] size-12 text-base-content/20"></span>
                            <p class="text-base-content/60 mt-2">No steps yet. Add your first email step.</p>
                        </div>
                    @else
                        <div class="space-y-4">
                            @foreach($funnel->steps as $step)
                                <div class="border border-base-200 rounded-lg p-4 hover:bg-base-50 transition-colors">
                                    <div class="flex items-start justify-between gap-4">
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <span class="badge badge-primary badge-sm">Step {{ $step->step_order }}</span>
                                                <span class="badge badge-ghost badge-sm">
                                                    <span class="icon-[tabler--clock] size-3 mr-1"></span>
                                                    {{ $step->delay_display }}
                                                </span>
                                                @if($step->hasCondition())
                                                    <span class="badge badge-warning badge-sm">
                                                        <span class="icon-[tabler--filter] size-3 mr-1"></span>
                                                        {{ $step->condition_display }}
                                                    </span>
                                                @endif
                                                @if(!$step->is_active)
                                                    <span class="badge badge-ghost badge-sm">Paused</span>
                                                @endif
                                            </div>
                                            <h4 class="font-semibold mt-2">{{ $step->name }}</h4>
                                            <p class="text-sm text-base-content/70 mt-1">
                                                Subject: {{ $step->subject }}
                                            </p>
                                            <div class="flex items-center gap-4 mt-2 text-xs text-base-content/50">
                                                <span>Sent: {{ $step->stats['sent'] ?? 0 }}</span>
                                                <span>Opened: {{ $step->stats['opened'] ?? 0 }} ({{ $step->stats['open_rate'] ?? 0 }}%)</span>
                                                <span>Clicked: {{ $step->stats['clicked'] ?? 0 }} ({{ $step->stats['click_rate'] ?? 0 }}%)</span>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-1">
                                            <button type="button" class="btn btn-ghost btn-xs"
                                                    onclick="editStep({{ json_encode($step) }})">
                                                <span class="icon-[tabler--edit] size-4"></span>
                                            </button>
                                            <form action="{{ route('backoffice.funnel.steps.destroy', [$funnel, $step]) }}" method="POST" class="inline"
                                                  onsubmit="return confirm('Delete this step?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-ghost btn-xs text-error">
                                                    <span class="icon-[tabler--trash] size-4"></span>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Step Modal -->
<div id="add-step-modal" class="custom-modal">
    <div class="custom-modal-box max-w-3xl bg-base-100">
        <!-- Header -->
        <div class="flex items-center justify-between mb-5">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-full bg-primary/20 flex items-center justify-center">
                    <span class="icon-[tabler--mail-plus] size-6 text-primary"></span>
                </div>
                <div>
                    <h3 class="font-bold text-lg">Add Email Step</h3>
                    <p class="text-xs text-base-content/50">Create a new email in the funnel sequence</p>
                </div>
            </div>
            <button type="button" onclick="closeAddStepModal()" class="btn btn-ghost btn-sm btn-circle hover:bg-error/10 hover:text-error transition-colors">
                <span class="icon-[tabler--x] size-5"></span>
            </button>
        </div>

        <form action="{{ route('backoffice.funnel.steps.store', $funnel) }}" method="POST" id="add-step-form" class="space-y-4">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-control">
                    <label class="label"><span class="label-text font-medium">Step Name</span></label>
                    <input type="text" name="name" class="input input-bordered" placeholder="e.g., Day 0 - Welcome Email" required />
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Delay Days</span></label>
                        <input type="number" name="delay_days" value="0" min="0" class="input input-bordered" required />
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Hours</span></label>
                        <input type="number" name="delay_hours" value="0" min="0" max="23" class="input input-bordered" required />
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-control">
                    <label class="label"><span class="label-text font-medium">Condition</span></label>
                    <select name="condition_type" id="add-condition-type" class="select select-bordered" onchange="toggleConditionTag('add')">
                        <option value="none">No Condition</option>
                        <option value="has_tag">If user has tag</option>
                        <option value="missing_tag">If user missing tag</option>
                    </select>
                </div>
                <div class="form-control" id="add-condition-tag-wrapper" style="display: none;">
                    <label class="label"><span class="label-text font-medium">Condition Tag</span></label>
                    <select name="condition_tag_id" class="select select-bordered">
                        <option value="">Select tag...</option>
                        @foreach($tags as $tag)
                            <option value="{{ $tag->id }}">{{ $tag->display_name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-control">
                    <label class="label"><span class="label-text font-medium">From Email</span></label>
                    <input type="email" name="from_email" class="input input-bordered" value="{{ config('mail.from.address') }}" required />
                </div>
                <div class="form-control">
                    <label class="label"><span class="label-text font-medium">From Name</span></label>
                    <input type="text" name="from_name" class="input input-bordered" value="{{ config('mail.from.name') }}" required />
                </div>
            </div>

            <div class="form-control">
                <label class="label"><span class="label-text font-medium">Subject</span></label>
                <input type="text" name="subject" class="input input-bordered" placeholder="Welcome to our platform, @{{first_name}}!" required />
                <label class="label">
                    <span class="label-text-alt text-base-content/60">Use @{{first_name}}, @{{last_name}}, @{{name}}, @{{email}} as placeholders</span>
                </label>
            </div>

            <div class="form-control">
                <label class="label"><span class="label-text font-medium">Email Body (HTML)</span></label>
                <textarea name="body_html" rows="8" class="textarea textarea-bordered font-mono text-sm" required></textarea>
            </div>

            <div class="form-control">
                <label class="label"><span class="label-text font-medium">Plain Text Version (optional)</span></label>
                <textarea name="body_text" rows="3" class="textarea textarea-bordered font-mono text-sm"></textarea>
            </div>

            <div class="form-control">
                <label class="label cursor-pointer justify-start gap-4">
                    <input type="checkbox" name="is_active" value="1" class="toggle toggle-primary" checked />
                    <span class="label-text">Step Active</span>
                </label>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-base-200">
                <button type="button" class="btn btn-ghost" onclick="closeAddStepModal()">Cancel</button>
                <button type="submit" class="btn btn-primary gap-2">
                    <span class="icon-[tabler--plus] size-4"></span>
                    Add Step
                </button>
            </div>
        </form>
    </div>
    <div class="custom-modal-backdrop" onclick="closeAddStepModal()"></div>
</div>

<!-- Edit Step Modal -->
<div id="edit-step-modal" class="custom-modal">
    <div class="custom-modal-box max-w-3xl bg-base-100">
        <!-- Header -->
        <div class="flex items-center justify-between mb-5">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-full bg-info/20 flex items-center justify-center">
                    <span class="icon-[tabler--mail-cog] size-6 text-info"></span>
                </div>
                <div>
                    <h3 class="font-bold text-lg">Edit Email Step</h3>
                    <p class="text-xs text-base-content/50">Modify the email step settings</p>
                </div>
            </div>
            <button type="button" onclick="closeEditStepModal()" class="btn btn-ghost btn-sm btn-circle hover:bg-error/10 hover:text-error transition-colors">
                <span class="icon-[tabler--x] size-5"></span>
            </button>
        </div>

        <form id="edit-step-form" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-control">
                    <label class="label"><span class="label-text font-medium">Step Name</span></label>
                    <input type="text" name="name" id="edit-name" class="input input-bordered" required />
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Delay Days</span></label>
                        <input type="number" name="delay_days" id="edit-delay_days" min="0" class="input input-bordered" required />
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text font-medium">Hours</span></label>
                        <input type="number" name="delay_hours" id="edit-delay_hours" min="0" max="23" class="input input-bordered" required />
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-control">
                    <label class="label"><span class="label-text font-medium">Condition</span></label>
                    <select name="condition_type" id="edit-condition-type" class="select select-bordered" onchange="toggleConditionTag('edit')">
                        <option value="none">No Condition</option>
                        <option value="has_tag">If user has tag</option>
                        <option value="missing_tag">If user missing tag</option>
                    </select>
                </div>
                <div class="form-control" id="edit-condition-tag-wrapper" style="display: none;">
                    <label class="label"><span class="label-text font-medium">Condition Tag</span></label>
                    <select name="condition_tag_id" id="edit-condition_tag_id" class="select select-bordered">
                        <option value="">Select tag...</option>
                        @foreach($tags as $tag)
                            <option value="{{ $tag->id }}">{{ $tag->display_name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-control">
                    <label class="label"><span class="label-text font-medium">From Email</span></label>
                    <input type="email" name="from_email" id="edit-from_email" class="input input-bordered" required />
                </div>
                <div class="form-control">
                    <label class="label"><span class="label-text font-medium">From Name</span></label>
                    <input type="text" name="from_name" id="edit-from_name" class="input input-bordered" required />
                </div>
            </div>

            <div class="form-control">
                <label class="label"><span class="label-text font-medium">Subject</span></label>
                <input type="text" name="subject" id="edit-subject" class="input input-bordered" required />
            </div>

            <div class="form-control">
                <label class="label"><span class="label-text font-medium">Email Body (HTML)</span></label>
                <textarea name="body_html" id="edit-body_html" rows="8" class="textarea textarea-bordered font-mono text-sm" required></textarea>
            </div>

            <div class="form-control">
                <label class="label"><span class="label-text font-medium">Plain Text Version (optional)</span></label>
                <textarea name="body_text" id="edit-body_text" rows="3" class="textarea textarea-bordered font-mono text-sm"></textarea>
            </div>

            <div class="form-control">
                <label class="label cursor-pointer justify-start gap-4">
                    <input type="checkbox" name="is_active" id="edit-is_active" value="1" class="toggle toggle-primary" />
                    <span class="label-text">Step Active</span>
                </label>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-base-200">
                <button type="button" class="btn btn-ghost" onclick="closeEditStepModal()">Cancel</button>
                <button type="submit" class="btn btn-primary gap-2">
                    <span class="icon-[tabler--check] size-4"></span>
                    Update Step
                </button>
            </div>
        </form>
    </div>
    <div class="custom-modal-backdrop" onclick="closeEditStepModal()"></div>
</div>

<!-- Custom Modal Styles -->
<style>
.custom-modal {
    pointer-events: none;
    opacity: 0;
    visibility: hidden;
    position: fixed;
    inset: 0;
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 9999;
    transition: opacity 0.2s ease-out, visibility 0.2s ease-out;
}

.custom-modal.modal-open {
    pointer-events: auto;
    opacity: 1;
    visibility: visible;
}

.custom-modal .custom-modal-box {
    position: relative;
    z-index: 10000;
    padding: 1.5rem;
    border-radius: 1rem;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    max-height: 90vh;
    overflow-y: auto;
    transform: scale(0.95);
    transition: transform 0.2s ease-out;
}

.custom-modal.modal-open .custom-modal-box {
    transform: scale(1);
}

.custom-modal .custom-modal-backdrop {
    position: fixed;
    inset: 0;
    background-color: rgba(0, 0, 0, 0.5);
    z-index: 9998;
}
</style>

<script>
// Add Step Modal Functions
function openAddStepModal() {
    document.getElementById('add-step-modal').classList.add('modal-open');
    document.body.style.overflow = 'hidden';
}

function closeAddStepModal() {
    document.getElementById('add-step-modal').classList.remove('modal-open');
    document.body.style.overflow = '';
    document.getElementById('add-step-form')?.reset();
}

// Edit Step Modal Functions
function openEditStepModal() {
    document.getElementById('edit-step-modal').classList.add('modal-open');
    document.body.style.overflow = 'hidden';
}

function closeEditStepModal() {
    document.getElementById('edit-step-modal').classList.remove('modal-open');
    document.body.style.overflow = '';
}

// Toggle condition tag visibility
function toggleConditionTag(prefix) {
    const conditionType = document.getElementById(prefix + '-condition-type').value;
    const wrapper = document.getElementById(prefix + '-condition-tag-wrapper');
    wrapper.style.display = conditionType !== 'none' ? 'block' : 'none';
}

// Edit step - populate form and open modal
function editStep(step) {
    const form = document.getElementById('edit-step-form');
    form.action = "{{ route('backoffice.funnel.steps.update', [$funnel, ':step']) }}".replace(':step', step.uuid);

    document.getElementById('edit-name').value = step.name;
    document.getElementById('edit-delay_days').value = step.delay_days;
    document.getElementById('edit-delay_hours').value = step.delay_hours;
    document.getElementById('edit-condition-type').value = step.condition_type;
    document.getElementById('edit-condition_tag_id').value = step.condition_tag_id || '';
    document.getElementById('edit-from_email').value = step.from_email;
    document.getElementById('edit-from_name').value = step.from_name;
    document.getElementById('edit-subject').value = step.subject;
    document.getElementById('edit-body_html').value = step.body_html;
    document.getElementById('edit-body_text').value = step.body_text || '';
    document.getElementById('edit-is_active').checked = step.is_active;

    toggleConditionTag('edit');
    openEditStepModal();
}

// Close modals on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeAddStepModal();
        closeEditStepModal();
    }
});
</script>
@endsection
