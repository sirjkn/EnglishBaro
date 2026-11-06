@auth
    @php
        $studentJourneyNav = [
            ['route' => 'home', 'label' => 'Landing', 'icon' => 'home'],
            ['route' => 'student.placement-test', 'label' => 'Placement Test', 'icon' => 'medal'],
            ['route' => 'student.dashboard', 'label' => 'Dashboard', 'icon' => 'dashboard'],
            ['route' => 'student.tracks.index', 'label' => 'My Course', 'icon' => 'book-open'],
            ['route' => 'student.more-practice', 'label' => 'More Practice', 'icon' => 'medal'],
            ['route' => 'student.payments', 'label' => 'Payment', 'icon' => 'credit-card'],
            ['route' => 'student.help', 'label' => 'Help', 'icon' => 'desk-phone'],
            ['route' => 'student.account', 'label' => 'Account', 'icon' => 'user-circle'],
        ];
    @endphp

    <div class="hidden border-b border-gray-200 bg-blue-500 px-2 py-1.5 dark:border-gray-800 md:block">
        <div class="flex w-full items-center justify-between gap-1.5 overflow-x-auto">
            @foreach ($studentJourneyNav as $item)
                <a href="{{ route($item['route']) }}"
                   class="flex shrink-0 items-center gap-1 whitespace-nowrap rounded-full border px-3 py-1 text-[11px] font-semibold transition {{ request()->routeIs($item['route']) || request()->routeIs($item['route'].'.*') ? 'border-white bg-white text-blue-700 shadow-sm' : 'border-blue-300 bg-blue-600/40 text-white hover:bg-blue-400' }}">
                    {{ $item['label'] }}
                </a>
            @endforeach
        </div>
    </div>
@endauth
