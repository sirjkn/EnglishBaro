@auth
    @php
        $studentJourneyNav = [
            ['route' => 'home', 'label' => 'Landing', 'icon' => 'home'],
            ['route' => 'student.dashboard', 'label' => 'Dashboard', 'icon' => 'dashboard'],
            ['route' => 'tracks.index', 'label' => 'Payment', 'icon' => 'credit-card'],
            ['route' => 'student.placement-test', 'label' => 'Placement Test', 'icon' => 'medal'],
            ['route' => 'student.placement-test.result', 'label' => 'Result', 'icon' => 'medal'],
            ['route' => 'student.intro-video', 'label' => 'Intro Video', 'icon' => 'dashboard'],
            ['route' => 'student.tracks.index', 'label' => 'My Course', 'icon' => 'book-open'],
            ['route' => 'student.more-practice', 'label' => 'More Practice', 'icon' => 'medal'],
            ['route' => 'student.help', 'label' => 'Help', 'icon' => 'desk-phone'],
            ['route' => 'student.account', 'label' => 'Account', 'icon' => 'user-circle'],
        ];
    @endphp

    <div class="hidden border-b border-gray-200 bg-blue-500 px-2 py-1.5 dark:border-gray-800 md:block">
        <div class="flex w-full items-center justify-between gap-0.5">
            @foreach ($studentJourneyNav as $item)
                @if (! $loop->first)
                    <span class="shrink-0 text-[10px] text-blue-200">&gt;</span>
                @endif
                <a href="{{ route($item['route']) }}"
                   class="flex shrink-0 items-center gap-1 whitespace-nowrap rounded px-1.5 py-1 text-[11px] font-medium {{ request()->routeIs($item['route']) || request()->routeIs($item['route'].'.*') ? 'bg-white text-blue-700' : 'text-white hover:bg-blue-400' }}">
                    {{ $item['label'] }}
                </a>
            @endforeach
        </div>
    </div>
@endauth
