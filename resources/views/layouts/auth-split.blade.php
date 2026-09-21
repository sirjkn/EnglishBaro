<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased bg-white dark:bg-gray-900">
        <x-site-header />

        <div class="flex items-center justify-center bg-indigo-50 dark:bg-gray-900 px-4 py-10">
            <div class="grid w-full max-w-4xl grid-cols-1 overflow-hidden rounded-3xl bg-white dark:bg-gray-800 shadow-xl md:grid-cols-2">
                <div class="p-8 sm:p-12">
                    {{ $slot }}
                </div>

                <div class="relative hidden items-center justify-center bg-gradient-to-br from-indigo-500 to-indigo-700 p-10 md:flex">
                    <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(circle at 20% 20%, white 0, transparent 40%), radial-gradient(circle at 80% 80%, white 0, transparent 40%);"></div>
                    <div class="relative flex w-full max-w-xs items-center justify-center rounded-3xl bg-white p-6 shadow-lg">
                        <img src="{{ asset('images/logo.png') }}" alt="EnglishBaro" class="w-full max-w-[220px]">
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
