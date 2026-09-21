<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ isset($title) ? $title.' - Admin - '.config('app.name') : 'Admin - '.config('app.name') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased bg-gray-50 dark:bg-gray-900 dark:text-gray-100">
        <div class="flex min-h-screen">
            <aside class="hidden w-64 shrink-0 border-r border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-800 md:block">
                <div class="flex h-16 items-center border-b border-gray-200 px-6 dark:border-gray-800">
                    <a href="{{ route('admin.dashboard') }}">
                        <img src="{{ asset('images/logo-wordmark.png') }}" alt="EnglishBaro" class="h-6 w-auto">
                    </a>
                </div>
                <nav class="space-y-1 overflow-y-auto p-4" style="max-height: calc(100vh - 4rem)">
                    @php
                        $adminNav = [
                            ['route' => 'admin.dashboard', 'label' => 'Dashboard', 'icon' => 'dashboard'],
                            ['route' => 'admin.students.index', 'label' => 'Students', 'icon' => 'user-circle'],
                            ['route' => 'admin.courses.index', 'label' => 'Courses', 'icon' => 'book-open'],
                            ['route' => 'admin.levels.index', 'label' => 'Levels', 'icon' => 'medal'],
                            ['route' => 'admin.payments.index', 'label' => 'Payments', 'icon' => 'credit-card'],
                            ['route' => 'admin.sessions.index', 'label' => 'Sessions', 'icon' => 'login'],
                            ['route' => 'admin.testimonials.index', 'label' => 'Testimonials', 'icon' => 'medal'],
                            ['route' => 'admin.contact-messages.index', 'label' => 'Contact Messages', 'icon' => 'desk-phone'],
                            ['route' => 'admin.audit-logs.index', 'label' => 'Audit Logs', 'icon' => 'map'],
                            ['route' => 'admin.settings.edit', 'label' => 'Settings', 'icon' => 'user-plus'],
                        ];
                    @endphp

                    @foreach ($adminNav as $item)
                        <a href="{{ route($item['route']) }}"
                           class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs($item['route']) || request()->routeIs(explode('.index', $item['route'])[0].'.*') ? 'bg-indigo-50 text-indigo-600 dark:bg-indigo-900/40 dark:text-indigo-300' : 'text-gray-600 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-700' }}">
                            <x-dynamic-component :component="'icons.'.$item['icon']" class="h-4 w-4" />
                            {{ $item['label'] }}
                        </a>
                    @endforeach

                    <a href="{{ route('home') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-700">
                        <x-icons.home class="h-4 w-4" />
                        Back to Site
                    </a>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="mt-2 flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-700">
                            <x-icons.logout class="h-4 w-4" />
                            Logout
                        </button>
                    </form>
                </nav>
            </aside>

            <div class="min-w-0 flex-1">
                <header class="flex items-center justify-between border-b border-gray-200 bg-white px-4 py-3 dark:border-gray-800 dark:bg-gray-800 md:hidden">
                    <img src="{{ asset('images/logo-wordmark.png') }}" alt="EnglishBaro" class="h-6 w-auto">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-sm font-medium text-gray-600 dark:text-gray-300">Logout</button>
                    </form>
                </header>

                @if (session('status'))
                    <div class="mx-4 mt-4 rounded-md bg-green-50 p-3 text-sm text-green-700 dark:bg-green-900/30 dark:text-green-300 sm:mx-6 lg:mx-8">
                        {{ session('status') }}
                    </div>
                @endif

                <main class="p-4 sm:p-6 lg:p-8">
                    {{ $slot }}
                </main>
            </div>
        </div>

        @stack('scripts')
    </body>
</html>
