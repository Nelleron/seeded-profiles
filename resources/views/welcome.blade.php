<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Seed Invitations Project</title>
    <!-- Tailwind CSS 4.0 via CDN -->
    <script src="https://unpkg.com/@tailwindcss/browser@4"></script>
</head>
<body class="bg-gray-50 flex items-center justify-center min-h-screen">
    <div class="max-w-md w-full bg-white shadow-lg rounded-lg p-8 text-center border border-gray-100">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-green-100 rounded-full mb-4">
            <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
        </div>
        <h1 class="text-2xl font-bold text-gray-900 mb-2 leading-tight">Project is Ready!</h1>
        <p class="text-gray-600 mb-6">
            Environment is configured with Laravel {{ app()->version() }}, Docker (Sail) and Tailwind CDN.
        </p>
        <div class="space-y-3">
            <div class="bg-blue-50 text-blue-700 px-4 py-2 rounded-md text-sm font-medium">
                Next step: Create migrations & models
            </div>
            <a href="https://github.com/{{ config('app.name') }}" class="block text-sm text-gray-400 hover:text-gray-600 transition-colors">
                Waiting for the first commit...
            </a>
        </div>
    </div>
</body>
</html>
