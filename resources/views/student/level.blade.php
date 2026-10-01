<x-layouts.student :title="$track->track_code.' '.$level->title">
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-[18rem_minmax(0,1fr)]">
        {{-- Sidebar: every level of the track --}}
        <div class="order-2 lg:order-1">
            <div class="rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
                <div class="border-b border-gray-200 p-4 dark:border-gray-700">
                    <p class="text-xs font-medium uppercase text-indigo-600 dark:text-indigo-400">Track {{ $track->track_code }}</p>
                    <a href="{{ route('student.tracks.show', $track) }}" class="text-sm font-semibold text-gray-900 hover:text-indigo-600 dark:text-white">
                        {{ $track->name }}
                    </a>
                </div>

                <p class="px-4 pt-3 text-xs font-semibold uppercase tracking-wide text-gray-400">Course Material</p>

                <div class="max-h-[36rem] overflow-y-auto p-2">
                    @foreach ($levelStatuses as $number => $status)
                        @php $isCurrent = $number === $level->number; @endphp
                        <a
                            href="{{ $status['unlocked'] ? route('student.tracks.level', [$track, $status['level']]) : '#' }}"
                            @unless ($status['unlocked']) aria-disabled="true" onclick="return false;" @endunless
                            class="flex items-center gap-2 rounded-lg px-2 py-2.5 text-sm font-medium {{ $isCurrent ? 'bg-indigo-50 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-300' : ($status['unlocked'] ? 'text-gray-900 hover:bg-gray-50 dark:text-white dark:hover:bg-gray-700' : 'cursor-not-allowed text-gray-400 dark:text-gray-500') }}"
                        >
                            @if ($status['completed'])
                                <span class="flex h-4 w-4 flex-none items-center justify-center rounded-full bg-green-500 text-[10px] text-white">&#10003;</span>
                            @elseif (! $status['unlocked'])
                                <x-icons.lock class="h-3.5 w-3.5 flex-none" />
                            @else
                                <span class="h-4 w-4 flex-none rounded-full border-2 {{ $isCurrent ? 'border-indigo-600' : 'border-gray-300 dark:border-gray-600' }}"></span>
                            @endif
                            <span class="truncate">Level {{ $number }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Main content: media + the 4 sections stacked top to bottom --}}
        <div class="order-1 lg:order-2">
            <a href="{{ route('student.tracks.show', $track) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">
                &larr; Back to Track {{ $track->track_code }}
            </a>

            <h1 class="mt-3 text-2xl font-bold text-gray-900 dark:text-white">{{ $level->title }}</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ $track->track_code }} &middot; {{ $track->name }}</p>

            <div class="mt-6 space-y-5">
                @foreach ($level->sections as $section)
                    @php
                        $firstLesson = $section->lessons->first();
                        $lessonsDone = $section->lessons->pluck('id')->diff($completedLessonIds)->isEmpty() && $section->lessons->isNotEmpty();
                    @endphp
                    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
                        @if ($firstLesson?->video)
                            <a href="{{ route('student.tracks.learn', [$track, $level, $firstLesson]) }}" class="group relative flex aspect-video w-full items-center justify-center overflow-hidden bg-gradient-to-br from-gray-800 to-gray-900">
                                <x-icons.play-circle class="h-12 w-12 text-white/90 transition group-hover:text-white" />
                            </a>
                        @endif

                        <div class="p-5">
                            <h2 class="font-semibold text-gray-900 dark:text-white">{{ $section->title }}</h2>

                            <ul class="mt-3 space-y-1">
                                @forelse ($section->lessons as $lesson)
                                    <li>
                                        <a href="{{ route('student.tracks.learn', [$track, $level, $lesson]) }}"
                                           class="flex items-center gap-2 rounded-lg px-2 py-2 text-sm text-gray-600 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-700">
                                            @if ($completedLessonIds->contains($lesson->id))
                                                <span class="text-green-500">&#10003;</span>
                                            @else
                                                <span class="h-2 w-2 rounded-full border border-gray-300 dark:border-gray-600"></span>
                                            @endif
                                            {{ $lesson->title }}
                                        </a>
                                    </li>
                                @empty
                                    <li class="px-2 py-2 text-sm text-gray-400">No lessons yet.</li>
                                @endforelse

                                @if ($section->activity)
                                    <li class="mt-1 border-t border-gray-100 pt-1 dark:border-gray-700">
                                        @if ($lessonsDone)
                                            <a href="{{ route('student.tracks.section-activity', [$track, $level, $section]) }}"
                                               class="flex items-center gap-2 rounded-lg px-2 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-gray-700">
                                                @if ($checkedAssessmentIds->contains($section->activity->id))
                                                    <span class="text-green-500">&#10003;</span>
                                                @else
                                                    <x-icons.medal class="h-3.5 w-3.5 text-indigo-500" />
                                                @endif
                                                Activity Questions
                                            </a>
                                        @else
                                            <span class="flex items-center gap-2 rounded-lg px-2 py-2 text-sm text-gray-400 dark:text-gray-500" title="Finish this section's lessons first">
                                                <x-icons.lock class="h-3.5 w-3.5" />
                                                Activity Questions
                                            </span>
                                        @endif
                                    </li>
                                @endif
                            </ul>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-layouts.student>
