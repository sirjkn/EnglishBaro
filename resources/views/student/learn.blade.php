<x-layouts.student :title="$lesson->title">
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-[18rem_minmax(0,1fr)_16rem]">
        {{-- Sidebar: every level of the track, current one expanded --}}
        <div class="order-2 lg:order-1">
            <div class="rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
                <div class="border-b border-gray-200 p-4 dark:border-gray-700">
                    <p class="text-xs font-medium uppercase text-indigo-600 dark:text-indigo-400">Track {{ $track->track_code }}</p>
                    <a href="{{ route('student.tracks.show', $track) }}" class="text-sm font-semibold text-gray-900 hover:text-indigo-600 dark:text-white">
                        {{ $track->name }}
                    </a>
                </div>

                <p class="px-4 pt-3 text-xs font-semibold uppercase tracking-wide text-gray-400">Course Material</p>

                <div class="max-h-[36rem] overflow-y-auto p-2" x-data="{ open: {{ $level->number }} }">
                    @foreach ($levelStatuses as $number => $status)
                        @php $isCurrent = $number === $level->number; @endphp
                        <div class="border-b border-gray-100 last:border-0 dark:border-gray-700">
                            <button
                                type="button"
                                @click="open = (open === {{ $number }} ? null : {{ $number }})"
                                :class="open === {{ $number }} ? 'bg-indigo-50 dark:bg-indigo-900/30' : ''"
                                class="flex w-full items-center gap-2 rounded-lg px-2 py-2.5 text-left text-sm font-medium {{ $status['unlocked'] ? 'text-gray-900 dark:text-white' : 'text-gray-400 dark:text-gray-500' }}"
                            >
                                @if ($status['completed'])
                                    <span class="flex h-4 w-4 flex-none items-center justify-center rounded-full bg-green-500 text-[10px] text-white">&#10003;</span>
                                @elseif (! $status['unlocked'])
                                    <x-icons.lock class="h-3.5 w-3.5 flex-none" />
                                @else
                                    <span class="h-4 w-4 flex-none rounded-full border-2 {{ $isCurrent ? 'border-indigo-600' : 'border-gray-300 dark:border-gray-600' }}"></span>
                                @endif
                                <span class="truncate">Level {{ $number }}</span>
                            </button>

                            <div x-show="open === {{ $number }}" x-cloak x-transition class="pb-2 pl-8 pr-2">
                                @if ($status['unlocked'])
                                    @if ($isCurrent)
                                        @foreach ($sections as $section)
                                            <p class="pt-2 pb-1 text-xs font-semibold uppercase text-gray-400">{{ $section->title }}</p>
                                            @foreach ($section->lessons as $sectionLesson)
                                                <a href="{{ route('student.tracks.learn', [$track, $level, $sectionLesson]) }}"
                                                   class="flex items-center gap-2 rounded-lg px-2 py-1.5 text-sm {{ $sectionLesson->id === $lesson->id ? 'bg-indigo-50 font-medium text-indigo-600 dark:bg-indigo-900/40 dark:text-indigo-300' : 'text-gray-600 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-700' }}">
                                                    @if ($completedLessonIds->contains($sectionLesson->id))
                                                        <span class="text-green-500">&#10003;</span>
                                                    @else
                                                        <span class="h-2 w-2 rounded-full border border-gray-300 dark:border-gray-600"></span>
                                                    @endif
                                                    <span class="truncate">{{ $sectionLesson->title }}</span>
                                                </a>
                                            @endforeach
                                            @if ($section->activity)
                                                @php
                                                    $sectionLessonsDone = $section->lessons->pluck('id')->diff($completedLessonIds)->isEmpty() && $section->lessons->isNotEmpty();
                                                @endphp
                                                @if ($sectionLessonsDone)
                                                    <a href="{{ route('student.tracks.section-activity', [$track, $level, $section]) }}"
                                                       class="flex items-center gap-2 rounded-lg px-2 py-1.5 text-sm text-gray-600 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-700">
                                                        <x-icons.medal class="h-3.5 w-3.5" />
                                                        Activity Questions
                                                    </a>
                                                @else
                                                    <span class="flex items-center gap-2 rounded-lg px-2 py-1.5 text-sm text-gray-400 dark:text-gray-500" title="Finish this section's lessons first">
                                                        <x-icons.lock class="h-3.5 w-3.5" />
                                                        Activity Questions
                                                    </span>
                                                @endif
                                            @endif
                                        @endforeach
                                    @else
                                        <a href="{{ route('student.tracks.resume', [$track, $status['level']]) }}" class="block rounded-lg px-2 py-1.5 text-sm text-indigo-600 hover:bg-gray-50 dark:text-indigo-400 dark:hover:bg-gray-700">
                                            Go to Level {{ $number }} &rarr;
                                        </a>
                                    @endif
                                @else
                                    <p class="pt-1 text-xs text-gray-400 dark:text-gray-500">Finish level {{ $number - 1 }} to unlock.</p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Main content --}}
        <div class="order-1 lg:order-2">
            <x-video-player :media="$lesson->video" />

            <h1 class="mt-4 text-xl font-bold text-gray-900 dark:text-white">{{ $lesson->title }}</h1>
            @if ($lesson->description)
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">{{ $lesson->description }}</p>
            @endif

            @if ($lesson->ebook)
                <div class="mt-4 flex items-center justify-between rounded-lg border border-gray-200 p-3 dark:border-gray-700">
                    <span class="text-sm text-gray-700 dark:text-gray-300">eBook: {{ $lesson->ebook->title }}</span>
                    @if ($lesson->ebook->file)
                        <a href="{{ $lesson->ebook->file->resolved_url }}" target="_blank" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">Download</a>
                    @endif
                </div>
            @endif

            @if ($lesson->resources->isNotEmpty())
                <div class="mt-4">
                    <p class="text-sm font-semibold text-gray-900 dark:text-white">Resources</p>
                    <ul class="mt-2 space-y-1">
                        @foreach ($lesson->resources as $resource)
                            <li>
                                <a href="{{ $resource->media->resolved_url }}" target="_blank" class="text-sm text-indigo-600 hover:text-indigo-500">{{ $resource->title }}</a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="mt-6 flex flex-wrap items-center justify-between gap-3 border-t border-gray-200 pt-4 dark:border-gray-700">
                <div class="flex gap-2">
                    @if ($previousStep)
                        <a href="{{ $previousStep['url'] }}" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-800">
                            &larr; {{ $previousStep['label'] }}
                        </a>
                    @endif
                    @if ($nextStep)
                        <a href="{{ $nextStep['url'] }}" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-800">
                            {{ $nextStep['label'] }} &rarr;
                        </a>
                    @endif
                </div>

                <form method="POST" action="{{ route('student.tracks.complete-lesson', [$track, $level, $lesson]) }}">
                    @csrf
                    <button type="submit" @disabled($isCompleted) class="rounded-md bg-indigo-600 px-5 py-2 text-sm font-semibold text-white hover:bg-indigo-500 disabled:opacity-50">
                        {{ $isCompleted ? 'Completed' : 'Mark Completed' }}
                    </button>
                </form>
            </div>
        </div>

        {{-- Up next: the track after this one --}}
        <div class="order-3">
            @if ($nextTrack)
                <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800 lg:sticky lg:top-24">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Up Next</p>

                    <div class="mt-3 aspect-video w-full overflow-hidden rounded-lg bg-gradient-to-br from-indigo-100 to-indigo-300 dark:from-indigo-900 dark:to-indigo-700">
                        @if ($nextTrack->thumbnail?->resolved_url)
                            <img src="{{ $nextTrack->thumbnail->resolved_url }}" alt="{{ $nextTrack->name }}" class="h-full w-full object-cover">
                        @else
                            <div class="flex h-full w-full items-center justify-center">
                                <span class="text-2xl font-black text-indigo-700 dark:text-indigo-200">{{ $nextTrack->track_code }}</span>
                            </div>
                        @endif
                    </div>

                    <span class="mt-3 block text-xs font-medium uppercase tracking-wide text-indigo-600 dark:text-indigo-400">Track {{ $nextTrack->track_code }}</span>
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white">{{ $nextTrack->name }}</h3>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        {{ $nextTrack->levels_count ?? $nextTrack->levels()->count() }} levels &middot; {{ $nextTrack->subscription_days }} days access
                    </p>

                    <a href="{{ route('tracks.show', $nextTrack) }}" class="mt-4 block w-full rounded-lg bg-indigo-600 px-4 py-2.5 text-center text-sm font-semibold text-white hover:bg-indigo-500">
                        View Track
                    </a>
                </div>
            @endif
        </div>
    </div>
</x-layouts.student>
