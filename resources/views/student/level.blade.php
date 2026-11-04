<x-layouts.student :title="$track->track_code.' '.$level->title">
    <div
        x-data="{ openLevel: {{ $level->number }} }"
        class="max-w-3xl"
    >
        {{-- Main content --}}
        <div>
            <a href="{{ route('student.tracks.show', $track) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">
                &larr; Back to Track {{ $track->track_code }}
            </a>

            {{-- Course outline / highlight card --}}
            <div class="relative mt-3 overflow-hidden rounded-2xl bg-gradient-to-br from-indigo-50 to-blue-100 p-6 dark:from-indigo-950 dark:to-gray-800">
                <div class="flex items-start gap-3">
                    <div class="flex h-10 w-10 flex-none items-center justify-center rounded-xl bg-white shadow-sm dark:bg-gray-900">
                        <x-icons.book-open class="h-5 w-5 text-indigo-600 dark:text-indigo-400" />
                    </div>

                    <div class="min-w-0">
                        <h1 class="text-base font-bold text-gray-900 dark:text-white sm:text-lg">
                            {{ $track->track_code }}, {{ collect(\App\Models\Section::TYPES)->map(fn ($type) => \App\Models\Section::titleFor($type))->implode(', ') }}
                        </h1>
                        <p class="text-xs text-gray-600 dark:text-gray-300">{{ $track->name }} &middot; {{ $allLevels->count() }} levels</p>
                    </div>
                </div>

                <div class="mt-4 flex flex-wrap gap-5">
                    <div class="flex items-center gap-2">
                        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-indigo-600 text-white">
                            <x-icons.play-circle class="h-3.5 w-3.5" />
                        </span>
                        <div>
                            <p class="text-xs font-semibold text-gray-900 dark:text-white">{{ $stats['videoCount'] }}</p>
                            <p class="text-[11px] text-gray-500 dark:text-gray-400">Number of videos</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-indigo-600 text-white">
                            <x-icons.clock class="h-3.5 w-3.5" />
                        </span>
                        <div>
                            <p class="text-xs font-semibold text-gray-900 dark:text-white">{{ $stats['timeLeft'] }}</p>
                            <p class="text-[11px] text-gray-500 dark:text-gray-400">Time left for the course</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-indigo-600 text-white">
                            <x-icons.clipboard-check class="h-3.5 w-3.5" />
                        </span>
                        <div>
                            <p class="text-xs font-semibold text-gray-900 dark:text-white">{{ $stats['examsDone'] }}/{{ $stats['examsTotal'] }}</p>
                            <p class="text-[11px] text-gray-500 dark:text-gray-400">Exams done</p>
                        </div>
                    </div>
                </div>

                @if ($track->description)
                    <p class="mt-4 max-w-3xl text-xs text-gray-700 dark:text-gray-300">
                        {{ $track->description }}
                    </p>
                @endif
            </div>

            {{-- Levels 1-100, stacked downward; scroll to progress --}}
            <div class="mt-6 space-y-3">
                @foreach ($allLevels as $levelItem)
                    @continue ($levelItem->id !== $level->id)
                    @php
                        $status = $levelStatuses[$levelItem->number];
                        $isUnlocked = $status['unlocked'];
                        $isCompleted = $status['completed'];
                        $isCurrent = $levelItem->id === $level->id;
                    @endphp

                    <div
                        id="level-{{ $levelItem->number }}"
                        class="scroll-mt-20 overflow-hidden rounded-xl border bg-white dark:bg-gray-800 {{ $isCurrent ? 'border-indigo-300 ring-1 ring-indigo-200 dark:border-indigo-700 dark:ring-indigo-900' : 'border-gray-200 dark:border-gray-700' }}"
                    >
                        <button
                            type="button"
                            @click="openLevel = (openLevel === {{ $levelItem->number }} ? null : {{ $levelItem->number }})"
                            class="flex w-full items-center gap-3 px-5 py-4 text-left"
                        >
                            @if ($isCompleted)
                                <span class="flex h-8 w-8 flex-none items-center justify-center rounded-full bg-green-500 text-sm text-white">&#10003;</span>
                            @elseif (! $isUnlocked)
                                <span class="flex h-8 w-8 flex-none items-center justify-center rounded-full bg-gray-100 text-gray-400 dark:bg-gray-700 dark:text-gray-500">
                                    <x-icons.lock class="h-4 w-4" />
                                </span>
                            @else
                                <span class="flex h-8 w-8 flex-none items-center justify-center rounded-full border-2 {{ $isCurrent ? 'border-indigo-600 text-indigo-600' : 'border-gray-300 text-gray-400 dark:border-gray-600' }} text-xs font-semibold">
                                    {{ $levelItem->number }}
                                </span>
                            @endif

                            <span class="min-w-0 flex-1">
                                <span class="block truncate text-sm font-semibold text-gray-900 dark:text-white">Level {{ $levelItem->number }}: {{ $levelItem->title }}</span>
                                <span class="block text-xs text-gray-500 dark:text-gray-400">
                                    @if (! $isUnlocked)
                                        Preview only &middot; finish level {{ $levelItem->number - 1 }} to unlock
                                    @else
                                        {{ $levelItem->sections->pluck('title')->implode(' &middot; ') }}
                                    @endif
                                </span>
                            </span>

                            <x-icons.chevron-left class="h-4 w-4 flex-none text-gray-400 transition-transform duration-200" x-bind:class="openLevel === {{ $levelItem->number }} ? '-rotate-90' : 'rotate-90'" />
                        </button>

                        <div x-show="openLevel === {{ $levelItem->number }}" x-cloak x-transition class="border-t border-gray-200 dark:border-gray-700">
                            @unless ($isUnlocked)
                                <p class="flex items-center gap-2 bg-gray-50 px-5 py-2.5 text-xs text-gray-500 dark:bg-gray-900/40 dark:text-gray-400">
                                    <x-icons.lock class="h-3.5 w-3.5" />
                                    Preview only &mdash; complete level {{ $levelItem->number - 1 }} to watch and read this material.
                                </p>
                            @endunless

                            <div x-data="{ openSection: {{ optional($resumeNode?->section ?? $levelItem->sections->first())->id }} }">
                            @foreach ($levelItem->sections as $section)
                                <div class="border-b border-gray-100 px-5 py-3 last:border-0 dark:border-gray-700">
                                    <button
                                        type="button"
                                        @click="openSection = (openSection === {{ $section->id }} ? null : {{ $section->id }})"
                                        class="flex w-full items-center justify-between pb-2 text-left"
                                    >
                                        <span class="text-xs font-semibold uppercase tracking-wide text-indigo-600 dark:text-indigo-400">{{ $section->title }}</span>
                                        <x-icons.chevron-left class="h-3.5 w-3.5 flex-none text-gray-400 transition-transform duration-200" x-bind:class="openSection === {{ $section->id }} ? '-rotate-90' : 'rotate-90'" />
                                    </button>

                                    <div x-show="openSection === {{ $section->id }}" x-cloak x-transition class="space-y-1">
                                        @foreach ($section->lessons as $lesson)
                                            @php
                                                $isResume = $isUnlocked && $resumeNode?->type === 'lesson' && $resumeNode->lesson->id === $lesson->id;
                                                $isDone = $completedLessonIds->contains($lesson->id);
                                                $minutes = $lesson->duration_seconds ? max(1, (int) round($lesson->duration_seconds / 60)) : null;
                                            @endphp
                                            <a
                                                @if ($isUnlocked)
                                                    href="{{ route('student.tracks.learn', [$track, $levelItem, $lesson]) }}"
                                                @else
                                                    href="#" aria-disabled="true" onclick="return false;"
                                                @endif
                                                class="flex items-center gap-3 rounded-lg px-2 py-2.5 {{ $isUnlocked ? ($isResume ? 'bg-indigo-50 dark:bg-indigo-900/30' : 'hover:bg-gray-50 dark:hover:bg-gray-700') : 'cursor-not-allowed opacity-60' }}"
                                            >
                                                <span class="flex h-9 w-9 flex-none items-center justify-center rounded-lg bg-indigo-100 text-indigo-600 dark:bg-indigo-900/40 dark:text-indigo-300">
                                                    <x-icons.play-circle class="h-4 w-4" />
                                                </span>

                                                <span class="min-w-0 flex-1">
                                                    <span class="block truncate text-sm font-medium text-gray-900 dark:text-white">{{ $lesson->title }}</span>
                                                    <span class="block text-xs text-gray-500 dark:text-gray-400">Video @if ($minutes) &middot; {{ $minutes }} min @endif</span>
                                                </span>

                                                @if (! $isUnlocked)
                                                    <x-icons.lock class="h-4 w-4 flex-none text-gray-300 dark:text-gray-600" />
                                                @elseif ($isResume)
                                                    <span class="flex flex-none items-center gap-1.5 rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white">
                                                        <x-icons.play-circle class="h-3.5 w-3.5" />
                                                        {{ $hasStartedTrack ? 'Resume' : 'Start' }}
                                                    </span>
                                                @elseif ($isDone)
                                                    <span class="flex h-5 w-5 flex-none items-center justify-center rounded-full bg-green-500 text-[10px] text-white">&#10003;</span>
                                                @else
                                                    <x-icons.chevron-right class="h-4 w-4 flex-none text-gray-300 dark:text-gray-600" />
                                                @endif
                                            </a>

                                            @if ($lesson->activity)
                                                @php
                                                    $assessment = $lesson->activity;
                                                    $activityIsResume = $isUnlocked && $resumeNode?->type === 'activity' && $resumeNode->assessment->id === $assessment->id;
                                                    $activityIsDone = $checkedAssessmentIds->contains($assessment->id);
                                                    $lessonDone = $isUnlocked && $isDone;
                                                @endphp
                                                <a
                                                    @if ($lessonDone)
                                                        href="{{ route('student.tracks.lesson-activity', [$track, $levelItem, $lesson]) }}"
                                                    @else
                                                        href="#" aria-disabled="true" onclick="return false;"
                                                    @endif
                                                    class="ml-4 flex items-center gap-3 rounded-lg px-2 py-2.5 {{ $activityIsResume ? 'bg-indigo-50 dark:bg-indigo-900/30' : ($lessonDone ? 'hover:bg-gray-50 dark:hover:bg-gray-700' : 'cursor-not-allowed opacity-60') }}"
                                                >
                                                    <span class="flex h-9 w-9 flex-none items-center justify-center rounded-lg bg-emerald-100 text-emerald-600 dark:bg-emerald-900/40 dark:text-emerald-300">
                                                        <x-icons.clipboard-check class="h-4 w-4" />
                                                    </span>

                                                    <span class="min-w-0 flex-1">
                                                        <span class="block truncate text-sm font-medium text-gray-900 dark:text-white">{{ $assessment->title }}</span>
                                                        <span class="block text-xs text-gray-500 dark:text-gray-400">Exercise @if ($assessment->time_limit_minutes) &middot; {{ $assessment->time_limit_minutes }} min @endif</span>
                                                    </span>

                                                    @if (! $lessonDone)
                                                        <x-icons.lock class="h-4 w-4 flex-none text-gray-300 dark:text-gray-600" />
                                                    @elseif ($activityIsResume)
                                                        <span class="flex flex-none items-center gap-1.5 rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white">
                                                            <x-icons.play-circle class="h-3.5 w-3.5" />
                                                            {{ $hasStartedTrack ? 'Resume' : 'Start' }}
                                                        </span>
                                                    @elseif ($activityIsDone)
                                                        <span class="flex h-5 w-5 flex-none items-center justify-center rounded-full bg-green-500 text-[10px] text-white">&#10003;</span>
                                                    @else
                                                        <x-icons.chevron-right class="h-4 w-4 flex-none text-gray-300 dark:text-gray-600" />
                                                    @endif
                                                </a>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                document.getElementById('level-{{ $level->number }}')?.scrollIntoView({ block: 'start' });
            });
        </script>
    @endpush
</x-layouts.student>
