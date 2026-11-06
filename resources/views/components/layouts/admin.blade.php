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

        <title>{{ isset($title) ? $title.' - Admin - '.config('app.name') : 'Admin - '.config('app.name') }}</title>

        <link rel="icon" href="{{ asset('images/favicon.svg') }}" type="image/svg+xml">
        <link rel="icon" href="{{ asset('images/favicon-32x32.png') }}" sizes="32x32" type="image/png">
        <link rel="icon" href="{{ asset('images/favicon-16x16.png') }}" sizes="16x16" type="image/png">
        <link rel="apple-touch-icon" href="{{ asset('images/apple-touch-icon.png') }}">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=poppins:400,500,600,700&display=swap" rel="stylesheet" />

        <script>
            window.adminMediaUploadUrl = @json(route('admin.media-uploads.store', [], false));
            window.adminMuxAssetsUrl = @json(route('admin.media-uploads.mux-assets', [], false));
            window.adminMuxFromAssetUrl = @json(route('admin.media-uploads.from-mux', [], false));
            window.adminMediaLibraryUrl = @json(route('admin.media-uploads.library', [], false));
        </script>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased bg-gray-50 dark:bg-gray-900 dark:text-gray-100">
        <div class="flex min-h-screen">
            <aside class="hidden w-64 shrink-0 border-r border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-800 md:block">
                <div class="flex h-16 items-center border-b border-gray-200 px-6 dark:border-gray-800">
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2">
                        <img src="{{ asset('images/logo-icon.png') }}" alt="" class="h-8 w-8">
                        <img src="{{ asset('images/logo-wordmark.png') }}" alt="EnglishBaro" class="h-7 w-auto">
                    </a>
                </div>
                <nav class="space-y-7 p-4">
                    @php
                        $adminNavGroups = [
                            [
                                'label' => null,
                                'items' => [
                                    ['route' => 'admin.dashboard', 'label' => 'Dashboard', 'icon' => 'dashboard', 'module' => 'dashboard'],
                                ],
                            ],
                            [
                                'label' => 'Student Management',
                                'items' => [
                                    ['route' => 'admin.students.index', 'label' => 'Students', 'icon' => 'user-circle', 'module' => 'students'],
                                    ['route' => 'admin.sessions.index', 'label' => 'Sessions', 'icon' => 'login', 'module' => 'sessions'],
                                    ['route' => 'admin.testimonials.index', 'label' => 'Testimonials', 'icon' => 'medal', 'module' => 'testimonials'],
                                ],
                            ],
                            [
                                'label' => 'Course Management',
                                'items' => [
                                    ['route' => 'admin.courses.index', 'label' => 'Courses', 'icon' => 'book-open', 'module' => 'tracks'],
                                    ['route' => 'admin.tracks.index', 'label' => 'Units (Tracks)', 'icon' => 'book-open', 'module' => 'tracks'],
                                    ['route' => 'admin.enrollments.index', 'label' => 'Enrollments', 'icon' => 'user-plus', 'module' => 'enrollments'],
                                    ['route' => 'admin.placement-test.index', 'label' => 'Placement Test', 'icon' => 'medal', 'module' => 'placement-test'],
                                    ['route' => 'admin.practice-questions.index', 'label' => 'More Practice', 'icon' => 'pencil', 'module' => 'more-practice'],
                                    ['route' => 'admin.answer-reviews.index', 'label' => 'Answer Reviews', 'icon' => 'desk-phone', 'module' => 'answer-reviews'],
                                ],
                            ],
                            [
                                'label' => 'Financial',
                                'items' => [
                                    ['route' => 'admin.payments.index', 'label' => 'Payments', 'icon' => 'credit-card', 'module' => 'payments'],
                                ],
                            ],
                            [
                                'label' => 'System',
                                'items' => [
                                    ['route' => 'admin.users.index', 'label' => 'Users', 'icon' => 'user-plus', 'module' => 'users'],
                                    ['route' => 'admin.settings.edit', 'label' => 'Settings', 'icon' => 'user-plus', 'module' => 'settings'],
                                    ['route' => 'admin.audit-logs.index', 'label' => 'Audit Logs', 'icon' => 'map', 'module' => 'audit-logs'],
                                    ['route' => 'admin.support.index', 'label' => 'Support', 'icon' => 'desk-phone', 'module' => 'support'],
                                ],
                            ],
                        ];

                        // Staff only see the modules they've been granted;
                        // admins (hasModule() always true for them) see everything.
                        $adminNavGroups = collect($adminNavGroups)
                            ->map(fn ($group) => [
                                'label' => $group['label'],
                                'items' => collect($group['items'])->filter(fn ($item) => auth()->user()->hasModule($item['module']))->values()->all(),
                            ])
                            ->filter(fn ($group) => ! empty($group['items']))
                            ->values()
                            ->all();
                    @endphp

                    @foreach ($adminNavGroups as $group)
                        <div>
                            @if ($group['label'])
                                <p class="px-3 pb-1 text-xs font-bold uppercase tracking-wide text-black dark:text-gray-200">{{ $group['label'] }}</p>
                            @endif
                            <div class="space-y-1">
                                @foreach ($group['items'] as $item)
                                    <a href="{{ route($item['route']) }}"
                                       class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-normal {{ request()->routeIs($item['route']) || request()->routeIs(explode('.index', $item['route'])[0].'.*') ? 'bg-indigo-50 text-indigo-600 dark:bg-indigo-900/40 dark:text-indigo-300' : 'text-black hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-gray-700' }}">
                                        <x-dynamic-component :component="'icons.'.$item['icon']" class="h-4 w-4" />
                                        {{ $item['label'] }}
                                        @if ($item['route'] === 'admin.answer-reviews.index' && ($pendingAnswerReviewCount ?? 0) > 0)
                                            <span class="ml-auto rounded-full bg-red-500 px-2 py-0.5 text-[10px] font-bold text-white">{{ $pendingAnswerReviewCount }}</span>
                                        @endif
                                        @if ($item['route'] === 'admin.support.index' && ($waitingSupportCount ?? 0) > 0)
                                            <span class="ml-auto rounded-full bg-red-500 px-2 py-0.5 text-[10px] font-bold text-white">{{ $waitingSupportCount }}</span>
                                        @endif
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endforeach

                    <div class="border-t border-gray-200 pt-3 dark:border-gray-700">
                        <a href="{{ route('home') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-normal text-black hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-gray-700">
                            <x-icons.home class="h-4 w-4" />
                            Back to Site
                        </a>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="mt-1 flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm font-normal text-black hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-gray-700">
                                <x-icons.logout class="h-4 w-4" />
                                Log Out
                            </button>
                        </form>
                    </div>
                </nav>
            </aside>

            <div class="min-w-0 flex-1">
                <header class="sticky top-0 z-30 hidden items-center justify-end gap-2 border-b border-gray-200 bg-white px-4 py-2.5 dark:border-gray-800 dark:bg-gray-800 md:flex">
                    <x-help-button class="flex items-center gap-1.5 rounded-md bg-green-500 px-3 py-1.5 text-sm font-medium text-white hover:bg-green-600" />
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="flex items-center gap-1.5 rounded-md border border-gray-300 bg-white px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">
                            <x-icons.logout class="h-4 w-4" />
                            Logout
                        </button>
                    </form>
                </header>

                <header class="flex items-center justify-between border-b border-gray-200 bg-white px-4 py-3 dark:border-gray-800 dark:bg-gray-800 md:hidden">
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2">
                        <img src="{{ asset('images/logo-icon.png') }}" alt="" class="h-7 w-7">
                        <img src="{{ asset('images/logo-wordmark.png') }}" alt="EnglishBaro" class="h-6 w-auto">
                    </a>
                    <div class="flex items-center gap-3">
                        <x-help-button class="flex items-center gap-1 rounded-md bg-green-500 px-2.5 py-1 text-xs font-medium text-white hover:bg-green-600" />
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="text-sm font-medium text-gray-600 dark:text-gray-300">Logout</button>
                        </form>
                    </div>
                </header>

                @if (session('status'))
                    <div class="mx-4 mt-4 rounded-md bg-green-50 p-3 text-sm text-green-700 dark:bg-green-900/30 dark:text-green-300 sm:mx-6 lg:mx-8">
                        {{ session('status') }}
                    </div>
                @endif

                <main class="p-4 pb-24 sm:p-6 lg:p-8 md:pb-8">
                    {{ $slot }}
                </main>

                {{-- Mobile bottom tab bar: the full sidebar is desktop-only, so
                     a handful of primary sections sit in the tab bar and the
                     rest live in the grouped "Others" sheet below. --}}
                @php
                    $adminPrimaryRoutes = ['admin.dashboard', 'admin.students.index', 'admin.tracks.index', 'admin.payments.index'];

                    $adminPrimaryNav = collect($adminNavGroups)
                        ->flatMap(fn ($group) => $group['items'])
                        ->filter(fn ($item) => in_array($item['route'], $adminPrimaryRoutes, true))
                        ->sortBy(fn ($item) => array_search($item['route'], $adminPrimaryRoutes, true))
                        ->values();

                    $adminMoreGroups = collect($adminNavGroups)
                        ->map(fn ($group) => [
                            'label' => $group['label'],
                            'items' => collect($group['items'])->reject(fn ($item) => in_array($item['route'], $adminPrimaryRoutes, true))->values(),
                        ])
                        ->filter(fn ($group) => $group['items']->isNotEmpty())
                        ->values();

                    $adminMoreActive = $adminMoreGroups
                        ->flatMap(fn ($group) => $group['items'])
                        ->contains(fn ($item) => request()->routeIs($item['route']) || request()->routeIs(explode('.index', $item['route'])[0].'.*'));
                @endphp

                <div x-data="{ moreOpen: false }" class="md:hidden">
                    <template x-if="moreOpen">
                        <div class="fixed inset-0 z-40 bg-black/30" @click="moreOpen = false"></div>
                    </template>

                    <div x-show="moreOpen" x-cloak x-transition
                         class="fixed inset-x-0 bottom-16 z-50 mx-3 max-h-[70vh] overflow-y-auto rounded-xl border border-black/10 bg-white p-2 shadow-xl dark:border-white/10 dark:bg-gray-800">
                        @foreach ($adminMoreGroups as $group)
                            <div>
                                @if ($group['label'])
                                    <p class="px-3 pb-1 pt-2 text-xs font-bold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ $group['label'] }}</p>
                                @endif
                                <div class="space-y-1">
                                    @foreach ($group['items'] as $item)
                                        <a href="{{ route($item['route']) }}"
                                           class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-normal {{ request()->routeIs($item['route']) || request()->routeIs(explode('.index', $item['route'])[0].'.*') ? 'bg-indigo-50 text-indigo-600 dark:bg-indigo-900/40 dark:text-indigo-300' : 'text-gray-700 dark:text-gray-200' }}">
                                            <x-dynamic-component :component="'icons.'.$item['icon']" class="h-4 w-4" />
                                            {{ $item['label'] }}
                                            @if ($item['route'] === 'admin.answer-reviews.index' && ($pendingAnswerReviewCount ?? 0) > 0)
                                                <span class="ml-auto rounded-full bg-red-500 px-2 py-0.5 text-[10px] font-bold text-white">{{ $pendingAnswerReviewCount }}</span>
                                            @endif
                                            @if ($item['route'] === 'admin.support.index' && ($waitingSupportCount ?? 0) > 0)
                                                <span class="ml-auto rounded-full bg-red-500 px-2 py-0.5 text-[10px] font-bold text-white">{{ $waitingSupportCount }}</span>
                                            @endif
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach

                        <div class="border-t border-gray-100 pt-2 dark:border-gray-700">
                            <a href="{{ route('home') }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-normal text-gray-700 dark:text-gray-200">
                                <x-icons.home class="h-4 w-4" />
                                Back to Site
                            </a>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-left text-sm font-normal text-gray-700 dark:text-gray-200">
                                    <x-icons.logout class="h-4 w-4" />
                                    Log Out
                                </button>
                            </form>
                        </div>
                    </div>

                    <nav class="fixed inset-x-0 bottom-0 z-40 flex items-stretch border-t border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-800" style="padding-bottom: env(safe-area-inset-bottom)">
                        @foreach ($adminPrimaryNav as $item)
                            @php $isActive = request()->routeIs($item['route']) || request()->routeIs(explode('.index', $item['route'])[0].'.*'); @endphp
                            <a href="{{ route($item['route']) }}"
                               class="relative flex flex-1 flex-col items-center gap-0.5 py-2 text-[11px] font-medium text-indigo-600 dark:text-indigo-400">
                                @if ($isActive)
                                    <span class="absolute bottom-0 left-1/2 h-0.5 w-5 -translate-x-1/2 rounded-full bg-indigo-600 dark:bg-indigo-400"></span>
                                @endif
                                <x-dynamic-component :component="'icons.'.$item['icon']" class="h-5 w-5" />
                                {{ $item['label'] }}
                            </a>
                        @endforeach

                        <button type="button" @click="moreOpen = !moreOpen"
                                class="relative flex flex-1 flex-col items-center gap-0.5 py-2 text-[11px] font-medium text-indigo-600 dark:text-indigo-400">
                            @if ($adminMoreActive)
                                <span class="absolute bottom-0 left-1/2 h-0.5 w-5 -translate-x-1/2 rounded-full bg-indigo-600 dark:bg-indigo-400"></span>
                            @endif
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
