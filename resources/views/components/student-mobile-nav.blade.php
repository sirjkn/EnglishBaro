@auth
    @if (auth()->user()->user_type === 'student')
        @php
            $mobilePrimaryNav = [
                ['route' => 'home', 'label' => 'Account', 'icon' => 'home', 'active' => request()->routeIs('home')],
                ['route' => 'student.course', 'label' => 'Course', 'icon' => 'book-open', 'active' => request()->routeIs('student.course')],
                ['route' => 'student.placement-test', 'label' => 'Placement Test', 'icon' => 'medal', 'active' => request()->routeIs('student.placement-test*')],
                ['route' => 'student.payments', 'label' => 'Payments', 'icon' => 'credit-card', 'active' => request()->routeIs('student.payments')],
            ];
            $mobileMoreNav = [
                ['route' => 'student.account', 'label' => 'Sessions', 'icon' => 'user-circle'],
                ['route' => 'student.notifications', 'label' => 'Notifications', 'icon' => 'bell'],
                ['route' => 'student.support.index', 'label' => 'Support', 'icon' => 'desk-phone'],
            ];
            $moreActive = collect($mobileMoreNav)->contains(fn ($item) => request()->routeIs($item['route']) || request()->routeIs($item['route'].'.*'));
        @endphp

        <div x-data="{ moreOpen: false }" class="md:hidden">
            <template x-if="moreOpen">
                <div class="fixed inset-0 z-40 bg-black/30" @click="moreOpen = false"></div>
            </template>

            <div x-show="moreOpen" x-cloak x-transition
                 class="fixed inset-x-0 bottom-16 z-50 mx-3 rounded-xl border border-black/10 bg-white p-2 shadow-xl dark:border-white/10 dark:bg-gray-800">
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
                       class="relative flex flex-1 flex-col items-center gap-0.5 py-2 text-[11px] font-medium text-indigo-600 dark:text-indigo-400">
                        @if ($item['active'])
                            <span class="absolute bottom-0 left-1/2 h-0.5 w-5 -translate-x-1/2 rounded-full bg-indigo-600 dark:bg-indigo-400"></span>
                        @endif
                        <x-dynamic-component :component="'icons.'.$item['icon']" class="h-5 w-5" />
                        {{ $item['label'] }}
                    </a>
                @endforeach

                <button type="button" @click="moreOpen = !moreOpen"
                        class="relative flex flex-1 flex-col items-center gap-0.5 py-2 text-[11px] font-medium text-indigo-600 dark:text-indigo-400">
                    @if ($moreActive)
                        <span class="absolute bottom-0 left-1/2 h-0.5 w-5 -translate-x-1/2 rounded-full bg-indigo-600 dark:bg-indigo-400"></span>
                    @endif
                    <x-icons.dots-horizontal class="h-5 w-5" />
                    Others
                </button>
            </nav>
        </div>
    @endif
@endauth
