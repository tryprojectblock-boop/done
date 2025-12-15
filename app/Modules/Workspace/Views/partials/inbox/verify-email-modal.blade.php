{{-- Verify Email Modal --}}
@if($workspace->inboxSettings && $workspace->inboxSettings->inbound_email)
<div id="verifyEmailModal" class="workspace-modal" role="dialog">
    <div class="workspace-modal-box bg-base-100 shadow-xl max-w-lg">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-12 h-12 rounded-full bg-primary/20 flex items-center justify-center">
                <span class="icon-[tabler--mail-check] size-6 text-primary"></span>
            </div>
            <div>
                <h3 class="text-lg font-bold">Verify Email Setup</h3>
                <p class="text-sm text-base-content/60">Test your email forwarding configuration</p>
            </div>
        </div>

        <div class="space-y-4">
            <div class="p-4 bg-base-200 rounded-lg">
                <p class="text-sm font-medium mb-2">To verify your setup:</p>
                <ol class="text-sm text-base-content/70 space-y-2 list-decimal list-inside">
                    <li>Forward an email to the inbound address below</li>
                    <li>Wait a few seconds for it to be processed</li>
                    <li>Click "Check Verification" to confirm</li>
                </ol>
            </div>

            <div class="form-control">
                <label class="label">
                    <span class="label-text font-medium">Inbound Email Address</span>
                </label>
                <div class="join w-full">
                    <input type="text" value="{{ $workspace->inboxSettings->inbound_email }}" class="input input-bordered join-item flex-1 font-mono text-sm" readonly>
                    <button type="button" class="btn btn-primary join-item" onclick="copyInboundEmail()">
                        <span class="icon-[tabler--copy] size-5"></span>
                    </button>
                </div>
            </div>

            <div class="alert alert-info">
                <span class="icon-[tabler--info-circle] size-5"></span>
                <span class="text-sm">Make sure you've configured your email provider to forward emails to this address.</span>
            </div>
        </div>

        <div class="flex justify-end gap-2 mt-6">
            <button type="button" class="btn btn-ghost" onclick="closeVerifyEmailModal()">Cancel</button>
            <form action="{{ route('workspace.verify-email', $workspace) }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="btn btn-primary gap-2">
                    <span class="icon-[tabler--refresh] size-5"></span>
                    Check Verification
                </button>
            </form>
        </div>
    </div>
</div>

<script>
function copyInboundEmail() {
    const email = '{{ $workspace->inboxSettings->inbound_email ?? '' }}';
    navigator.clipboard.writeText(email).then(() => {
        // Show toast or feedback
        const toast = document.createElement('div');
        toast.className = 'toast toast-top toast-end z-50';
        toast.innerHTML = `
            <div class="alert alert-success">
                <span class="icon-[tabler--check] size-5"></span>
                <span>Email copied to clipboard!</span>
            </div>
        `;
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 3000);
    });
}

function openVerifyEmailModal() {
    document.getElementById('verifyEmailModal').classList.add('open');
    document.body.style.overflow = 'hidden';
}

function closeVerifyEmailModal() {
    document.getElementById('verifyEmailModal').classList.remove('open');
    document.body.style.overflow = '';
}

// Close modal when clicking outside
document.getElementById('verifyEmailModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeVerifyEmailModal();
    }
});
</script>
@endif
