<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invitation Expired - {{ config('app.name', 'NewDone') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-base-200 flex items-center justify-center p-4">
    <div class="w-full max-w-md text-center">
        <div class="card bg-base-100 shadow-xl">
            <div class="card-body py-12">
                <div class="text-warning mb-4">
                    <span class="icon-[tabler--clock-exclamation] size-16"></span>
                </div>
                <h1 class="text-2xl font-bold text-base-content mb-2">Invitation Expired</h1>
                <p class="text-base-content/60 mb-6">
                    This invitation link has expired. Please contact your team administrator to request a new invitation.
                </p>
                <p class="text-sm text-base-content/50 mb-6">
                    The invitation for <strong>{{ $email }}</strong> is no longer valid.
                </p>
                <a href="{{ url('/') }}" class="btn btn-primary">
                    <span class="icon-[tabler--home] size-5"></span>
                    Go to Homepage
                </a>
            </div>
        </div>
    </div>
</body>
</html>
