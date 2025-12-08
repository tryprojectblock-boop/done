@extends('layouts.app')

@section('content')
<div class="min-h-screen flex items-center justify-center p-4 bg-base-200">
    <div class="w-full max-w-lg">
        <div class="card bg-base-100 shadow-xl">
            <div class="card-body">
                <!-- Header -->
                <div class="text-center mb-6">
                    <div class="w-16 h-16 rounded-full bg-primary/10 flex items-center justify-center mx-auto mb-4">
                        <span class="icon-[tabler--shield-lock] size-8 text-primary"></span>
                    </div>
                    <h1 class="text-2xl font-bold text-base-content">Set Up Two-Factor Authentication</h1>
                    <p class="text-base-content/60 mt-2">
                        Your organization requires Two-Factor Authentication to access the application.
                    </p>
                </div>

                @if(session('warning'))
                    <div class="alert alert-warning mb-4">
                        <span class="icon-[tabler--alert-triangle] size-5"></span>
                        <span>{{ session('warning') }}</span>
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-error mb-4">
                        <span class="icon-[tabler--alert-circle] size-5"></span>
                        <span>{{ $errors->first() }}</span>
                    </div>
                @endif

                <!-- Step 1: Scan QR Code -->
                <div class="mb-6">
                    <h2 class="font-semibold text-base-content flex items-center gap-2 mb-3">
                        <span class="w-6 h-6 rounded-full bg-primary text-primary-content text-sm flex items-center justify-center">1</span>
                        Scan the QR Code
                    </h2>
                    <p class="text-sm text-base-content/60 mb-4">
                        Open your authenticator app (Google Authenticator, Microsoft Authenticator, Authy, etc.) and scan this QR code:
                    </p>

                    <div class="flex justify-center bg-white p-4 rounded-lg">
                        {!! $qrCodeSvg !!}
                    </div>

                    <div class="mt-4 text-center">
                        <p class="text-xs text-base-content/60 mb-2">Or enter this code manually:</p>
                        <code class="bg-base-200 px-3 py-2 rounded text-sm font-mono select-all">{{ $secret }}</code>
                    </div>
                </div>

                <!-- Step 2: Save Recovery Codes -->
                <div class="mb-6">
                    <h2 class="font-semibold text-base-content flex items-center gap-2 mb-3">
                        <span class="w-6 h-6 rounded-full bg-primary text-primary-content text-sm flex items-center justify-center">2</span>
                        Save Your Recovery Codes
                    </h2>
                    <p class="text-sm text-base-content/60 mb-4">
                        Store these recovery codes in a safe place. You can use them to access your account if you lose your authenticator device.
                    </p>

                    <div class="bg-base-200 rounded-lg p-4">
                        <div class="grid grid-cols-2 gap-2">
                            @foreach($recoveryCodes as $code)
                                <code class="bg-base-100 px-2 py-1 rounded text-sm font-mono text-center">{{ $code }}</code>
                            @endforeach
                        </div>
                    </div>

                    <div class="mt-2 text-center">
                        <button type="button" onclick="copyRecoveryCodes()" class="btn btn-ghost btn-sm">
                            <span class="icon-[tabler--copy] size-4"></span>
                            Copy Codes
                        </button>
                    </div>
                </div>

                <!-- Step 3: Verify -->
                <div class="mb-6">
                    <h2 class="font-semibold text-base-content flex items-center gap-2 mb-3">
                        <span class="w-6 h-6 rounded-full bg-primary text-primary-content text-sm flex items-center justify-center">3</span>
                        Enter Verification Code
                    </h2>
                    <p class="text-sm text-base-content/60 mb-4">
                        Enter the 6-digit code from your authenticator app to verify setup:
                    </p>

                    <form action="{{ route('two-factor.confirm') }}" method="POST">
                        @csrf
                        <div class="form-control">
                            <input type="text"
                                   name="code"
                                   class="input input-bordered text-center text-2xl tracking-widest font-mono @error('code') input-error @enderror"
                                   placeholder="000000"
                                   maxlength="6"
                                   pattern="[0-9]{6}"
                                   autocomplete="one-time-code"
                                   inputmode="numeric"
                                   required
                                   autofocus />
                        </div>

                        <button type="submit" class="btn btn-primary w-full mt-4">
                            <span class="icon-[tabler--check] size-5"></span>
                            Verify and Enable 2FA
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function copyRecoveryCodes() {
    const codes = @json($recoveryCodes);
    const text = codes.join('\n');
    navigator.clipboard.writeText(text).then(() => {
        alert('Recovery codes copied to clipboard!');
    });
}
</script>
@endsection
