<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ isset($title) ? $title.' - '.config('app.name') : config('app.name') }}</title>

        <link rel="icon" href="{{ asset('images/favicon.svg') }}" type="image/svg+xml">
        <link rel="icon" href="{{ asset('images/favicon-32x32.png') }}" sizes="32x32" type="image/png">
        <link rel="icon" href="{{ asset('images/favicon-16x16.png') }}" sizes="16x16" type="image/png">
        <link rel="apple-touch-icon" href="{{ asset('images/apple-touch-icon.png') }}">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased bg-gray-50 dark:bg-gray-900 dark:text-gray-100">
        <div class="flex min-h-screen">
            {{-- Sidebar --}}
            <aside
                x-data="{ collapsed: localStorage.getItem('sidebarCollapsed') === 'true' }"
                x-init="$watch('collapsed', value => localStorage.setItem('sidebarCollapsed', value))"
                :class="collapsed ? 'w-20' : 'w-64'"
                class="relative hidden shrink-0 border-r border-gray-200 bg-white transition-all duration-200 dark:border-gray-800 dark:bg-gray-800 md:block"
            >
                <button
                    type="button"
                    @click="collapsed = !collapsed"
                    :aria-expanded="(!collapsed).toString()"
                    aria-label="Toggle sidebar"
                    class="absolute -right-3 top-6 z-10 flex h-6 w-6 items-center justify-center rounded-full border border-gray-200 bg-white text-gray-500 shadow-sm hover:bg-gray-50 hover:text-indigo-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700"
                >
                    <x-icons.chevron-left class="h-3.5 w-3.5 transition-transform duration-200" x-bind:class="collapsed ? 'rotate-180' : ''" />
                </button>

                <div class="flex h-16 items-center border-b border-gray-200 px-6 dark:border-gray-800" :class="collapsed ? 'justify-center px-0' : ''">
                    <a href="{{ route('student.dashboard') }}">
                        <img src="{{ asset('images/logo-wordmark.png') }}" alt="EnglishBaro" class="h-9 w-auto" x-show="!collapsed">
                        <img src="{{ asset('images/favicon-32x32.png') }}" alt="EnglishBaro" class="h-8 w-8" x-show="collapsed" x-cloak>
                    </a>
                </div>
                <nav class="space-y-4 overflow-y-auto overflow-x-hidden p-4" style="max-height: calc(100vh - 4rem)">
                    @php
                        $studentNavGroups = [
                            [
                                'label' => null,
                                'items' => [
                                    ['route' => 'student.dashboard', 'label' => 'Dashboard', 'icon' => 'dashboard'],
                                ],
                            ],
                            [
                                'label' => 'Learning',
                                'items' => [
                                    ['route' => 'student.tracks.index', 'label' => 'My Courses', 'icon' => 'book-open'],
                                    ['route' => 'tracks.index', 'label' => 'All Course Tracks', 'icon' => 'map'],
                                    ['route' => 'student.progress', 'label' => 'My Progress', 'icon' => 'medal'],
                                ],
                            ],
                            [
                                'label' => 'Account',
                                'items' => [
                                    ['route' => 'student.account', 'label' => 'Account', 'icon' => 'user-circle'],
                                    ['route' => 'student.payments', 'label' => 'Payments', 'icon' => 'credit-card'],
                                    ['route' => 'student.notifications', 'label' => 'Notifications', 'icon' => 'bell'],
                                ],
                            ],
                        ];
                    @endphp

                    @foreach ($studentNavGroups as $group)
                        <div>
                            @if ($group['label'])
                                <p class="px-3 pb-1 text-xs font-bold uppercase tracking-wide text-gray-500 dark:text-gray-400" x-show="!collapsed" x-cloak>{{ $group['label'] }}</p>
                            @endif
                            <div class="space-y-1">
                                @foreach ($group['items'] as $item)
                                    <a href="{{ route($item['route']) }}"
                                       :title="collapsed ? '{{ $item['label'] }}' : ''"
                                       :class="collapsed ? 'justify-center px-0' : 'justify-start'"
                                       class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs($item['route']) || request()->routeIs($item['route'].'.*') ? 'bg-indigo-50 text-indigo-600 dark:bg-indigo-900/40 dark:text-indigo-300' : 'text-gray-600 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-700' }}">
                                        <x-dynamic-component :component="'icons.'.$item['icon']" class="h-4 w-4 shrink-0" />
                                        <span x-show="!collapsed" x-cloak>{{ $item['label'] }}</span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endforeach

                    <div class="border-t border-gray-200 pt-3 dark:border-gray-700">
                        <a href="{{ route('home') }}"
                           :title="collapsed ? 'Back to Site' : ''"
                           :class="collapsed ? 'justify-center px-0' : 'justify-start'"
                           class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-700">
                            <x-icons.home class="h-4 w-4 shrink-0" />
                            <span x-show="!collapsed" x-cloak>Back to Site</span>
                        </a>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                    :title="collapsed ? 'Log Out' : ''"
                                    :class="collapsed ? 'justify-center px-0' : 'justify-start'"
                                    class="mt-1 flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-700">
                                <x-icons.logout class="h-4 w-4 shrink-0" />
                                <span x-show="!collapsed" x-cloak>Log Out</span>
                            </button>
                        </form>
                    </div>
                </nav>
            </aside>

            <div class="flex-1">
                {{-- Mobile top bar --}}
                <header class="flex items-center justify-between border-b border-gray-200 bg-white px-4 py-3 dark:border-gray-800 dark:bg-gray-800 md:hidden">
                    <img src="{{ asset('images/logo-wordmark.png') }}" alt="EnglishBaro" class="h-8 w-auto">
                    <div class="flex items-center gap-3">
                        <a href="{{ route('student.notifications') }}" class="text-gray-500 dark:text-gray-300">
                            <x-icons.bell class="h-5 w-5" />
                        </a>
                    </div>
                </header>

                @if (session('status'))
                    <div class="mx-4 mt-4 rounded-md bg-green-50 p-3 text-sm text-green-700 dark:bg-green-900/30 dark:text-green-300">
                        {{ session('status') }}
                    </div>
                @endif

                <main class="p-4 pb-24 sm:p-6 lg:p-8 md:pb-8">
                    {{ $slot }}
                </main>

                {{-- Mobile bottom tab bar --}}
                @php
                    $mobilePrimaryNav = [
                        ['route' => 'student.dashboard', 'label' => 'Dashboard', 'icon' => 'dashboard', 'active' => request()->routeIs('student.dashboard')],
                        ['route' => 'student.tracks.index', 'label' => 'My Courses', 'icon' => 'book-open', 'active' => request()->routeIs('student.tracks.*')],
                        ['route' => 'student.progress', 'label' => 'Progress', 'icon' => 'medal', 'active' => request()->routeIs('student.progress')],
                        ['route' => 'student.payments', 'label' => 'Payments', 'icon' => 'credit-card', 'active' => request()->routeIs('student.payments')],
                    ];
                    $mobileMoreNav = [
                        ['route' => 'tracks.index', 'label' => 'All Course Tracks', 'icon' => 'map'],
                        ['route' => 'student.account', 'label' => 'Account', 'icon' => 'user-circle'],
                        ['route' => 'student.notifications', 'label' => 'Notifications', 'icon' => 'bell'],
                        ['route' => 'home', 'label' => 'Back to Site', 'icon' => 'home'],
                    ];
                    $moreActive = collect($mobileMoreNav)->contains(fn ($item) => request()->routeIs($item['route']) || request()->routeIs($item['route'].'.*'));
                @endphp

                <div x-data="{ moreOpen: false }" class="md:hidden">
                    <template x-if="moreOpen">
                        <div class="fixed inset-0 z-40 bg-black/30" @click="moreOpen = false"></div>
                    </template>

                    <div x-show="moreOpen" x-cloak x-transition
                         class="fixed inset-x-0 bottom-16 z-50 mx-3 rounded-xl border border-gray-200 bg-white p-2 shadow-xl dark:border-gray-700 dark:bg-gray-800">
                        @foreach ($mobileMoreNav as $item)
                            <a href="{{ route($item['route']) }}"
                               class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium {{ request()->routeIs($item['route']) || request()->routeIs($item['route'].'.*') ? 'bg-indigo-50 text-indigo-600 dark:bg-indigo-900/40 dark:text-indigo-300' : 'text-gray-700 dark:text-gray-200' }}">
                                <x-dynamic-component :component="'icons.'.$item['icon']" class="h-4 w-4" />
                                {{ $item['label'] }}
                            </a>
                        @endforeach

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-left text-sm font-medium text-gray-700 dark:text-gray-200">
                                <x-icons.logout class="h-4 w-4" />
                                Log Out
                            </button>
                        </form>
                    </div>

                    <nav class="fixed inset-x-0 bottom-0 z-40 flex items-stretch border-t border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-800" style="padding-bottom: env(safe-area-inset-bottom)">
                        @foreach ($mobilePrimaryNav as $item)
                            <a href="{{ route($item['route']) }}"
                               class="flex flex-1 flex-col items-center gap-0.5 py-2 text-[11px] font-medium {{ $item['active'] ? 'text-indigo-600 dark:text-indigo-400' : 'text-gray-500 dark:text-gray-400' }}">
                                <x-dynamic-component :component="'icons.'.$item['icon']" class="h-5 w-5" />
                                {{ $item['label'] }}
                            </a>
                        @endforeach

                        <button type="button" @click="moreOpen = !moreOpen"
                                class="flex flex-1 flex-col items-center gap-0.5 py-2 text-[11px] font-medium {{ $moreActive ? 'text-indigo-600 dark:text-indigo-400' : 'text-gray-500 dark:text-gray-400' }}">
                            <x-icons.dots-horizontal class="h-5 w-5" />
                            Others
                        </button>
                    </nav>
                </div>
            </div>
        </div>

        @stack('scripts')
    </body>
</html>
