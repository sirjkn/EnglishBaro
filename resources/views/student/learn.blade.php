<x-layouts.student :title="$lesson->title">
    <div class="mx-auto max-w-3xl" x-data="lessonVideoTracker({{ (int) $resumeSeconds }}, '{{ route('student.tracks.video-progress', [$track, $level, $lesson]) }}')">
        @php
            $videoResources = $lesson->resources->filter(fn ($r) => $r->media?->type === 'video');
            $audioResources = $lesson->resources->filter(fn ($r) => $r->media?->type === 'audio');
            $imageResources = $lesson->resources->filter(fn ($r) => $r->media?->type === 'image');
            $documentResources = $lesson->resources->filter(fn ($r) => ! in_array($r->media?->type, ['video', 'audio', 'image'], true));
            $primaryVideo = $videoResources->first()?->media ?? $lesson->video;
            // When there's no video, the first audio file takes the main
            // player slot instead; any further audio stays in the list below.
            $primaryAudioResource = $primaryVideo ? null : $audioResources->first();
            $secondaryAudioResources = $primaryAudioResource ? $audioResources->skip(1) : $audioResources;
        @endphp

        <div class="mb-2 flex items-center justify-between">
            <a href="{{ route('student.tracks.level', [$track, $level]) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">
                &larr; {{ $level->title }}
            </a>
            <button
                type="button"
                @click="distractionFree = !distractionFree"
                class="rounded-md border border-black/10 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-50 dark:border-white/10 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
            >
                <span x-text="distractionFree ? 'Exit Distraction Free' : 'Distraction Free'"></span>
            </button>
        </div>

        @if ($primaryVideo)
            <x-video-player :media="$primaryVideo" />
        @elseif ($primaryAudioResource)
            <x-audio-player :media="$primaryAudioResource->media" :title="$primaryAudioResource->title" />
        @endif

        <h1 class="mt-4 text-xl font-bold text-gray-900 dark:text-white">{{ $lesson->title }}</h1>
        @if ($lesson->description)
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">{{ $lesson->description }}</p>
        @endif

        @if ($secondaryAudioResources->isNotEmpty())
            @once
                <script src="https://cdn.jsdelivr.net/npm/@mux/mux-player@2.9.0/dist/mux-player.js" type="module"></script>
            @endonce
            <div class="mt-4 space-y-2">
                @foreach ($secondaryAudioResources as $resource)
                    <div class="rounded-lg border border-gray-200 p-3 dark:border-gray-700">
                        <p class="mb-1.5 text-xs font-medium text-gray-500 dark:text-gray-400">{{ $resource->title }}</p>
                        @if ($resource->media->mux_ready && $resource->media->mux_playback_id)
                            <mux-player audio class="w-full" playback-id="{{ $resource->media->mux_playback_id }}" stream-type="on-demand"></mux-player>
                        @else
                            <p class="text-xs text-gray-400">Audio is still processing. Please check back shortly.</p>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif

        @if ($imageResources->isNotEmpty())
            <div class="mt-4 grid grid-cols-2 gap-2 sm:grid-cols-3">
                @foreach ($imageResources as $resource)
                    <a href="{{ $resource->media->resolved_url }}" target="_blank" class="block overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700">
                        <img src="{{ $resource->media->resolved_url }}" alt="{{ $resource->title }}" class="aspect-square w-full object-cover">
                    </a>
                @endforeach
            </div>
        @endif

        @if ($lesson->ebook)
            <div class="mt-4 flex items-center justify-between rounded-lg border border-gray-200 p-3 dark:border-gray-700">
                <span class="text-sm text-gray-700 dark:text-gray-300">eBook: {{ $lesson->ebook->title }}</span>
                @if ($lesson->ebook->file)
                    <a href="{{ $lesson->ebook->file->resolved_url }}" target="_blank" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">Download</a>
                @endif
            </div>
        @endif

        @if ($documentResources->isNotEmpty())
            <div class="mt-4">
                <p class="text-sm font-semibold text-gray-900 dark:text-white">Resources</p>
                <ul class="mt-2 space-y-1">
                    @foreach ($documentResources as $resource)
                        <li>
                            <a href="{{ $resource->media->resolved_url }}" target="_blank" class="text-sm text-indigo-600 hover:text-indigo-500">{{ $resource->title }}</a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="mt-6 flex items-center justify-between gap-2 border-t border-gray-200 pt-4 dark:border-gray-700">
            <div class="flex min-w-0 flex-none gap-2">
                @if ($previousStep)
                    <a href="{{ $previousStep['url'] }}" class="rounded-md border border-gray-300 px-3 py-2 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-800 sm:px-4 sm:text-sm">
                        &larr; {{ $previousStep['label'] }}
                    </a>
                @endif
            </div>

            <form method="POST" action="{{ route('student.tracks.complete-lesson', [$track, $level, $lesson]) }}" class="flex-none">
                @csrf
                <button type="submit" @disabled($isCompleted) :disabled="!videoSatisfied" class="rounded-md bg-indigo-600 px-3 py-2 text-xs font-semibold text-white hover:bg-indigo-500 disabled:cursor-not-allowed disabled:bg-gray-300 disabled:text-gray-500 disabled:hover:bg-gray-300 dark:disabled:bg-gray-700 dark:disabled:text-gray-400 sm:px-5 sm:text-sm">
                    {{ $isCompleted ? 'Completed' : 'Mark Completed' }}
                </button>
            </form>
        </div>
        @unless ($isCompleted)
            <p class="mt-1 text-right text-xs text-gray-400" x-show="!videoSatisfied" x-cloak>{{ $primaryVideo ? 'Watch the full video' : 'Listen to the full audio' }} to mark this lesson complete.</p>
        @endunless

        {{-- Activity Questions: inline on the same page as the lesson --}}
        @if ($assessment)
            <div class="mt-8 border-t border-gray-200 pt-6 dark:border-gray-700">
                @if (! $isCompleted)
                    <div class="flex items-center gap-2 rounded-lg border border-gray-200 bg-gray-50 p-4 text-sm text-gray-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400">
                        <x-icons.lock class="h-4 w-4 flex-none" />
                        Finish this lesson to unlock its Activity Questions.
                    </div>
                @else
                    @php
                        $initialAnswers = [];
                        $initialFeedback = [];
                        foreach ($assessment->questions as $q) {
                            if ($q->type === 'multiple_choice' && $q->allow_multiple) {
                                $initialAnswers[$q->id] = [];
                            }
                        }
                        $revealAnswers = $attempt && ((bool) $attempt->passed || $attemptsUsed >= $maxAttempts);

                        if ($attempt) {
                            foreach ($attempt->answers as $answer) {
                                $q = $assessment->questions->firstWhere('id', $answer->assessment_question_id);
                                $isMultiSelect = $q?->type === 'multiple_choice' && $q->allow_multiple;

                                $initialAnswers[$answer->assessment_question_id] = $isMultiSelect
                                    ? ($answer->selected_option_ids ?? [])
                                    : ($answer->assessment_option_id ?? $answer->short_answer_text);

                                $correctOption = $q?->options->firstWhere('is_correct', true);
                                $initialFeedback[$answer->assessment_question_id] = [
                                    'correct' => (bool) $answer->is_correct,
                                    'correct_option_id' => $revealAnswers ? $correctOption?->id : null,
                                    'correct_option_ids' => $revealAnswers && $isMultiSelect ? $q->options->where('is_correct', true)->pluck('id')->values()->all() : null,
                                    'correct_text' => $revealAnswers && in_array($q?->type, ['short_answer', 'fill_blank'], true) ? $q->correct_short_answer : null,
                                    'pending_review' => $answer->isPendingReview(),
                                    'review_remarks' => $answer->review_remarks,
                                ];
                            }
                        }
                    @endphp

                    <div x-data="lessonActivity({
                        checkUrl: @js(route('student.tracks.lesson-activity.check-answers', [$track, $level, $lesson])),
                        alreadyChecked: @js($checked),
                        initialAnswers: @js($initialAnswers),
                        initialFeedback: @js($initialFeedback),
                        initialScore: @js($attempt->score ?? 0),
                        initialPassed: @js((bool) ($attempt->passed ?? false)),
                        attemptsUsed: @js($attemptsUsed),
                        maxAttempts: @js($maxAttempts),
                    })">
                        <div class="flex items-center justify-between">
                            <h2 class="text-lg font-bold text-gray-900 dark:text-white">{{ $assessment->title }}</h2>
                            <span x-show="checked" x-cloak class="rounded-full px-3 py-1 text-xs font-bold" :class="passed ? 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300' : 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300'">
                                <span x-text="score"></span>% <span x-text="passed ? 'Passed' : 'Try again'"></span>
                            </span>
                        </div>
                        <p class="mt-1 text-xs text-gray-400" x-show="!passed" x-cloak>
                            <span x-show="attemptsUsed < maxAttempts" x-text="(maxAttempts - attemptsUsed) + ' attempt' + (maxAttempts - attemptsUsed === 1 ? '' : 's') + ' remaining'"></span>
                            <span x-show="attemptsUsed >= maxAttempts" class="font-medium text-red-600">No attempts remaining.</span>
                        </p>
                        @if ($assessment->description)
                            <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">{{ $assessment->description }}</p>
                        @endif

                        <div class="mt-4 space-y-5">
                            @foreach ($assessment->questions as $question)
                                <div class="rounded-lg border border-black/10 bg-white p-4 dark:border-white/10 dark:bg-gray-800">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $loop->iteration }}. {{ $question->question }}</p>

                                    @if (in_array($question->type, ['short_answer', 'fill_blank'], true))
                                        <input type="text"
                                               x-model="answers[{{ $question->id }}]"
                                               :disabled="inputsLocked"
                                               class="mt-3 w-full rounded-md border-gray-300 text-sm disabled:bg-gray-100 dark:border-gray-600 dark:bg-gray-900 dark:text-white dark:disabled:bg-gray-800"
                                               placeholder="{{ $question->type === 'fill_blank' ? 'Fill in the blank' : 'Type your answer' }}">
                                    @elseif ($question->allow_multiple)
                                        <p class="mt-1 text-xs text-gray-400">Select all that apply.</p>
                                        <div class="mt-2 space-y-2">
                                            @foreach ($question->options as $option)
                                                <label class="flex items-center gap-2 rounded-md border px-3 py-2 text-sm"
                                                       :class="feedback[{{ $question->id }}] ? (feedback[{{ $question->id }}].correct_option_ids.includes({{ $option->id }}) ? 'border-green-400 bg-green-50 dark:bg-green-900/20' : ((answers[{{ $question->id }}] || []).includes({{ $option->id }}) ? 'border-red-400 bg-red-50 dark:bg-red-900/20' : 'border-gray-200 dark:border-gray-700')) : 'border-gray-200 dark:border-gray-700'">
                                                    <input type="checkbox"
                                                           value="{{ $option->id }}"
                                                           x-model.number="answers[{{ $question->id }}]"
                                                           :disabled="inputsLocked"
                                                           class="text-indigo-600">
                                                    <span class="flex-1 text-gray-700 dark:text-gray-200">{{ $option->option_text }}</span>
                                                    <template x-if="feedback[{{ $question->id }}] && feedback[{{ $question->id }}].correct_option_ids.includes({{ $option->id }})">
                                                        <span class="flex-none text-xs font-semibold text-green-600">&#10003; Correct</span>
                                                    </template>
                                                </label>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="mt-3 space-y-2">
                                            @foreach ($question->options as $option)
                                                <label class="flex items-center gap-2 rounded-md border px-3 py-2 text-sm"
                                                       :class="feedback[{{ $question->id }}] ? (feedback[{{ $question->id }}].correct_option_id === {{ $option->id }} ? 'border-green-400 bg-green-50 dark:bg-green-900/20' : (answers[{{ $question->id }}] == {{ $option->id }} ? 'border-red-400 bg-red-50 dark:bg-red-900/20' : 'border-gray-200 dark:border-gray-700')) : 'border-gray-200 dark:border-gray-700'">
                                                    <input type="radio"
                                                           name="question-{{ $question->id }}"
                                                           value="{{ $option->id }}"
                                                           x-model="answers[{{ $question->id }}]"
                                                           :disabled="inputsLocked"
                                                           class="text-indigo-600">
                                                    <span class="flex-1 text-gray-700 dark:text-gray-200">{{ $option->option_text }}</span>
                                                    <template x-if="feedback[{{ $question->id }}] && feedback[{{ $question->id }}].correct_option_id === {{ $option->id }}">
                                                        <span class="flex-none text-xs font-semibold text-green-600">&#10003; Correct</span>
                                                    </template>
                                                </label>
                                            @endforeach
                                        </div>
                                    @endif

                                    <template x-if="feedback[{{ $question->id }}] && feedback[{{ $question->id }}].pending_review">
                                        <div class="mt-2 rounded-md bg-amber-50 px-3 py-2 text-xs font-medium text-amber-700 dark:bg-amber-900/30 dark:text-amber-300">
                                            <span>&#8987; Pending admin review — short answers can be phrased in more than one way.</span>
                                            <template x-if="feedback[{{ $question->id }}].review_remarks">
                                                <p class="mt-1 font-normal">Admin remarks: <span x-text="feedback[{{ $question->id }}].review_remarks"></span></p>
                                            </template>
                                        </div>
                                    </template>

                                    <template x-if="feedback[{{ $question->id }}] && !feedback[{{ $question->id }}].pending_review">
                                        <p class="mt-2 text-xs font-medium" :class="feedback[{{ $question->id }}].correct ? 'text-green-600' : 'text-red-600'">
                                            <span x-show="feedback[{{ $question->id }}].correct">&#10003; Correct</span>
                                            <span x-show="!feedback[{{ $question->id }}].correct">
                                                &#10007; Not quite.
                                                <template x-if="feedback[{{ $question->id }}].correct_text">
                                                    <span>Correct answer: <span x-text="feedback[{{ $question->id }}].correct_text"></span></span>
                                                </template>
                                            </span>
                                            <template x-if="feedback[{{ $question->id }}].review_remarks">
                                                <span class="block font-normal text-gray-500">Admin remarks: <span x-text="feedback[{{ $question->id }}].review_remarks"></span></span>
                                            </template>
                                        </p>
                                    </template>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-4 flex items-center gap-3">
                            <button type="button" @click="submit()" :disabled="submitting" x-show="!inputsLocked"
                                    class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500 disabled:opacity-50">
                                <span x-text="submitting ? 'Checking...' : (checked ? 'Try Again' : 'Check Answers')"></span>
                            </button>
                            <p x-show="inputsLocked && passed" x-cloak class="text-sm font-medium text-green-600">You passed — nice work!</p>
                            <p x-show="inputsLocked && !passed" x-cloak class="text-sm font-medium text-red-600">No attempts remaining — correct answers are shown above.</p>
                            <p x-show="error" x-text="error" class="text-sm text-red-600"></p>
                        </div>
                    </div>
                @endif
            </div>
        @endif
    </div>

    @push('scripts')
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('lessonActivity', (config) => ({
                    answers: config.initialAnswers || {},
                    feedback: config.initialFeedback || {},
                    checked: config.alreadyChecked,
                    score: config.initialScore,
                    passed: config.initialPassed,
                    attemptsUsed: config.attemptsUsed || 0,
                    maxAttempts: config.maxAttempts || 3,
                    submitting: false,
                    error: null,

                    get inputsLocked() {
                        return this.passed || this.attemptsUsed >= this.maxAttempts;
                    },

                    submit() {
                        this.submitting = true;
                        this.error = null;

                        fetch(config.checkUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            },
                            body: JSON.stringify({ answers: this.answers }),
                        })
                            .then((response) => {
                                if (!response.ok) throw new Error('Could not check your answers. Please try again.');
                                return response.json();
                            })
                            .then((data) => {
                                this.score = data.score;
                                this.passed = data.passed;
                                this.feedback = Object.fromEntries(data.feedback.map((item) => [item.question_id, item]));
                                this.checked = true;
                                this.attemptsUsed = data.attempts_used ?? this.attemptsUsed + 1;
                                this.maxAttempts = data.max_attempts ?? this.maxAttempts;
                            })
                            .catch((err) => {
                                this.error = err.message;
                            })
                            .finally(() => {
                                this.submitting = false;
                            });
                    },
                }));
            });
        </script>
    @endpush
</x-layouts.student>
