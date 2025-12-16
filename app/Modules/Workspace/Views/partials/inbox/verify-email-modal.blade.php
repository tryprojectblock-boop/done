{{-- Verify Email Modal --}}
@if($workspace->inboxSettings)
<div id="verifyEmailModal" class="workspace-modal" role="dialog">
    <div class="workspace-modal-box bg-base-100 shadow-xl" style="max-width: 36rem; max-height: 90vh; overflow-y: auto;">
        {{-- Header --}}
        <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 rounded-full {{ $workspace->inboxSettings->email_verified ? 'bg-success/20' : 'bg-primary/20' }} flex items-center justify-center flex-shrink-0">
                <span class="icon-[tabler--mail-check] size-5 {{ $workspace->inboxSettings->email_verified ? 'text-success' : 'text-primary' }}"></span>
            </div>
            <div>
                <h3 class="text-lg font-bold">Email Configuration</h3>
                <p class="text-sm text-base-content/60">Configure email forwarding for your inbox</p>
            </div>
        </div>

        {{-- Verification Status --}}
        @if($workspace->inboxSettings->email_verified)
        <div class="alert alert-success py-2 mb-4">
            <span class="icon-[tabler--circle-check] size-4"></span>
            <span class="text-sm">Verified {{ $workspace->inboxSettings->email_verified_at->diffForHumans() }}</span>
        </div>
        @elseif($workspace->inboxSettings->from_email)
        <div class="alert alert-warning py-2 mb-4">
            <span class="icon-[tabler--clock] size-4"></span>
            <span class="text-sm">Pending - Waiting for first email via Mailgun</span>
        </div>
        @endif

        <form id="verifyEmailForm" action="{{ route('workspace.verify-email', $workspace) }}" method="POST">
            @csrf

            {{-- Alert Messages --}}
            <div id="verifyEmailAlert" class="hidden mb-4"></div>

            {{-- From Email --}}
            <div class="form-control mb-3">
                <label class="label py-1">
                    <span class="label-text font-medium text-sm">From Email Address</span>
                </label>
                <input type="email"
                       name="from_email"
                       id="fromEmailInput"
                       value="{{ old('from_email', $workspace->inboxSettings->from_email ?? '') }}"
                       placeholder="support@yourdomain.com"
                       class="input input-bordered input-sm w-full"
                       required>
                <label class="label py-0.5">
                    <span id="fromEmailError" class="label-text-alt text-xs text-error hidden"></span>
                    <span id="fromEmailHint" class="label-text-alt text-xs text-base-content/50">Email customers will see in replies. This address will be mapped to your inbound email and cannot be used by another workspace.</span>
                </label>
            </div>

            {{-- Inbound Email --}}
            <div class="form-control mb-4">
                <label class="label py-1">
                    <span class="label-text font-medium text-sm">Inbound Email Address</span>
                </label>
                <div class="join w-full">
                    <input type="text" value="{{ $workspace->inboxSettings->inbound_email ?? 'Not generated' }}" class="input input-bordered input-sm join-item flex-1 font-mono text-xs bg-base-200" readonly>
                    @if($workspace->inboxSettings->inbound_email)
                    <button type="button" class="btn btn-ghost btn-sm join-item" onclick="copyInboundEmail()" title="Copy">
                        <span class="icon-[tabler--copy] size-4"></span>
                    </button>
                    @endif
                </div>
            </div>

            {{-- Setup Instructions --}}
            <div class="text-xs font-medium text-base-content/50 mb-2">MAILGUN SETUP</div>
            <div class="bg-base-200 rounded-lg p-3 space-y-2 mb-4 text-sm">
                <div class="flex gap-2">
                    <span class="badge badge-primary badge-xs mt-1">1</span>
                    <div>
                        <span class="font-medium">MX Records</span>
                        <span class="text-base-content/60"> - Point to mxa.mailgun.org & mxb.mailgun.org</span>
                    </div>
                </div>
                <div class="flex gap-2">
                    <span class="badge badge-primary badge-xs mt-1">2</span>
                    <div>
                        <span class="font-medium">Inbound Route</span>
                        <span class="text-base-content/60"> - Forward to:</span>
                        <code class="text-xs bg-primary/10 text-primary px-1 rounded block mt-1 break-all">{{ url('/api/webhooks/mailgun/inbound') }}</code>
                    </div>
                </div>
                <div class="flex gap-2">
                    <span class="badge badge-primary badge-xs mt-1">3</span>
                    <div>
                        <span class="font-medium">Test</span>
                        <span class="text-base-content/60"> - Send email to inbound address above</span>
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex justify-end gap-2">
                <button type="button" class="btn btn-ghost btn-sm" onclick="closeVerifyEmailModal()">Cancel</button>
                <button type="submit" id="verifyEmailBtn" class="btn btn-primary btn-sm">
                    <span class="icon-[tabler--send] size-4"></span>
                    <span id="verifyEmailBtnText">Send Verification</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function copyInboundEmail() {
    const email = '{{ $workspace->inboxSettings->inbound_email ?? '' }}';
    navigator.clipboard.writeText(email).then(() => {
        const toast = document.createElement('div');
        toast.className = 'toast toast-top toast-end z-50';
        toast.innerHTML = '<div class="alert alert-success py-2"><span class="icon-[tabler--check] size-4"></span><span class="text-sm">Copied!</span></div>';
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 2000);
    });
}

