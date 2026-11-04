<x-layouts.student :title="$track->track_code.' '.$level->title">
    <div x-data="{ openLevel: {{ $level->number }}, openSection: null, search: '', changeLevelOpen: false, selTrack: '{{ $track->track_code }}', selLevel: {{ $level->number }} }" class="max-w-4xl">
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
            <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
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
                <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
                    <p class="text-xl font-bold text-gray-900 dark:text-white">{{ $completedLevelsCount }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Levels completed</p>
                </div>
                <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
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

            <div class="mt-4 divide-y divide-gray-100 overflow-hidden rounded-xl border border-gray-200 bg-white dark:divide-gray-700 dark:border-gray-700 dark:bg-gray-800">
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
                            <span class="w-9 flex-none font-mono text-xs text-gray-400">{{ str_pad($levelItem->number, 3, '0', STR_PAD_LEFT) }}</span>

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
                                    {!! $levelItem->sections->pluck('title')->implode(' &middot; ') !!}
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
                                    @php $sectionNumber = 0; $priorDone = true; @endphp
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
                                                        <a href="{{ route('student.tracks.learn', [$track, $levelItem, $sectionLessons->first()]) }}" @click.stop class="rounded-full border border-gray-300 px-3 py-1 text-[11px] font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700">Review</a>
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
                                                <div x-show="openSection === {{ $section->id }}" x-cloak x-transition class="space-y-1 bg-gray-50 px-2 pb-2.5 pl-10 dark:bg-gray-900/40">
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
                                                        <div class="flex items-center gap-2 rounded-lg px-2 py-2 {{ $lessonIsResume ? 'bg-indigo-50 dark:bg-indigo-900/30' : 'bg-white dark:bg-gray-800' }} {{ $lessonIsLocked ? 'opacity-60' : '' }}">
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

                                                            @if ($lessonIsDone)
                                                                <a href="{{ route('student.tracks.learn', [$track, $levelItem, $lesson]) }}" class="rounded-full border border-gray-300 px-2.5 py-0.5 text-[10px] font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700">Review</a>
                                                            @elseif ($lessonIsLocked)
                                                                <span class="rounded-full bg-gray-100 px-2.5 py-0.5 text-[10px] font-semibold text-gray-400 dark:bg-gray-700 dark:text-gray-500">Locked</span>
                                                            @else
                                                                <a href="{{ route('student.tracks.learn', [$track, $levelItem, $lesson]) }}" class="rounded-full bg-indigo-600 px-2.5 py-0.5 text-[10px] font-semibold text-white hover:bg-indigo-500">{{ $lessonHasStarted ? 'Continue' : 'Start' }}</a>
                                                            @endif
                                                        </div>
                                                    @endforeach
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
            <div x-show="changeLevelOpen" x-transition class="w-full max-w-[19.5rem] rounded-xl bg-white p-3 shadow-xl dark:bg-gray-800">
                <h2 class="text-xs font-bold text-gray-900 dark:text-white">Change your level</h2>
                <p class="mt-0.5 text-[10px] leading-tight text-gray-500 dark:text-gray-400">You decide this yourself. Nothing you do in the course or in practice tests changes it automatically.</p>

                <p class="mt-2.5 text-[9px] font-bold uppercase tracking-wide text-gray-400">Track</p>
                <div class="mt-1 flex flex-wrap items-center gap-0.5">
                    @foreach ($pickerTracks as $pickerTrack)
                        <button type="button" @click="selTrack = '{{ $pickerTrack->track_code }}'"
                                class="rounded-full border px-1.5 py-0.5 text-[10px]"
                                :class="selTrack === '{{ $pickerTrack->track_code }}' ? 'border-gray-300 bg-white font-bold text-gray-900 dark:border-gray-500 dark:bg-gray-700 dark:text-white' : 'border-transparent font-medium text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300'">
                            {{ $pickerTrack->track_code }}
                        </button>
                    @endforeach
                </div>

                <p class="mt-2.5 text-[9px] font-bold uppercase tracking-wide text-gray-400">Starting level</p>
                <div class="mt-1 grid grid-cols-3 gap-1">
                    @foreach ([1, 10, 20, 30, 40, 50, 60, 70, 80, 90, 100] as $checkpoint)
                        <button type="button" @click="selLevel = {{ $checkpoint }}"
                                class="rounded-md border px-1 py-1 text-[10px] font-semibold"
                                :class="selLevel === {{ $checkpoint }} ? 'border-gray-400 bg-gray-50 text-gray-900 dark:border-gray-400 dark:bg-gray-700 dark:text-white' : 'border-gray-200 text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700'">
                            {{ $checkpoint }}
                        </button>
                    @endforeach
                </div>

                <div class="mt-2.5 rounded-md bg-amber-50 px-1.5 py-1.5 text-[9px] leading-tight text-amber-800 dark:bg-amber-900/20 dark:text-amber-300">
                    Your finished work is kept. If you come back to a level later, your progress is still there.
                </div>

                <div class="mt-2.5 flex items-center justify-end gap-1.5">
                    <button type="button" @click="changeLevelOpen = false" class="rounded-full px-2 py-1 text-[10px] font-semibold text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700">Cancel</button>
                    <form method="POST" action="{{ route('student.change-level') }}">
                        @csrf
                        <input type="hidden" name="track_code" :value="selTrack">
                        <input type="hidden" name="level_number" :value="selLevel">
                        <button type="submit" class="rounded-full bg-teal-700 px-2.5 py-1 text-[10px] font-semibold text-white hover:bg-teal-600">
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
                document.getElementById('level-{{ $level->number }}')?.scrollIntoView({ block: 'start' });
            });
        </script>
    @endpush
</x-layouts.student>
