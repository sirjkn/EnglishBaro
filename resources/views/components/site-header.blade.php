<header class="border-b border-gray-200 dark:border-gray-800">
    <nav class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8" aria-label="Main navigation">
        <a href="{{ route('home') }}" class="flex items-center gap-2 text-xl font-bold text-indigo-600 dark:text-indigo-400">
            EnglishBaro
        </a>

        <div class="hidden items-center gap-8 md:flex">
            <a href="{{ route('home') }}" class="text-sm font-medium text-gray-700 hover:text-indigo-600 dark:text-gray-300 dark:hover:text-indigo-400">Home</a>
            <a href="{{ route('courses.index') }}" class="text-sm font-medium text-gray-700 hover:text-indigo-600 dark:text-gray-300 dark:hover:text-indigo-400">Courses</a>
            <a href="{{ route('contact') }}" class="text-sm font-medium text-gray-700 hover:text-indigo-600 dark:text-gray-300 dark:hover:text-indigo-400">Contact</a>
        </div>

        <div class="flex items-center gap-3">
            @auth
                <a href="{{ route('dashboard') }}" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500">Dashboard</a>
            @else
                <a href="{{ route('login') }}" class="text-sm font-medium text-gray-700 hover:text-indigo-600 dark:text-gray-300 dark:hover:text-indigo-400">Login</a>
                <a href="{{ route('register.create') }}" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500">Join</a>
            @endauth
        </div>
    </nav>

    <div class="flex items-center justify-around border-t border-gray-200 py-2 md:hidden dark:border-gray-800">
        <a href="{{ route('home') }}" class="text-sm font-medium text-gray-700 dark:text-gray-300">Home</a>
        <a href="{{ route('courses.index') }}" class="text-sm font-medium text-gray-700 dark:text-gray-300">Courses</a>
        <a href="{{ route('contact') }}" class="text-sm font-medium text-gray-700 dark:text-gray-300">Contact</a>
    </div>
</header>
