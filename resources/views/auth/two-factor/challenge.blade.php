<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Two-Factor Authentication - {{ config('app.name', 'NewDone') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-base-200">
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="w-full max-w-md">
            <div class="card bg-base-100 shadow-xl">
                <div class="card-body">
                    <!-- Header -->
                    <div class="text-center mb-6">
                        <div class="w-16 h-16 rounded-full bg-primary/10 flex items-center justify-center mx-auto mb-4">
                            <span class="icon-[tabler--shield-lock] size-8 text-primary"></span>
                        </div>
                        <h1 class="text-2xl font-bold text-base-content">Two-Factor Authentication</h1>
                        <p class="text-base-content/60 mt-2">
                            Enter the verification code from your authenticator app
                        </p>
                    </div>

                    @if($errors->any())
                        <div class="alert alert-error mb-4">
                            <span class="icon-[tabler--alert-circle] size-5"></span>
                            <span>{{ $errors->first() }}</span>
                        </div>
                    @endif

                    <form action="{{ route('two-factor.verify') }}" method="POST">
                        @csrf

                        <div class="form-control mb-4">
                            <label class="label" for="two-factor-code">
                                <span class="label-text font-medium">Verification Code</span>
                            </label>
                            <input type="text"
                                   name="code"
                                   id="two-factor-code"
                                   class="input input-bordered text-center text-2xl tracking-widest font-mono"
                                   placeholder="000000"
                                   autocomplete="one-time-code"
                                   inputmode="numeric"
                                   required
                                   autofocus
                                   aria-describedby="two-factor-code-hint" />
                            <div class="label" id="two-factor-code-hint">
                                <span class="label-text-alt text-base-content/60">
                                    Enter the 6-digit code from your authenticator app
                                </span>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-full">
                            <span class="icon-[tabler--login] size-5"></span>
                            Verify
                        </button>
                    </form>

                    <!-- Recovery Code Option -->
                    <div class="divider">OR</div>

                    <div class="text-center">
                        <p class="text-sm text-base-content/60 mb-2">
                            Lost access to your authenticator app?
                        </p>
                        <button type="button" onclick="showRecoveryForm()" class="btn btn-ghost btn-sm">
                            Use a Recovery Code
                        </button>
                    </div>

                    <!-- Recovery Code Form (Hidden by default) -->
                    <div id="recovery-form" class="hidden mt-4">
                        <form action="{{ route('two-factor.verify') }}" method="POST">
                            @csrf
                            <div class="form-control mb-4">
                                <label class="label" for="recovery-code">
                                    <span class="label-text font-medium">Recovery Code</span>
                                </label>
                                <input type="text"
                                       name="code"
                                       id="recovery-code"
                                       class="input input-bordered font-mono"
                                       placeholder="Enter recovery code"
                                       required />
                            </div>
                            <button type="submit" class="btn btn-outline w-full">
                                Verify with Recovery Code
                            </button>
                        </form>
                    </div>

                    <!-- Back to Login -->
                    <div class="text-center mt-6">
                        <a href="{{ route('login') }}" class="text-sm text-base-content/60 hover:text-base-content">
                            <span class="icon-[tabler--arrow-left] size-4 inline-block mr-1"></span>
                            Back to Login
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showRecoveryForm() {
            document.getElementById('recovery-form').classList.toggle('hidden');
        }
    </script>
</body>
</html>
