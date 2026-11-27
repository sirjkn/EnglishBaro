<x-layouts.student :title="$track->track_code.' '.$level->title">
    <div
        x-data="{
            openLevel: {{ $level->number }},
            openLesson: new URLSearchParams(location.search).get('open_lesson')
                ? Number(new URLSearchParams(location.search).get('open_lesson'))
                : {{ $resumeNode?->type === 'lesson' && $resumeNode->section->level_id === $level->id ? $resumeNode->lesson->id : 'null' }},
            search: '', changeLevelOpen: false, selTrack: '{{ $track->track_code }}', selLevel: {{ $level->number }},
            advanceTo(target) {
                if (! target) { window.location.reload(); return; }

                // The lesson a student is advancing into may not have been
                // unlocked yet when this page was rendered, in which case its
                // content was never sent to the browser at all — reload with
                // the target in the URL so the server renders it unlocked and
                // this same script reopens it straight away.
                const lessonEl = document.querySelector('[data-lesson-id=\'' + target.lessonId + '\']');

                if (! lessonEl) {
                    window.location.href = window.location.pathname + '?open_lesson=' + target.lessonId;
                    return;
                }

                this.openLesson = target.lessonId;
                this.$nextTick(() => lessonEl.scrollIntoView({ behavior: 'smooth', block: 'start' }));
            },
        }"
        @lesson-advance.window="advanceTo($event.detail)"
        class="max-w-4xl"
    >
        @if ($subscription)
            @php
                $daysLeft = max(0, now()->startOfDay()->diffInDays($subscription->expires_at->copy()->startOfDay(), false));
            @endphp
            <div class="mb-4 flex flex-wrap items-center justify-between gap-3 rounded-xl bg-amber-50 px-4 py-3 text-sm dark:bg-amber-900/20">
                <div class="flex items-center gap-2 text-amber-800 dark:text-amber-300">
                    <x-icons.clock class="h-4 w-4 flex-none" />
                    <span><span class="font-bold">{{ $daysLeft }} days left</span> &middot; Your access ends on {{ $subscription->expires_at->format('j F Y') }}.</span>
                </div>
                <a href="{{ route('student.payments') }}" class="flex-none rounded-full bg-amber-900 px-4 py-1.5 text-xs font-semibold text-white hover:bg-amber-800">Renew</a>
            </div>
        @endif

        <p class="text-xs font-bold uppercase tracking-wide text-gray-400">My Course</p>
        <div class="mt-1 flex flex-wrap items-center justify-between gap-3">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $track->track_code }} &mdash; Level {{ $level->number }}</h1>
            <button type="button" @click="changeLevelOpen = true" class="flex-none rounded-md border border-gray-300 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">
                Change level
            </button>
        </div>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $completedLevelsCount }} of {{ $allLevels->count() }} levels complete on this track.</p>

        <div class="mt-5 grid grid-cols-1 gap-4 lg:grid-cols-[minmax(0,1fr)_16rem]">
            <div class="rounded-xl border-2 border-indigo-300 bg-white p-5 dark:border-indigo-700 dark:bg-gray-800">
                <p class="text-xs font-bold uppercase tracking-wide text-gray-400">Continue Learning</p>
                <div class="mt-3 flex items-center gap-3">
                    <span class="flex h-11 w-11 flex-none items-center justify-center rounded-lg bg-indigo-600 text-sm font-bold text-white">{{ $track->track_code }}</span>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-bold text-gray-900 dark:text-white">{{ $resumeNode->section->title ?? 'Grammar' }} &mdash; Level {{ $level->number }}</p>
                        <p class="truncate text-xs text-gray-500 dark:text-gray-400">{{ $resumeNode->lesson->title ?? $level->title }}</p>
                    </div>
                    @if ($resumeStep)
                        <a href="{{ $resumeStep['url'] }}" class="flex flex-none items-center gap-1.5 rounded-lg bg-indigo-600 px-4 py-2 text-xs font-semibold text-white hover:bg-indigo-500">
                            {{ $hasStartedTrack ? 'Continue' : 'Start' }}
                            <x-icons.chevron-right class="h-3.5 w-3.5" />
                        </a>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3 lg:grid-cols-1">
                <div class="rounded-xl border-2 border-indigo-300 bg-white p-4 dark:border-indigo-700 dark:bg-gray-800">
                    <p class="text-xl font-bold text-gray-900 dark:text-white">{{ $completedLevelsCount }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Levels completed</p>
                </div>
                <div class="rounded-xl border-2 border-indigo-300 bg-white p-4 dark:border-indigo-700 dark:bg-gray-800">
                    <p class="text-xl font-bold text-gray-900 dark:text-white">{{ $allLevels->count() - $completedLevelsCount }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Levels still to go</p>
                </div>
            </div>
        </div>

        <div id="level-list" class="mt-6 scroll-mt-20">
            <div class="flex flex-wrap items-center gap-3">
                <div class="relative min-w-0 flex-1">
                    <input
                        type="search"
                        x-model="search"
                        placeholder="Search levels"
                        class="w-full rounded-full border-gray-300 py-2 px-4 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                    >
                </div>
            </div>

            <div class="mt-4 divide-y divide-gray-100 overflow-hidden rounded-xl border-2 border-indigo-300 bg-white dark:divide-gray-700 dark:border-indigo-700 dark:bg-gray-800">
                @foreach ($allLevels as $levelItem)
                    @php
                        $status = $levelStatuses[$levelItem->number];
                        $isUnlocked = $status['unlocked'];
                        $isCompleted = $status['completed'];
                        $isCurrent = $levelItem->id === $level->id;
                    @endphp

                    <div
                        id="level-{{ $levelItem->number }}"
                        x-show="search === '' || {{ \Illuminate\Support\Js::from(strtolower($levelItem->number.' '.$levelItem->title)) }}.includes(search.toLowerCase())"
                        class="scroll-mt-20 {{ $isCurrent ? 'bg-indigo-50/60 dark:bg-indigo-900/20' : '' }}"
                    >
                        @php
                            $levelHasStarted = $levelItem->sections->flatMap->lessons->contains(fn ($l) => $startedLessonIds->contains($l->id));
                        @endphp
                        @if ($isCurrent)
                            {{-- The current level is always expanded, with no
                            collapse control and no Start/Continue badge —
                            it's already where the student is. --}}
                            <div class="flex w-full items-center gap-3 px-4 py-3 text-left">
                                <span class="min-w-0 flex-1">
                                    <span class="flex items-center gap-2">
                                        <span class="truncate text-sm font-semibold text-gray-900 dark:text-white">{{ $levelItem->title }}</span>
                                        @if ($levelItem->number === 1)
                                            <span class="flex-none rounded-full bg-gray-100 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-gray-500 dark:bg-gray-700 dark:text-gray-300">Starting Point</span>
                                        @endif
                                    </span>
                                    <span class="block truncate text-xs text-gray-500 dark:text-gray-400">
                                        {{ $levelItem->sections->pluck('title')->implode(' · ') }}
                                    </span>
                                </span>
                            </div>
                        @else
                            <button
                                type="button"
                                @click="openLevel = (openLevel === {{ $levelItem->number }} ? null : {{ $levelItem->number }})"
                                class="flex w-full items-center gap-3 px-4 py-3 text-left"
                            >
                                @if ($isCompleted)
                                    <span class="flex h-6 w-6 flex-none items-center justify-center rounded-full bg-green-500 text-xs text-white">&#10003;</span>
                                @elseif (! $isUnlocked)
                                    <span class="flex h-6 w-6 flex-none items-center justify-center rounded-full bg-gray-100 text-gray-400 dark:bg-gray-700 dark:text-gray-500">
                                        <x-icons.lock class="h-3.5 w-3.5" />
                                    </span>
                                @endif

                                <span class="min-w-0 flex-1">
                                    <span class="flex items-center gap-2">
                                        <span class="truncate text-sm font-semibold text-gray-900 dark:text-white">{{ $levelItem->title }}</span>
                                        @if ($levelItem->number === 1)
                                            <span class="flex-none rounded-full bg-gray-100 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-gray-500 dark:bg-gray-700 dark:text-gray-300">Starting Point</span>
                                        @endif
                                    </span>
                                    <span class="block truncate text-xs text-gray-500 dark:text-gray-400">
                                        {{ $levelItem->sections->pluck('title')->implode(' · ') }}
                                    </span>
                                </span>

                                @if ($isCompleted)
                                    <span class="flex-none rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700 dark:bg-green-900/40 dark:text-green-300">Complete</span>
                                @elseif (! $isUnlocked)
                                    <span class="flex-none rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-500 dark:bg-gray-700 dark:text-gray-400">Locked</span>
                                @else
                                    <span class="flex-none rounded-full bg-indigo-100 px-3 py-1 text-xs font-semibold text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300">{{ $levelHasStarted ? 'Continue' : 'Start' }}</span>
                                @endif

                                <x-icons.chevron-left class="h-4 w-4 flex-none text-gray-400 transition-transform duration-200" x-bind:class="openLevel === {{ $levelItem->number }} ? '-rotate-90' : 'rotate-90'" />
                            </button>
                        @endif

                        <div x-show="{{ $isCurrent ? 'true' : 'openLevel === '.$levelItem->number }}" x-cloak x-transition class="border-t border-gray-100 bg-gray-50 px-4 py-3 dark:border-gray-700 dark:bg-gray-900/40">
                            @unless ($isUnlocked)
                                <p class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                                    <x-icons.lock class="h-3.5 w-3.5" />
                                    Preview only &mdash; complete level {{ $levelItem->number - 1 }} to watch and read this material.
                                </p>
                            @else
                                @php
                                    $priorDone = true;
                                    // The order a student walks through this level's lessons in,
                                    // used to work out what to open next once one is finished.
                                    $flatLessonSteps = $levelItem->sections->flatMap(
                                        fn ($s) => $s->lessons->map(fn ($l) => ['sectionId' => $s->id, 'lessonId' => $l->id])
                                    )->values();
                                @endphp
                                <div class="divide-y divide-gray-100 dark:divide-gray-700">
                                    @foreach ($levelItem->sections as $section)
                                        @foreach ($section->lessons as $subIndex => $lesson)
                                            @php
                                                $lessonIsDone = $completedLessonIds->contains($lesson->id);
                                                $lessonIsLocked = ! $priorDone;
                                                $priorDone = $priorDone && $lessonIsDone;
                                                $lessonIsResume = $resumeNode?->type === 'lesson' && $resumeNode->lesson->id === $lesson->id;
                                                $lessonHasStarted = $startedLessonIds->contains($lesson->id);
                                                $lessonQuestionCount = $lesson->activity?->questions->count() ?? 0;
                                                $lessonLabel = $section->title.' '.$levelItem->number.'.'.($subIndex + 1);
                                                $lessonStepIndex = $flatLessonSteps->search(fn ($s) => $s['lessonId'] === $lesson->id);
                                                $lessonAdvanceTarget = $flatLessonSteps->get($lessonStepIndex + 1);
                                            @endphp
                                            <div class="{{ $lessonIsResume ? 'bg-indigo-50 dark:bg-indigo-900/30' : '' }} {{ $lessonIsLocked ? 'opacity-60' : '' }}">
                                                <button
                                                    type="button"
                                                    @if (! $lessonIsLocked) @click="openLesson = (openLesson === {{ $lesson->id }} ? null : {{ $lesson->id }})" @endif
                                                    @if ($lessonIsLocked) disabled @endif
                                                    class="flex w-full items-center gap-3 px-2 py-2.5 text-left"
                                                >
                                                    @if ($lessonIsDone)
                                                        <span class="flex h-5 w-5 flex-none items-center justify-center rounded-full bg-green-100 text-[10px] text-green-600 dark:bg-green-900/40 dark:text-green-300">&#10003;</span>
                                                    @elseif ($lessonIsLocked)
                                                        <span class="flex h-5 w-5 flex-none items-center justify-center rounded-full bg-gray-100 text-gray-400 dark:bg-gray-700 dark:text-gray-500">
                                                            <x-icons.lock class="h-2.5 w-2.5" />
                                                        </span>
                                                    @else
                                                        <span class="flex h-5 w-5 flex-none items-center justify-center rounded-full border-2 border-gray-300 dark:border-gray-600"></span>
                                                    @endif

                                                    <span class="min-w-0 flex-1">
                                                        <span class="block truncate text-xs font-semibold text-gray-900 dark:text-white">{{ $lessonLabel }}</span>
                                                        @if (! $lessonIsLocked && $lessonQuestionCount)
                                                            <span class="block truncate text-[11px] text-gray-500 dark:text-gray-400">{{ $lessonQuestionCount }} question{{ $lessonQuestionCount === 1 ? '' : 's' }}</span>
                                                        @endif
                                                    </span>

                                                    @if ($lessonIsDone)
                                                        <span class="flex-none rounded-full bg-green-100 px-3 py-1 text-[11px] font-semibold text-green-700 dark:bg-green-900/40 dark:text-green-300">Done</span>
                                                    @elseif ($lessonIsLocked)
                                                        <span class="flex-none rounded-full bg-gray-100 px-3 py-1 text-[11px] font-semibold text-gray-400 dark:bg-gray-700 dark:text-gray-500">Locked</span>
                                                    @else
                                                        <span class="flex-none rounded-full bg-indigo-600 px-3 py-1 text-[11px] font-semibold text-white">{{ $lessonHasStarted ? 'Continue' : 'Start' }}</span>
                                                    @endif

                                                    @unless ($lessonIsLocked)
                                                        <x-icons.chevron-left class="h-3.5 w-3.5 flex-none text-gray-400 transition-transform duration-200" x-bind:class="openLesson === {{ $lesson->id }} ? '-rotate-90' : 'rotate-90'" />
                                                    @endunless
                                                </button>

                                                @unless ($lessonIsLocked)
                                                    <div data-lesson-id="{{ $lesson->id }}" x-show="openLesson === {{ $lesson->id }}" x-cloak x-transition class="scroll-mt-24 bg-gray-50 px-2 pb-3 pl-10 dark:bg-gray-900/40">
                                                        @include('student.partials.lesson-preview', [
                                                            'lesson' => $lesson,
                                                            'track' => $track,
                                                            'level' => $levelItem,
                                                            'lessonIsDone' => $lessonIsDone,
                                                            'lessonHasStarted' => $lessonHasStarted,
                                                            'resumeSeconds' => $lessonProgressByLessonId->get($lesson->id)?->video_progress_seconds ?? 0,
                                                            'assessment' => $lesson->activity,
                                                            'attempt' => $lesson->activity ? $latestAttemptByAssessmentId->get($lesson->activity->id) : null,
                                                            'checked' => $lesson->activity ? $checkedAssessmentIds->contains($lesson->activity->id) : false,
                                                            'attemptsUsed' => $lesson->activity ? ($attemptsUsedByAssessmentId->get($lesson->activity->id) ?? 0) : 0,
                                                            'maxAttempts' => \App\Services\AssessmentGradingService::MAX_ATTEMPTS,
                                                            'advanceTarget' => $lessonAdvanceTarget,
                                                        ])
                                                    </div>
                                                @endunless
                                            </div>
                                        @endforeach
                                    @endforeach
                                </div>
                            @endunless
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div x-show="changeLevelOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="background: rgba(0,0,0,.4)" @click.self="changeLevelOpen = false">
            <div x-show="changeLevelOpen" x-transition class="max-h-[95vh] w-full max-w-xl overflow-y-auto rounded-xl bg-white p-5 shadow-xl dark:bg-gray-800">
                <h2 class="text-base font-bold text-gray-900 dark:text-white">Change your level</h2>
                <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Choose where to start inside a track that's open to you.</p>

                <div class="mt-3 rounded-lg border border-indigo-200 bg-indigo-50 p-3 text-xs leading-snug text-indigo-900 dark:border-indigo-800 dark:bg-indigo-900/20 dark:text-indigo-200">
                    <p class="font-semibold">Switch freely</p>
                    <p class="mt-1">
                        Your payment covers every track in this course ({{ $pickerTracks->pluck('track_code')->implode(' → ') }}), so you can jump into any of them and start at any level.
                    </p>
                </div>

                <p class="mt-3 text-xs font-bold uppercase tracking-wide text-gray-400">Track</p>
                <div class="mt-1.5 flex flex-wrap items-center gap-1.5">
                    @foreach ($pickerTracks as $pickerTrack)
                        <button type="button" @click="selTrack = '{{ $pickerTrack->track_code }}'"
                                class="rounded-md border px-2.5 py-1 text-xs"
                                :class="selTrack === '{{ $pickerTrack->track_code }}' ? 'border-indigo-500 bg-indigo-50 font-bold text-indigo-700 dark:border-indigo-400 dark:bg-indigo-900/30 dark:text-indigo-200' : 'border-gray-300 font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700'">
                            {{ $pickerTrack->track_code }}
                        </button>
                    @endforeach
                </div>

                <p class="mt-3 text-xs font-bold uppercase tracking-wide text-gray-400">Starting level</p>
                <div class="mt-1.5 grid grid-cols-6 gap-1.5">
                    @foreach ([1, 10, 20, 30, 40, 50, 60, 70, 80, 90, 100] as $checkpoint)
                        <button type="button" @click="selLevel = {{ $checkpoint }}"
                                class="rounded-md border px-1.5 py-1 text-xs font-semibold"
                                :class="selLevel === {{ $checkpoint }} ? 'border-indigo-500 bg-indigo-50 text-indigo-700 dark:border-indigo-400 dark:bg-indigo-900/30 dark:text-indigo-200' : 'border-gray-200 text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700'">
                            {{ $checkpoint }}
                        </button>
                    @endforeach
                </div>

                <div class="mt-3 rounded-md bg-amber-50 px-3 py-2 text-xs text-amber-800 dark:bg-amber-900/20 dark:text-amber-300">
                    Your finished work is kept. If you come back to a level later, your progress is still there.
                </div>

                <div class="mt-3 flex items-center justify-end gap-2">
                    <button type="button" @click="changeLevelOpen = false" class="rounded-md border border-gray-300 px-3.5 py-1.5 text-xs font-semibold text-gray-600 hover:bg-gray-100 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">Cancel</button>
                    <form method="POST" action="{{ route('student.change-level') }}">
                        @csrf
                        <input type="hidden" name="track_code" :value="selTrack">
                        <input type="hidden" name="level_number" :value="selLevel">
                        <button type="submit" class="rounded-md bg-teal-700 px-3.5 py-1.5 text-xs font-semibold text-white hover:bg-teal-600">
                            Move to <span x-text="selTrack"></span> Level <span x-text="selLevel"></span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const openLessonId = new URLSearchParams(location.search).get('open_lesson');
                const lessonEl = openLessonId && document.querySelector('[data-lesson-id="' + openLessonId + '"]');
                (lessonEl || document.getElementById('level-{{ $level->number }}'))?.scrollIntoView({ block: 'start' });
            });
        </script>
    @endpush
</x-layouts.student>
