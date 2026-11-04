<x-layouts.student :title="$lesson->title.' Activity Questions'">
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

    <div class="mx-auto max-w-3xl"
         x-data="lessonActivity({
             checkUrl: @js(route('student.tracks.lesson-activity.check-answers', [$track, $level, $lesson])),
             alreadyChecked: @js($checked),
             initialAnswers: @js($initialAnswers),
             initialFeedback: @js($initialFeedback),
             initialScore: @js($attempt->score ?? 0),
             initialPassed: @js((bool) ($attempt->passed ?? false)),
             attemptsUsed: @js($attemptsUsed),
             maxAttempts: @js($maxAttempts),
         })">
        <a href="{{ route('student.tracks.level', [$track, $level]) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">
            &larr; {{ $level->title }}
        </a>

        <div class="mt-3 flex items-center justify-between">
            <div>
                <span class="text-xs font-medium uppercase text-indigo-600 dark:text-indigo-400">{{ $lesson->title }}</span>
                <h1 class="text-xl font-bold text-gray-900 dark:text-white">{{ $assessment->title }}</h1>
            </div>
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

        <div class="mt-6 space-y-5">
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

        <div class="mt-6 flex flex-wrap items-center justify-between gap-3 border-t border-gray-200 pt-4 dark:border-gray-700">
            <div class="flex gap-2">
                @if ($previousStep)
                    <a href="{{ $previousStep['url'] }}" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-800">
                        &larr; {{ $previousStep['label'] }}
                    </a>
                @endif
            </div>

            @if ($nextStep)
                <a href="{{ $nextStep['url'] }}"
                   x-bind:class="!checked ? 'pointer-events-none opacity-50 cursor-not-allowed' : ''"
                   x-bind:aria-disabled="(!checked).toString()"
                   class="rounded-md bg-indigo-600 px-5 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
                    {{ $nextStep['label'] }} &rarr;
                </a>
            @elseif ($isLastStep)
                <span x-show="checked" x-cloak class="text-sm font-medium text-green-600">Level complete! Head back to see what's next.</span>
            @endif
        </div>
        <p x-show="nextStep && !checked" x-cloak class="mt-1 text-right text-xs text-gray-500 dark:text-gray-400">Check your answers to continue</p>
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
