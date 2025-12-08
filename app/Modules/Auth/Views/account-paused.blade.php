<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Account Paused - {{ config('app.name', 'NewDone') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

    <!-- Styles / Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-base-200">
    <div class="min-h-screen flex flex-col items-center justify-center p-4">
        <!-- Logo -->
        <div class="mb-8">
            <a href="/" class="flex items-center gap-2">
                <span class="icon-[tabler--checkbox] size-10 text-primary"></span>
                <span class="text-2xl font-bold text-base-content">{{ config('app.name', 'NewDone') }}</span>
            </a>
        </div>

        <!-- Account Paused Card -->
        <div class="w-full max-w-lg">
            <div class="card bg-base-100 shadow-xl">
                <div class="card-body text-center">
                    <!-- Warning Icon -->
                    <div class="flex justify-center mb-4">
                        <div class="w-20 h-20 rounded-full bg-warning/20 flex items-center justify-center">
                            <span class="icon-[tabler--player-pause] size-10 text-warning"></span>
                        </div>
                    </div>

                    <!-- Title -->
                    <h1 class="text-2xl font-bold text-base-content mb-2">Account Temporarily Paused</h1>

                    <!-- Message -->
                    <p class="text-base-content/70 mb-6">
                        Your account has been temporarily paused by the administrator.
                        You won't be able to access your workspace until this is resolved.
                    </p>

                    <!-- Reason Card -->
                    <div class="bg-warning/10 border border-warning/30 rounded-lg p-4 mb-6 text-left">
                        <div class="flex items-start gap-3">
                            <span class="icon-[tabler--alert-triangle] size-5 text-warning mt-0.5 flex-shrink-0"></span>
                            <div>
                                <h3 class="font-semibold text-base-content mb-1">Reason</h3>
                                <p class="text-base-content/80">{{ $company->pause_reason }}</p>

                                @if($company->pause_description)
                                <div class="mt-3 pt-3 border-t border-warning/20">
                                    <h4 class="font-medium text-sm text-base-content/70 mb-1">Additional Details</h4>
                                    <p class="text-sm text-base-content/70">{{ $company->pause_description }}</p>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Pause Date -->
                    <div class="text-sm text-base-content/50 mb-6">
                        <span class="icon-[tabler--calendar] size-4 inline-block mr-1 align-middle"></span>
                        Paused on {{ $company->paused_at->format('F d, Y \a\t h:i A') }}
                    </div>

                    <!-- Contact Info -->
                    <div class="bg-base-200 rounded-lg p-4 mb-6">
                        <h3 class="font-semibold text-base-content mb-2">Need Help?</h3>
                        <p class="text-sm text-base-content/70 mb-3">
                            If you believe this is a mistake or would like to resolve this issue, please contact our support team.
                        </p>
                        <a href="mailto:support@{{ parse_url(config('app.url'), PHP_URL_HOST) }}" class="btn btn-outline btn-sm">
                            <span class="icon-[tabler--mail] size-4"></span>
                            Contact Support
                        </a>
                    </div>

                    <!-- Logout Button -->
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-ghost w-full">
                            <span class="icon-[tabler--logout] size-4"></span>
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="mt-8 text-center text-sm text-base-content/50">
            <p>&copy; {{ date('Y') }} {{ config('app.name', 'NewDone') }}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
