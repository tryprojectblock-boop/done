<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $form->name }} - Unavailable</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: {{ $form->background_color }};
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center py-8 px-4">
    <div class="max-w-md mx-auto text-center">
        <!-- Logo -->
        @if($form->logo_url)
        <div class="mb-6">
            <img src="{{ $form->logo_url }}" alt="Logo" class="h-12 mx-auto">
        </div>
        @endif

        <!-- Message Card -->
        <div class="bg-white rounded-2xl shadow-xl p-8">
            <div class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 mb-2">Form Unavailable</h1>
            <p class="text-gray-600">
                This ticket submission form is currently not accepting new submissions.
            </p>
        </div>

        <!-- Footer -->
        <div class="mt-6 text-gray-500 text-sm">
            <p>Powered by {{ config('app.name') }}</p>
        </div>
    </div>
</body>
</html>