function openVerifyEmailModal() {
    document.getElementById('verifyEmailModal').classList.add('open');
    document.body.style.overflow = 'hidden';
    clearVerifyEmailErrors();
}

function closeVerifyEmailModal() {
    document.getElementById('verifyEmailModal').classList.remove('open');
    document.body.style.overflow = '';
}

function clearVerifyEmailErrors() {
    const input = document.getElementById('fromEmailInput');
    const errorSpan = document.getElementById('fromEmailError');
    const hintSpan = document.getElementById('fromEmailHint');
    const alertDiv = document.getElementById('verifyEmailAlert');

    input?.classList.remove('input-error');
    errorSpan?.classList.add('hidden');
    hintSpan?.classList.remove('hidden');
    alertDiv?.classList.add('hidden');
}

function showVerifyEmailError(message) {
    const input = document.getElementById('fromEmailInput');
    const errorSpan = document.getElementById('fromEmailError');
    const hintSpan = document.getElementById('fromEmailHint');

    input?.classList.add('input-error');
    if (errorSpan) {
        errorSpan.textContent = message;
        errorSpan.classList.remove('hidden');
    }
    hintSpan?.classList.add('hidden');
}

function showVerifyEmailAlert(message, type = 'success') {
    const alertDiv = document.getElementById('verifyEmailAlert');
    if (alertDiv) {
        const icon = type === 'success' ? 'tabler--check' : 'tabler--alert-circle';
        alertDiv.className = `alert alert-${type} py-2 mb-4`;
        alertDiv.innerHTML = `<span class="icon-[${icon}] size-4"></span><span class="text-sm">${message}</span>`;
    }
}

document.getElementById('verifyEmailModal')?.addEventListener('click', function(e) {
    if (e.target === this) closeVerifyEmailModal();
});

// Handle form submission via AJAX
document.getElementById('verifyEmailForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();

    const form = this;
    const btn = document.getElementById('verifyEmailBtn');
    const btnText = document.getElementById('verifyEmailBtnText');
    const input = document.getElementById('fromEmailInput');

    // Clear previous errors
    clearVerifyEmailErrors();

    // Show loading state
    btn.disabled = true;
    btnText.textContent = 'Sending...';

    try {
        const formData = new FormData(form);
        const response = await fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            }
        });

        const data = await response.json();

        if (response.ok && data.success) {
            showVerifyEmailAlert(data.message, 'success');
            // Update the input value
            if (input) input.value = formData.get('from_email');
        } else if (data.errors?.from_email) {
            showVerifyEmailError(data.errors.from_email[0] || data.errors.from_email);
        } else if (data.error) {
            showVerifyEmailError(data.error);
        } else {
            showVerifyEmailError('An error occurred. Please try again.');
        }
    } catch (error) {
        console.error('Error:', error);
        showVerifyEmailError('An error occurred. Please try again.');
    } finally {
        btn.disabled = false;
        btnText.textContent = 'Send Verification';
    }
});
</script>
@endif
