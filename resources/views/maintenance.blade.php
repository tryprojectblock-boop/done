<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Maintenance - {{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    <!-- Styles / Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-base-200 flex items-center justify-center p-4">
    <div class="max-w-lg w-full">
        <div class="card bg-base-100 shadow-xl">
            <div class="card-body text-center">
                <!-- Icon -->
                <div class="flex justify-center mb-4">
                    <div class="w-20 h-20 bg-warning/20 rounded-full flex items-center justify-center">
                        <span class="icon-[tabler--tool] size-10 text-warning"></span>
                    </div>
                </div>

                <!-- Title -->
                <h1 class="text-2xl font-bold text-base-content mb-2">
                    Under Maintenance
                </h1>

                <!-- Message -->
                <p class="text-base-content/70 mb-6">
                    {{ $message ?? 'We are currently performing maintenance. Please check back soon.' }}
                </p>

                <!-- Scheduled End Time -->
                @if(!empty($until))
                    <div class="alert alert-info mb-6">
                        <span class="icon-[tabler--clock] size-5"></span>
                        <div class="text-left">
                            <div class="font-medium">Expected completion</div>
                            <div class="text-sm">{{ \Carbon\Carbon::parse($until)->format('F j, Y \a\t g:i A') }}</div>
                        </div>
                    </div>
                @endif

                <!-- What to do -->
                <div class="bg-base-200 rounded-lg p-4 mb-6">
                    <h3 class="font-medium text-base-content mb-2">What can you do?</h3>
                    <ul class="text-sm text-base-content/70 space-y-1 text-left">
                        <li class="flex items-center gap-2">
                            <span class="icon-[tabler--refresh] size-4"></span>
                            Refresh this page in a few minutes
                        </li>
                        <li class="flex items-center gap-2">
                            <span class="icon-[tabler--mail] size-4"></span>
                            Contact support if you need immediate assistance
                        </li>
                    </ul>
                </div>

                <!-- Actions -->
                <div class="flex justify-center">
                    <button onclick="window.location.reload()" class="btn btn-primary">
                        <span class="icon-[tabler--refresh] size-5"></span>
                        Refresh Page
                    </button>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center mt-6 text-sm text-base-content/50">
            {{ config('app.name') }} &copy; {{ date('Y') }}
        </div>
    </div>
</body>
</html>
