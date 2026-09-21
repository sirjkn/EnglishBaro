@php
    $navLinkClasses = fn (bool $active) => $active
        ? 'flex items-center gap-1.5 rounded-full bg-indigo-50 px-3 py-1.5 text-sm font-medium text-indigo-600 dark:bg-indigo-900/40 dark:text-indigo-300'
        : 'flex items-center gap-1.5 rounded-full px-3 py-1.5 text-sm font-medium text-gray-600 hover:bg-gray-50 hover:text-indigo-600 dark:text-gray-300 dark:hover:bg-gray-800 dark:hover:text-indigo-400';
@endphp

<header class="sticky top-0 z-40 border-b border-gray-200 bg-white/90 backdrop-blur dark:border-gray-800 dark:bg-gray-900/90">
    <nav class="mx-auto flex max-w-7xl items-center justify-between gap-2 px-3 py-3 sm:gap-0 sm:px-6 sm:py-4 lg:px-8" aria-label="Main navigation">
        <a href="{{ route('home') }}" class="flex shrink-0 items-center">
            <img src="{{ asset('images/logo-wordmark.png') }}" alt="EnglishBaro" class="h-6 w-auto sm:h-7">
        </a>

        <div class="hidden items-center gap-2 md:flex">
            <a href="{{ route('home') }}" class="{{ $navLinkClasses(request()->routeIs('home')) }}">
                <x-icons.home class="h-4 w-4" />
                Home
            </a>
            <a href="{{ route('courses.index') }}" class="{{ $navLinkClasses(request()->routeIs('courses.*')) }}">
                <x-icons.book-open class="h-4 w-4" />
                Courses
            </a>
            <a href="{{ route('contact') }}" class="{{ $navLinkClasses(request()->routeIs('contact')) }}">
                <x-icons.envelope class="h-4 w-4" />
                Contact
            </a>
        </div>

        <div class="flex shrink-0 items-center gap-2 sm:gap-3">
            @auth
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2 rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500 sm:px-5 sm:py-2.5 sm:text-base">
                    <x-icons.dashboard class="h-4 w-4 sm:h-5 sm:w-5" />
                    <span class="hidden sm:inline">Dashboard</span>
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="flex items-center gap-2 rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 sm:px-5 sm:py-2.5 sm:text-base dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-800">
                        <x-icons.logout class="h-4 w-4 sm:h-5 sm:w-5" />
                        <span class="hidden sm:inline">Logout</span>
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}" class="flex items-center gap-2 rounded-md border border-indigo-600 px-4 py-2 text-sm font-semibold text-indigo-600 hover:bg-indigo-50 sm:px-5 sm:py-2.5 sm:text-base dark:text-indigo-400 dark:hover:bg-indigo-900/30">
                    <x-icons.login class="h-4 w-4 sm:h-5 sm:w-5" />
                    Login
                </a>
                <a href="{{ route('register.create') }}" class="flex items-center gap-2 rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500 sm:px-5 sm:py-2.5 sm:text-base">
                    <x-icons.user-plus class="h-4 w-4 sm:h-5 sm:w-5" />
                    Join
                </a>
            @endauth
        </div>
    </nav>
</header>

{{-- Mobile bottom navigation --}}
<nav class="fixed inset-x-0 bottom-0 z-40 flex items-center justify-around border-t border-gray-200 bg-white/95 py-2 backdrop-blur dark:border-gray-800 dark:bg-gray-900/95 md:hidden" aria-label="Mobile navigation">
    <a href="{{ route('home') }}" class="flex flex-col items-center gap-0.5 rounded-lg px-3 py-1 text-xs font-medium {{ request()->routeIs('home') ? 'bg-indigo-50 text-indigo-600 dark:bg-indigo-900/40 dark:text-indigo-300' : 'text-gray-600 dark:text-gray-300' }}">
        <x-icons.home class="h-4 w-4" />
        Home
    </a>
    <a href="{{ route('courses.index') }}" class="flex flex-col items-center gap-0.5 rounded-lg px-3 py-1 text-xs font-medium {{ request()->routeIs('courses.*') ? 'bg-indigo-50 text-indigo-600 dark:bg-indigo-900/40 dark:text-indigo-300' : 'text-gray-600 dark:text-gray-300' }}">
        <x-icons.book-open class="h-4 w-4" />
        Courses
    </a>
    <a href="{{ route('contact') }}" class="flex flex-col items-center gap-0.5 rounded-lg px-3 py-1 text-xs font-medium {{ request()->routeIs('contact') ? 'bg-indigo-50 text-indigo-600 dark:bg-indigo-900/40 dark:text-indigo-300' : 'text-gray-600 dark:text-gray-300' }}">
        <x-icons.envelope class="h-4 w-4" />
        Contact
    </a>
</nav>
