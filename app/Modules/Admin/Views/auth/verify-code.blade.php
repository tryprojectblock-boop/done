@extends('admin::layouts.auth')

@section('title', 'Enter Verification Code')

@section('content')
<div class="card bg-base-100 shadow-xl">
    <div class="card-body">
        <h2 class="card-title justify-center mb-2">Enter Verification Code</h2>
        <p class="text-center text-base-content/60 mb-6">
            We sent a 6-digit code to<br>
            <strong>{{ $email }}</strong>
        </p>

        @if(session('success'))
            <div class="alert alert-success mb-4">
                <span class="icon-[tabler--circle-check] size-5"></span>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-error mb-4">
                <span class="icon-[tabler--alert-circle] size-5"></span>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        <form action="{{ route('backoffice.verify-code.verify') }}" method="POST">
            @csrf

            <div class="form-control mb-6">
                <label class="label" for="verification-code">
                    <span class="label-text font-medium">Verification Code</span>
                </label>
                <input
                    type="text"
                    name="code"
                    id="verification-code"
                    class="input input-bordered w-full text-center text-2xl tracking-widest"
                    placeholder="000000"
                    maxlength="6"
                    pattern="[0-9]{6}"
                    required
                    autofocus
                    aria-describedby="verification-code-hint"
                />
                <div class="label" id="verification-code-hint">
                    <span class="label-text-alt text-base-content/50">Code expires in 10 minutes</span>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-full mb-4">
                <span class="icon-[tabler--check] size-5"></span>
                Verify Code
            </button>
        </form>

        <div class="text-center">
            <form action="{{ route('backoffice.resend-code') }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="btn btn-ghost btn-sm">
                    <span class="icon-[tabler--refresh] size-4"></span>
                    Resend Code
                </button>
            </form>
        </div>
    </div>
</div>

<div class="text-center mt-6 text-sm text-base-content/50">
    <a href="{{ route('backoffice.verify-email') }}" class="hover:text-primary">
        <span class="icon-[tabler--arrow-left] size-4 inline-block mr-1"></span>
        Use Different Email
    </a>
</div>
@endsection
