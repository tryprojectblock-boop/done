@extends('admin::layouts.auth')

@section('title', 'Admin Login')

@section('content')
<div class="card bg-base-100 shadow-xl">
    <div class="card-body">
        <h2 class="card-title justify-center mb-2">Welcome Back</h2>
        <p class="text-center text-base-content/60 mb-6">Sign in to your admin account</p>

        @if($errors->any())
            <div class="alert alert-error mb-4">
                <span class="icon-[tabler--alert-circle] size-5"></span>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        <form action="{{ route('backoffice.login') }}" method="POST">
            @csrf

            <div class="form-control mb-4">
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

            <div class="form-control mb-6">
                <label class="label">
                    <span class="label-text font-medium">Password</span>
                </label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-base-content/50">
                        <span class="icon-[tabler--lock] size-5"></span>
                    </span>
                    <input
                        type="password"
                        name="password"
                        class="input input-bordered w-full pl-10"
                        placeholder="Enter your password"
                        required
                    />
                </div>
            </div>

            <div class="form-control mb-6">
                <label class="label cursor-pointer justify-start gap-2">
                    <input type="checkbox" name="remember" class="checkbox checkbox-primary checkbox-sm" />
                    <span class="label-text">Remember me</span>
                </label>
            </div>

            <button type="submit" class="btn btn-primary w-full">
                <span class="icon-[tabler--login] size-5"></span>
                Sign In
            </button>
        </form>
    </div>
</div>

<div class="text-center mt-6 text-sm text-base-content/50">
    <a href="{{ route('backoffice.verify-email') }}" class="hover:text-primary">
        <span class="icon-[tabler--arrow-left] size-4 inline-block mr-1"></span>
        Start Over
    </a>
</div>
@endsection
