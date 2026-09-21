<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ isset($title) ? $title.' - '.config('app.name') : config('app.name') }}</title>
        <meta name="description" content="{{ $description ?? 'EnglishBaro is an online English learning platform with video lessons, eBooks, and assessments.' }}">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased bg-white dark:bg-gray-900 dark:text-gray-100 pb-16 md:pb-0">
        <x-site-header />

        @if (session('status'))
            <div class="mx-auto max-w-7xl px-4 pt-4 sm:px-6 lg:px-8">
                <div class="rounded-md bg-green-50 dark:bg-green-900/30 p-4 text-sm text-green-700 dark:text-green-300">
                    {{ session('status') }}
                </div>
            </div>
        @endif

        <main>
            {{ $slot }}
        </main>

        <footer class="mt-24 border-t border-gray-200 dark:border-gray-800">
            <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 gap-8 md:grid-cols-3">
                    <div>
                        <div class="text-lg font-bold text-indigo-600 dark:text-indigo-400">EnglishBaro</div>
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                            {{ \App\Models\CompanySetting::get('footer_text', 'Learn English online with video lessons, eBooks, and assessments.') }}
                        </p>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Links</h3>
                        <ul class="mt-3 space-y-2 text-sm text-gray-500 dark:text-gray-400">
                            <li><a href="{{ route('home') }}" class="hover:text-indigo-600">Home</a></li>
                            <li><a href="{{ route('courses.index') }}" class="hover:text-indigo-600">Courses</a></li>
                            <li><a href="{{ route('contact') }}" class="hover:text-indigo-600">Contact</a></li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Contact</h3>
                        <ul class="mt-3 space-y-2 text-sm text-gray-500 dark:text-gray-400">
                            <li>{{ \App\Models\CompanySetting::get('support_email', 'support@englishbaro.test') }}</li>
                            <li>{{ \App\Models\CompanySetting::get('phone', '+000 000 0000') }}</li>
                        </ul>
                    </div>
                </div>
                <p class="mt-8 text-xs text-gray-400 dark:text-gray-500">
                    {{ \App\Models\CompanySetting::get('copyright', '© '.date('Y').' EnglishBaro. All rights reserved.') }}
                </p>
            </div>
        </footer>
    </body>
</html>
