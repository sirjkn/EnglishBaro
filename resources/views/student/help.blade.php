<x-layouts.student :title="'Help'">
    <div class="max-w-5xl">
        <p class="text-xs font-bold uppercase tracking-wide text-gray-400">Help</p>
        <h1 class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">How to use the website</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Watch the video again, or send us a message if you're still stuck.</p>

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
            </div>

            <div class="space-y-4">
                <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
                    <p class="text-xs font-bold uppercase tracking-wide text-gray-400">Still stuck?</p>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">Send a message on WhatsApp and we'll get back to you.</p>
                    <a href="{{ $whatsappHref }}" target="_blank" rel="noopener noreferrer" class="mt-3 flex items-center justify-center rounded-full bg-teal-700 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-600">
                        Contact support on WhatsApp
                    </a>
                    <p class="mt-2 text-xs text-gray-400">If you've lost your phone, tell us and we'll reset your device for you.</p>
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
