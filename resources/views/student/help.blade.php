<x-layouts.student :title="'Help'">
    <div class="max-w-5xl">
        <p class="text-xs font-bold uppercase tracking-wide text-gray-400">Help</p>
        <h1 class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">How to use the website</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Watch the video again, or read the short answers below.</p>

        <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-[minmax(0,1fr)_16rem]">
            <div>
                @if ($helpVideo)
                    <x-video-player :media="$helpVideo" />
                @else
                    <div class="flex aspect-video items-center justify-center rounded-xl bg-gray-100 text-sm text-gray-400 dark:bg-gray-800">
                        No help video has been set up yet.
                    </div>
                @endif

                <h2 class="mt-3 text-base font-bold text-gray-900 dark:text-white">How to use the website</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">The same video you watched when you joined.</p>

                <div x-data="{ openFaq: null }" class="mt-8">
                    <h2 class="text-base font-bold text-gray-900 dark:text-white">Frequently asked questions</h2>
                    <div class="mt-3 space-y-2">
                        @foreach ([
                            ['q' => 'How long do I have access to the course?', 'a' => 'Your access runs from the day you pay for the number of days shown on your plan. You can see exactly how many days are left on your dashboard and on this page.'],
                            ['q' => 'Does one payment cover every track and level?', 'a' => 'Yes. A single payment unlocks every track and every level on EnglishBaro for the length of your plan, so you can move between them freely.'],
                            ['q' => 'How do I move to the next level?', 'a' => 'Finish all the sections in your current level and the next one unlocks automatically. You can also use "Change level" on the My Course page to jump straight to a starting checkpoint (level 1, 10, 20, and so on).'],
                            ['q' => 'Can I retake the placement test?', 'a' => 'Only your first placement test sets your level. After that, visiting the placement test page shows your result and lets you review your answers, but it will not change your level.'],
                            ['q' => 'I lost my phone or can\'t log in. What do I do?', 'a' => 'Contact us on WhatsApp using the button on this page. Tell us you have lost your phone and we will reset your device so you can log in on a new one.'],
                            ['q' => 'How do I contact support?', 'a' => 'Tap "Contact support" above to message us on WhatsApp. We will get back to you as soon as we can.'],
                        ] as $index => $faq)
                            <div class="rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
                                <div role="button" tabindex="0"
                                    @click="openFaq = (openFaq === {{ $index }} ? null : {{ $index }})"
                                    @keydown.enter="openFaq = (openFaq === {{ $index }} ? null : {{ $index }})"
                                    class="flex w-full cursor-pointer items-center justify-between gap-3 px-4 py-3 text-left">
                                    <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $faq['q'] }}</p>
                                    <x-icons.chevron-right class="h-3.5 w-3.5 flex-none text-gray-400 transition-transform" x-bind:class="openFaq === {{ $index }} ? 'rotate-90' : ''" />
                                </div>
                                <div x-show="openFaq === {{ $index }}" x-cloak class="px-4 pb-3 text-sm text-gray-600 dark:text-gray-300">
                                    {{ $faq['a'] }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="space-y-4">
                <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
                    <p class="text-xs font-bold uppercase tracking-wide text-gray-400">Still stuck?</p>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">Send a message and we will get back to you.</p>
                    <a href="{{ $whatsappHref }}" target="_blank" rel="noopener noreferrer" class="mt-3 flex items-center justify-center rounded-full bg-teal-700 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-600">
                        Contact support
                    </a>
                    <p class="mt-2 text-xs text-gray-400">If you have lost your phone, tell us and we will reset your device for you.</p>
                </div>

                @if ($track)
                    <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
                        <p class="text-xs font-bold uppercase tracking-wide text-gray-400">Your course</p>
                        <div class="mt-2 flex items-center gap-3">
                            <span class="flex h-10 w-10 flex-none items-center justify-center rounded-lg bg-teal-700 text-sm font-bold text-white">{{ $track->track_code }}</span>
                            <div class="min-w-0">
                                <p class="truncate text-sm font-bold text-gray-900 dark:text-white">Level {{ $currentLevelNumber }}</p>
                                @if ($subscription)
                                    @php
                                        $daysLeft = max(0, now()->startOfDay()->diffInDays($subscription->expires_at->copy()->startOfDay(), false));
                                    @endphp
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $daysLeft }} days of access left</p>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-layouts.student>
