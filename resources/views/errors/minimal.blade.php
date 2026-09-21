<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $code }} - {{ $title }} | {{ config('app.name') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-gray-100 dark:bg-gray-900">
        <div class="min-h-screen flex flex-col items-center justify-center px-4 text-center">
            <p class="text-sm font-semibold tracking-wide text-indigo-600 dark:text-indigo-400 uppercase">Error {{ $code }}</p>
            <h1 class="mt-2 text-3xl font-bold text-gray-900 dark:text-white sm:text-4xl">{{ $title }}</h1>
            <p class="mt-4 max-w-md text-base text-gray-500 dark:text-gray-400">{{ $message }}</p>
            <a href="{{ url('/') }}" class="mt-8 inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-500">
                Back to Home
            </a>
        </div>
    </body>
</html>
