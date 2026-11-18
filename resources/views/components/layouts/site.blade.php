<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <script>
            // Dark mode is opt-in only (toggled in Settings), never the OS/browser preference.
            try {
                if (localStorage.getItem('theme') === 'dark') {
                    document.documentElement.classList.add('dark');
                }
            } catch (e) {}
        </script>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ isset($title) ? $title.' - '.config('app.name') : config('app.name') }}</title>
        <meta name="description" content="{{ $description ?? 'EnglishBaro is an online English learning platform with video lessons, eBooks, and assessments.' }}">

        <link rel="icon" href="{{ asset('images/favicon.svg') }}" type="image/svg+xml">
        <link rel="icon" href="{{ asset('images/favicon-32x32.png') }}" sizes="32x32" type="image/png">
        <link rel="icon" href="{{ asset('images/favicon-16x16.png') }}" sizes="16x16" type="image/png">
        <link rel="apple-touch-icon" href="{{ asset('images/apple-touch-icon.png') }}">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=poppins:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased bg-white dark:bg-gray-900 dark:text-gray-100">
        @auth
            @if (auth()->user()->user_type === 'student')
                <x-student-app-shell>
                    @if (session('status'))
                        <div class="rounded-md bg-green-50 dark:bg-green-900/30 p-4 text-sm text-green-700 dark:text-green-300">
                            {{ session('status') }}
                        </div>
                    @endif

                    {{ $slot }}

                    <x-site-footer class="-mx-4 mt-16 sm:-mx-6 lg:-mx-8" />
                </x-student-app-shell>
            @else
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

                <x-site-footer />
            @endif
        @else
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

            <x-site-footer />
        @endauth
    </body>
</html>
