<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ isset($title) ? $title.' - '.config('app.name') : config('app.name') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased bg-gray-50 dark:bg-gray-900 dark:text-gray-100">
        <div class="flex min-h-screen">
            {{-- Sidebar --}}
            <aside class="hidden w-64 shrink-0 border-r border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-800 md:block">
                <div class="flex h-16 items-center border-b border-gray-200 px-6 dark:border-gray-800">
                    <a href="{{ route('student.dashboard') }}">
                        <img src="{{ asset('images/logo-wordmark.png') }}" alt="EnglishBaro" class="h-9 w-auto">
                    </a>
                </div>
                <nav class="space-y-1 p-4">
                    @php
                        $studentNav = [
                            ['route' => 'student.dashboard', 'label' => 'Dashboard', 'icon' => 'dashboard'],
                            ['route' => 'student.courses.index', 'label' => 'My Courses', 'icon' => 'book-open'],
                            ['route' => 'courses.index', 'label' => 'All Courses', 'icon' => 'map'],
                            ['route' => 'student.progress', 'label' => 'My Progress', 'icon' => 'medal'],
                            ['route' => 'student.account', 'label' => 'Account', 'icon' => 'user-circle'],
                            ['route' => 'student.payments', 'label' => 'Payments', 'icon' => 'credit-card'],
                            ['route' => 'student.notifications', 'label' => 'Notifications', 'icon' => 'bell'],
                        ];
                    @endphp

                    @foreach ($studentNav as $item)
                        <a href="{{ route($item['route']) }}"
                           class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs($item['route']) || request()->routeIs($item['route'].'.*') ? 'bg-indigo-50 text-indigo-600 dark:bg-indigo-900/40 dark:text-indigo-300' : 'text-gray-600 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-700' }}">
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

            <div class="flex-1">
                {{-- Mobile top bar --}}
                <header class="flex items-center justify-between border-b border-gray-200 bg-white px-4 py-3 dark:border-gray-800 dark:bg-gray-800 md:hidden">
                    <img src="{{ asset('images/logo-wordmark.png') }}" alt="EnglishBaro" class="h-8 w-auto">
                    <div class="flex items-center gap-3">
                        <a href="{{ route('student.account') }}" class="text-sm font-medium text-gray-600 dark:text-gray-300">Account</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="text-sm font-medium text-gray-600 dark:text-gray-300">Logout</button>
                        </form>
                    </div>
                </header>

                <div class="flex items-center justify-around border-b border-gray-200 bg-white py-2 dark:border-gray-800 dark:bg-gray-800 md:hidden">
                    <a href="{{ route('student.dashboard') }}" class="text-xs font-medium {{ request()->routeIs('student.dashboard') ? 'text-indigo-600' : 'text-gray-600 dark:text-gray-300' }}">Dashboard</a>
                    <a href="{{ route('student.courses.index') }}" class="text-xs font-medium {{ request()->routeIs('student.courses.*') ? 'text-indigo-600' : 'text-gray-600 dark:text-gray-300' }}">My Courses</a>
                    <a href="{{ route('student.progress') }}" class="text-xs font-medium {{ request()->routeIs('student.progress') ? 'text-indigo-600' : 'text-gray-600 dark:text-gray-300' }}">Progress</a>
                    <a href="{{ route('student.payments') }}" class="text-xs font-medium {{ request()->routeIs('student.payments') ? 'text-indigo-600' : 'text-gray-600 dark:text-gray-300' }}">Payments</a>
                </div>

                @if (session('status'))
                    <div class="mx-4 mt-4 rounded-md bg-green-50 p-3 text-sm text-green-700 dark:bg-green-900/30 dark:text-green-300">
                        {{ session('status') }}
                    </div>
                @endif

                <main class="p-4 sm:p-6 lg:p-8">
                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
