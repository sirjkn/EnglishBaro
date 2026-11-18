<x-layouts.student :title="$track->track_code.' '.$level->title">
    <div
        x-data="{
            openLevel: {{ $level->number }},
            openSection: new URLSearchParams(location.search).get('open_section') ? Number(new URLSearchParams(location.search).get('open_section')) : null,
            openLesson: new URLSearchParams(location.search).get('open_lesson') ? Number(new URLSearchParams(location.search).get('open_lesson')) : null,
            search: '', changeLevelOpen: false, selTrack: '{{ $track->track_code }}', selLevel: {{ $level->number }},
            advanceTo(target) {
                if (! target) { window.location.reload(); return; }

                // The section a student is advancing into may not have been
                // unlocked yet when this page was rendered, in which case its
                // content was never sent to the browser at all — reload with
                // the target in the URL so the server renders it unlocked and
                // this same script reopens it straight away.
                const section = document.querySelector('[data-section-id=\'' + target.sectionId + '\']');
                const sectionShowsLessonsInline = section && section.querySelector('[data-lesson-id]');
                const lessonEl = section && section.querySelector('[data-lesson-id=\'' + target.lessonId + '\']');

                // Same for the next lesson inside a multi-lesson section: it was
                // locked at render time, so its video isn't on the page yet.
                if (! section || (sectionShowsLessonsInline && ! lessonEl)) {
                    window.location.href = window.location.pathname + '?open_section=' + target.sectionId + '&open_lesson=' + target.lessonId;
                    return;
                }

                this.openSection = target.sectionId;
                this.openLesson = target.lessonId;
                if (lessonEl) this.$nextTick(() => lessonEl.scrollIntoView({ behavior: 'smooth', block: 'start' }));
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

                            @php
                                $levelHasStarted = $levelItem->sections->flatMap->lessons->contains(fn ($l) => $startedLessonIds->contains($l->id));
                            @endphp
                            @if ($isCompleted)
                                <span class="flex-none rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700 dark:bg-green-900/40 dark:text-green-300">Complete</span>
                            @elseif (! $isUnlocked)
                                <span class="flex-none rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-500 dark:bg-gray-700 dark:text-gray-400">Locked</span>
                            @else
                                <span class="flex-none rounded-full bg-indigo-100 px-3 py-1 text-xs font-semibold text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300">{{ $levelHasStarted ? 'Continue' : 'Start' }}</span>
                            @endif

                            <x-icons.chevron-left class="h-4 w-4 flex-none text-gray-400 transition-transform duration-200" x-bind:class="openLevel === {{ $levelItem->number }} ? '-rotate-90' : 'rotate-90'" />
                        </button>

                        <div x-show="openLevel === {{ $levelItem->number }}" x-cloak x-transition class="border-t border-gray-100 bg-gray-50 px-4 py-3 dark:border-gray-700 dark:bg-gray-900/40">
                            @unless ($isUnlocked)
                                <p class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                                    <x-icons.lock class="h-3.5 w-3.5" />
                                    Preview only &mdash; complete level {{ $levelItem->number - 1 }} to watch and read this material.
                                </p>
                            @else
                                <div class="divide-y divide-gray-100 dark:divide-gray-700">
                                    @php
                                        $sectionNumber = 0;
                                        $priorDone = true;
                                        // The order a student walks through this level's lessons in,
                                        // used to work out what to open next once one is finished.
                                        $flatLessonSteps = $levelItem->sections->flatMap(
                                            fn ($s) => $s->lessons->map(fn ($l) => ['sectionId' => $s->id, 'lessonId' => $l->id])
                                        )->values();
                                    @endphp
                                    @foreach ($levelItem->sections as $section)
                                        @php
                                            $sectionNumber++;
                                            $sectionLessons = $section->lessons;
                                            $doneCount = $sectionLessons->filter(fn ($l) => $completedLessonIds->contains($l->id))->count();
                                            $isDone = $sectionLessons->isNotEmpty() && $doneCount === $sectionLessons->count();
                                            $isLocked = ! $priorDone;
                                            $priorDone = $priorDone && $isDone;
                                            $isResume = $sectionLessons->contains(fn ($l) => $resumeNode?->type === 'lesson' && $resumeNode->lesson->id === $l->id);
                                            $nextLesson = $sectionLessons->first(fn ($l) => ! $completedLessonIds->contains($l->id)) ?? $sectionLessons->first();
                                            $questionCount = $sectionLessons->sum(fn ($l) => $l->activity?->questions->count() ?? 0);
                                            $hasWrittenAnswer = $sectionLessons->contains(fn ($l) => $l->activity?->questions->contains(fn ($q) => in_array($q->type, ['short_answer', 'fill_blank'], true)) ?? false);
                                        @endphp
                                        <div class="{{ $isResume ? 'bg-indigo-50 dark:bg-indigo-900/30' : '' }} {{ $isLocked ? 'opacity-60' : '' }}">
                                            <div
                                                role="button"
                                                tabindex="0"
                                                @click="openSection = (openSection === {{ $section->id }} ? null : {{ $section->id }})"
                                                @keydown.enter="openSection = (openSection === {{ $section->id }} ? null : {{ $section->id }})"
                                                class="flex w-full cursor-pointer items-center gap-3 px-2 py-2.5 text-left"
                                            >
                                                <span class="w-4 flex-none text-right text-xs text-gray-400">{{ $sectionNumber }}</span>

                                                @if ($isDone)
                                                    <span class="flex h-4 w-4 flex-none items-center justify-center rounded-full bg-green-100 text-[10px] text-green-600 dark:bg-green-900/40 dark:text-green-300">&#10003;</span>
                                                @elseif ($isLocked)
                                                    <span class="flex h-4 w-4 flex-none items-center justify-center rounded-full bg-gray-100 text-gray-400 dark:bg-gray-700 dark:text-gray-500">
                                                        <x-icons.lock class="h-2 w-2" />
                                                    </span>
                                                @else
                                                    <span class="flex h-4 w-4 flex-none items-center justify-center rounded-full border-2 border-gray-300 dark:border-gray-600"></span>
                                                @endif

                                                <span class="min-w-0 flex-1">
                                                    <span class="block truncate text-xs font-semibold text-gray-900 dark:text-white">{{ $section->title }}</span>
                                                    <span class="block truncate text-[11px] text-gray-500 dark:text-gray-400">
                                                        @if ($isLocked)
                                                            Locked &middot; finish section {{ $sectionNumber - 1 }} first
                                                        @else
                                                            {{ $sectionLessons->count() }} video{{ $sectionLessons->count() === 1 ? '' : 's' }}
                                                            @if ($questionCount) &middot; {{ $questionCount }} question{{ $questionCount === 1 ? '' : 's' }} @endif
                                                            @if ($hasWrittenAnswer) + written answer @endif
                                                            @if ($isDone) &middot; already watched @endif
                                                        @endif
                                                    </span>
                                                </span>

                                                <div class="flex flex-none items-center gap-2">
                                                    @if ($isDone)
                                                        <span class="rounded-full bg-green-100 px-3 py-1 text-[11px] font-semibold text-green-700 dark:bg-green-900/40 dark:text-green-300">Done</span>
                                                    @elseif ($isLocked)
                                                        <span class="rounded-full bg-gray-100 px-3 py-1 text-[11px] font-semibold text-gray-400 dark:bg-gray-700 dark:text-gray-500">Locked</span>
                                                    @elseif ($nextLesson)
                                                        @php $sectionHasStarted = $sectionLessons->contains(fn ($l) => $startedLessonIds->contains($l->id)); @endphp
                                                        <span class="rounded-full bg-indigo-600 px-3 py-1 text-[11px] font-semibold text-white">{{ $sectionHasStarted ? 'Continue' : 'Start' }}</span>
                                                    @endif
                                                </div>

                                                <x-icons.chevron-left class="h-3.5 w-3.5 flex-none text-gray-400 transition-transform duration-200" x-bind:class="openSection === {{ $section->id }} ? '-rotate-90' : 'rotate-90'" />
                                            </div>

                                            @unless ($isLocked)
                                                <div data-section-id="{{ $section->id }}" x-show="openSection === {{ $section->id }}" x-cloak x-transition class="bg-gray-50 px-2 pb-2.5 pl-10 dark:bg-gray-900/40">
                                                    @if ($sectionLessons->count() === 1)
                                                        {{-- A section with a single lesson has nothing to choose between,
                                                        so opening the section goes straight to its video and questions
                                                        instead of making the student click through another row first. --}}
                                                        @php
                                                            $onlyLesson = $sectionLessons->first();
                                                            $onlyLessonHasStarted = $startedLessonIds->contains($onlyLesson->id);
                                                            $onlyLessonStepIndex = $flatLessonSteps->search(fn ($s) => $s['lessonId'] === $onlyLesson->id);
                                                            $onlyLessonAdvanceTarget = $flatLessonSteps->get($onlyLessonStepIndex + 1);
                                                        @endphp
                                                        @include('student.partials.lesson-preview', [
                                                            'lesson' => $onlyLesson,
                                                            'track' => $track,
                                                            'level' => $levelItem,
                                                            'lessonIsDone' => $isDone,
                                                            'lessonHasStarted' => $onlyLessonHasStarted,
                                                            'resumeSeconds' => $lessonProgressByLessonId->get($onlyLesson->id)?->video_progress_seconds ?? 0,
                                                            'assessment' => $onlyLesson->activity,
                                                            'attempt' => $onlyLesson->activity ? $latestAttemptByAssessmentId->get($onlyLesson->activity->id) : null,
                                                            'checked' => $onlyLesson->activity ? $checkedAssessmentIds->contains($onlyLesson->activity->id) : false,
                                                            'attemptsUsed' => $onlyLesson->activity ? ($attemptsUsedByAssessmentId->get($onlyLesson->activity->id) ?? 0) : 0,
                                                            'maxAttempts' => \App\Services\AssessmentGradingService::MAX_ATTEMPTS,
                                                            'advanceTarget' => $onlyLessonAdvanceTarget,
                                                        ])
                                                    @else
                                                        <div class="space-y-1">
                                                            @php $subPriorDone = true; @endphp
                                                            @foreach ($sectionLessons as $subIndex => $lesson)
                                                                @php
                                                                    $lessonIsDone = $completedLessonIds->contains($lesson->id);
                                                                    $lessonIsLocked = ! $subPriorDone;
                                                                    $subPriorDone = $subPriorDone && $lessonIsDone;
                                                                    $lessonIsResume = $resumeNode?->type === 'lesson' && $resumeNode->lesson->id === $lesson->id;
                                                                    $lessonHasStarted = $startedLessonIds->contains($lesson->id);
                                                                    $lessonQuestionCount = $lesson->activity?->questions->count() ?? 0;
                                                                @endphp
                                                                <div class="rounded-lg {{ $lessonIsResume ? 'bg-indigo-50 dark:bg-indigo-900/30' : 'bg-white dark:bg-gray-800' }} {{ $lessonIsLocked ? 'opacity-60' : '' }}">
                                                                    {{-- Opening the section shows every unlocked lesson's video and
                                                                    questions directly; this row is just a heading, not a toggle. --}}
                                                                    <div class="flex items-center gap-2 px-2 py-2">
                                                                        @if ($lessonIsDone)
                                                                            <span class="flex h-5 w-5 flex-none items-center justify-center rounded-full bg-green-100 text-[10px] text-green-600 dark:bg-green-900/40 dark:text-green-300">&#10003;</span>
                                                                        @elseif ($lessonIsLocked)
                                                                            <span class="flex h-5 w-5 flex-none items-center justify-center rounded-full bg-gray-100 text-gray-400 dark:bg-gray-700 dark:text-gray-500">
                                                                                <x-icons.lock class="h-2.5 w-2.5" />
                                                                            </span>
                                                                        @else
                                                                            <span class="flex h-5 w-5 flex-none items-center justify-center rounded-full border-2 border-gray-300 dark:border-gray-600"></span>
                                                                        @endif

                                                                        <span class="min-w-0 flex-1 text-xs text-gray-700 dark:text-gray-300">
                                                                            {{ $section->title }} {{ $subIndex + 1 }}
                                                                            @if (! $lessonIsLocked && $lessonQuestionCount) &middot; {{ $lessonQuestionCount }} question{{ $lessonQuestionCount === 1 ? '' : 's' }} @endif
                                                                        </span>

                                                                        @if ($lessonIsLocked)
                                                                            <span class="rounded-md bg-gray-100 px-2.5 py-0.5 text-[10px] font-semibold text-gray-400 dark:bg-gray-700 dark:text-gray-500">Locked</span>
                                                                        @endif
                                                                    </div>

                                                                    @unless ($lessonIsLocked)
                                                                        @php
                                                                            $lessonStepIndex = $flatLessonSteps->search(fn ($s) => $s['lessonId'] === $lesson->id);
                                                                            $lessonAdvanceTarget = $flatLessonSteps->get($lessonStepIndex + 1);
                                                                        @endphp
                                                                        <div data-lesson-id="{{ $lesson->id }}" class="scroll-mt-24 px-2 pb-3">
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
                                                        </div>
                                                    @endif
                                                </div>
                                            @endunless
                                        </div>
                                    @endforeach
                                </div>
                            @endunless
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div x-show="changeLevelOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="background: rgba(0,0,0,.4)" @click.self="changeLevelOpen = false">
            <div x-show="changeLevelOpen" x-transition class="max-h-[90vh] w-full max-w-xl overflow-y-auto rounded-xl bg-white p-6 shadow-xl dark:bg-gray-800">
                <h2 class="text-lg font-bold text-gray-900 dark:text-white">Change your level</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Choose where to start inside a track that's open to you.</p>

                <div class="mt-4 rounded-lg border border-indigo-200 bg-indigo-50 p-4 text-sm leading-relaxed text-indigo-900 dark:border-indigo-800 dark:bg-indigo-900/20 dark:text-indigo-200">
                    <p class="font-semibold">How tracks unlock</p>
                    <p class="mt-1">
                        You're currently on <span class="font-bold">{{ $currentTrack->track_code }}</span>, the track your placement test put you in (or the one you've since moved up to).
                        Tracks are taken in order ({{ $pickerTracks->pluck('track_code')->implode(' → ') }}), one at a time.
                    </p>
                    <ul class="mt-2 list-disc space-y-1 pl-5">
                        <li>Only your current track, <span class="font-bold">{{ $currentTrack->track_code }}</span>, is open to work in.</li>
                        <li>The next track unlocks automatically when you finish this one.</li>
                        <li>Tracks before your placement stay locked, and tracks you've finished stay open for review.</li>
                    </ul>
                </div>

                <p class="mt-5 text-xs font-bold uppercase tracking-wide text-gray-400">Track</p>
                <div class="mt-2 flex flex-wrap items-center gap-2">
                    @foreach ($pickerTracks as $pickerTrack)
                        @if ($unlockedTrackIds->contains($pickerTrack->id))
                            <button type="button" @click="selTrack = '{{ $pickerTrack->track_code }}'"
                                    class="rounded-md border px-3 py-1.5 text-sm"
                                    :class="selTrack === '{{ $pickerTrack->track_code }}' ? 'border-indigo-500 bg-indigo-50 font-bold text-indigo-700 dark:border-indigo-400 dark:bg-indigo-900/30 dark:text-indigo-200' : 'border-gray-300 font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700'">
                                {{ $pickerTrack->track_code }}
                            </button>
                        @else
                            <span title="Locked — tracks open in order" class="inline-flex cursor-not-allowed items-center gap-1 rounded-md border border-dashed border-gray-300 bg-gray-50 px-3 py-1.5 text-sm font-medium text-gray-400 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-500">
                                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>
                                {{ $pickerTrack->track_code }}
                            </span>
                        @endif
                    @endforeach
                </div>

                <p class="mt-5 text-xs font-bold uppercase tracking-wide text-gray-400">Starting level</p>
                <div class="mt-2 grid grid-cols-4 gap-2 sm:grid-cols-6">
                    @foreach ([1, 10, 20, 30, 40, 50, 60, 70, 80, 90, 100] as $checkpoint)
                        <button type="button" @click="selLevel = {{ $checkpoint }}"
                                class="rounded-md border px-2 py-2 text-sm font-semibold"
                                :class="selLevel === {{ $checkpoint }} ? 'border-indigo-500 bg-indigo-50 text-indigo-700 dark:border-indigo-400 dark:bg-indigo-900/30 dark:text-indigo-200' : 'border-gray-200 text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700'">
                            {{ $checkpoint }}
                        </button>
                    @endforeach
                </div>

                <div class="mt-5 rounded-md bg-amber-50 px-3 py-2.5 text-sm text-amber-800 dark:bg-amber-900/20 dark:text-amber-300">
                    Your finished work is kept. If you come back to a level later, your progress is still there.
                </div>

                <div class="mt-5 flex items-center justify-end gap-2">
                    <button type="button" @click="changeLevelOpen = false" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-600 hover:bg-gray-100 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">Cancel</button>
                    <form method="POST" action="{{ route('student.change-level') }}">
                        @csrf
                        <input type="hidden" name="track_code" :value="selTrack">
                        <input type="hidden" name="level_number" :value="selLevel">
                        <button type="submit" class="rounded-md bg-teal-700 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-600">
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
