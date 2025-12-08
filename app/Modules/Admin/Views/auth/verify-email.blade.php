@extends('admin::layouts.auth')

@section('title', 'Admin Verification')

@section('content')
<div class="card bg-base-100 shadow-xl">
    <div class="card-body">
        <h2 class="card-title justify-center mb-2">Verify Your Identity</h2>
        <p class="text-center text-base-content/60 mb-6">Enter your admin email to receive a verification code</p>

        @if($errors->any())
            <div class="alert alert-error mb-4">
                <span class="icon-[tabler--alert-circle] size-5"></span>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        <form action="{{ route('backoffice.verify-email.send') }}" method="POST">
            @csrf

            <div class="form-control mb-6">
                <label class="label">
                    <span class="label-text font-medium">Email Address</span>
                </label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-base-content/50">
                        <span class="icon-[tabler--mail] size-5"></span>
                    </span>
                    <input
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        class="input input-bordered w-full pl-10"
                        placeholder="admin@example.com"
                        required
                        autofocus
                    />
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-full">
                <span class="icon-[tabler--send] size-5"></span>
                Send Verification Code
            </button>
        </form>
    </div>
</div>

<div class="text-center mt-6 text-sm text-base-content/50">
    <a href="{{ url('/') }}" class="hover:text-primary">
        <span class="icon-[tabler--arrow-left] size-4 inline-block mr-1"></span>
        Back to Main Site
    </a>
</div>
@endsection
