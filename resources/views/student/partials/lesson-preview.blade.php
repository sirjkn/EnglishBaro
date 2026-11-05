@php
    $previewVideoResources = $lesson->resources->filter(fn ($r) => $r->media?->type === 'video');
    $previewAudioResources = $lesson->resources->filter(fn ($r) => $r->media?->type === 'audio');
    $previewPrimaryVideo = $previewVideoResources->first()?->media ?? $lesson->video;
    $previewPrimaryAudio = $previewPrimaryVideo ? null : $previewAudioResources->first();
    $hasAssessment = (bool) $assessment;
    $completeUrl = route('student.tracks.complete-lesson', [$track, $level, $lesson]);
    $progressUrl = route('student.tracks.video-progress', [$track, $level, $lesson]);
@endphp

<div class="space-y-3 px-2 pb-3"
     x-data="lessonVideoTracker({{ (int) $resumeSeconds }}, @js($progressUrl), {
        completed: @js($lessonIsDone),
        completeUrl: @js($completeUrl),
        hasAssessment: @js($hasAssessment),
        advance: @js($advanceTarget),
     })"
>
    @if ($previewPrimaryVideo)
        <x-video-player :media="$previewPrimaryVideo" />
    @elseif ($previewPrimaryAudio)
        <x-audio-player :media="$previewPrimaryAudio->media" :title="$previewPrimaryAudio->title" />
    @else
        <p class="text-xs text-gray-400">No preview media for this lesson yet.</p>
    @endif

    <div class="flex items-center justify-between gap-2">
        <p class="text-xs text-gray-400" x-show="!completed && !videoSatisfied" x-cloak>Watch the full video to mark this lesson complete.</p>
        <p class="text-xs text-red-600" x-show="completeError" x-text="completeError" x-cloak></p>

        <button type="button" @click="markCompleted()" x-show="!completed" :disabled="!videoSatisfied || marking"
                class="ml-auto flex-none rounded-md bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-indigo-500 disabled:cursor-not-allowed disabled:bg-gray-300 disabled:text-gray-500 dark:disabled:bg-gray-700 dark:disabled:text-gray-400">
            <span x-text="marking ? 'Saving...' : 'Mark Completed'"></span>
        </button>
        <span x-show="completed" x-cloak class="ml-auto flex-none rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700 dark:bg-green-900/40 dark:text-green-300">Completed</span>
    </div>

    @if ($hasAssessment)
        <div x-show="!completed" x-cloak class="flex items-center gap-2 rounded-lg border border-gray-200 bg-gray-50 p-3 text-xs text-gray-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400">
            <x-icons.lock class="h-3.5 w-3.5 flex-none" />
            Finish this lesson to unlock its questions.
        </div>

        <div x-show="completed" x-cloak>
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
                advance: @js($advanceTarget),
            })">
                <div class="flex items-center justify-between">
                    <p class="text-[11px] font-bold uppercase tracking-wide text-gray-400">{{ $assessment->title }}</p>
                    <span x-show="checked" x-cloak class="rounded-full px-2 py-0.5 text-[10px] font-bold" :class="passed ? 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300' : 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300'">
                        <span x-text="score"></span>% <span x-text="passed ? 'Passed' : 'Try again'"></span>
                    </span>
                </div>
                <p class="mt-0.5 text-[11px] text-gray-400" x-show="!passed" x-cloak>
                    <span x-show="attemptsUsed < maxAttempts" x-text="(maxAttempts - attemptsUsed) + ' attempt' + (maxAttempts - attemptsUsed === 1 ? '' : 's') + ' remaining'"></span>
                    <span x-show="attemptsUsed >= maxAttempts" class="font-medium text-red-600">No attempts remaining.</span>
                </p>

                <div class="mt-3 space-y-3">
                    @foreach ($assessment->questions as $question)
                        <div class="rounded-lg border border-gray-200 bg-gray-50 p-3 dark:border-gray-700 dark:bg-gray-900/40">
                            <p class="text-xs font-medium text-gray-900 dark:text-white">{{ $loop->iteration }}. {{ $question->question }}</p>

                            @if (in_array($question->type, ['short_answer', 'fill_blank'], true))
                                <input type="text"
                                       x-model="answers[{{ $question->id }}]"
                                       :disabled="inputsLocked"
                                       class="mt-2 w-full rounded-md border-gray-300 text-xs disabled:bg-gray-100 dark:border-gray-600 dark:bg-gray-900 dark:text-white dark:disabled:bg-gray-800"
                                       placeholder="{{ $question->type === 'fill_blank' ? 'Fill in the blank' : 'Type your answer' }}">
                            @elseif ($question->allow_multiple)
                                <p class="mt-1 text-[11px] text-gray-400">Select all that apply.</p>
                                <div class="mt-1.5 space-y-1.5">
                                    @foreach ($question->options as $option)
                                        <label class="flex items-center gap-2 rounded-md border px-2.5 py-1.5 text-xs"
                                               :class="feedback[{{ $question->id }}] ? (feedback[{{ $question->id }}].correct_option_ids.includes({{ $option->id }}) ? 'border-green-400 bg-green-50 dark:bg-green-900/20' : ((answers[{{ $question->id }}] || []).includes({{ $option->id }}) ? 'border-red-400 bg-red-50 dark:bg-red-900/20' : 'border-gray-200 dark:border-gray-700')) : 'border-gray-200 dark:border-gray-700'">
                                            <input type="checkbox"
                                                   value="{{ $option->id }}"
                                                   x-model.number="answers[{{ $question->id }}]"
                                                   :disabled="inputsLocked"
                                                   class="text-indigo-600">
                                            <span class="flex-1 text-gray-700 dark:text-gray-200">{{ $option->option_text }}</span>
                                            <template x-if="feedback[{{ $question->id }}] && feedback[{{ $question->id }}].correct_option_ids.includes({{ $option->id }})">
                                                <span class="flex-none text-[10px] font-semibold text-green-600">&#10003;</span>
                                            </template>
                                        </label>
                                    @endforeach
                                </div>
                            @else
                                <div class="mt-2 space-y-1.5">
                                    @foreach ($question->options as $option)
                                        <label class="flex items-center gap-2 rounded-md border px-2.5 py-1.5 text-xs"
                                               :class="feedback[{{ $question->id }}] ? (feedback[{{ $question->id }}].correct_option_id === {{ $option->id }} ? 'border-green-400 bg-green-50 dark:bg-green-900/20' : (answers[{{ $question->id }}] == {{ $option->id }} ? 'border-red-400 bg-red-50 dark:bg-red-900/20' : 'border-gray-200 dark:border-gray-700')) : 'border-gray-200 dark:border-gray-700'">
                                            <input type="radio"
                                                   name="question-{{ $question->id }}"
                                                   value="{{ $option->id }}"
                                                   x-model="answers[{{ $question->id }}]"
                                                   :disabled="inputsLocked"
                                                   class="text-indigo-600">
                                            <span class="flex-1 text-gray-700 dark:text-gray-200">{{ $option->option_text }}</span>
                                            <template x-if="feedback[{{ $question->id }}] && feedback[{{ $question->id }}].correct_option_id === {{ $option->id }}">
                                                <span class="flex-none text-[10px] font-semibold text-green-600">&#10003;</span>
                                            </template>
                                        </label>
                                    @endforeach
                                </div>
                            @endif

                            <template x-if="feedback[{{ $question->id }}]">
                                <p class="mt-1.5 text-[11px] font-medium" :class="feedback[{{ $question->id }}].correct ? 'text-green-600' : 'text-red-600'">
                                    <span x-show="feedback[{{ $question->id }}].correct">&#10003; Correct</span>
                                    <span x-show="!feedback[{{ $question->id }}].correct">
                                        &#10007; Not quite.
                                        <template x-if="feedback[{{ $question->id }}].correct_text">
                                            <span>Correct answer: <span x-text="feedback[{{ $question->id }}].correct_text"></span></span>
                                        </template>
                                    </span>
                                </p>
                            </template>
                        </div>
                    @endforeach
                </div>

                <div class="mt-3 flex items-center gap-3">
                    <button type="button" @click="submit()" :disabled="submitting" x-show="!inputsLocked"
                            class="rounded-md bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-indigo-500 disabled:opacity-50">
                        <span x-text="submitting ? 'Checking...' : (checked ? 'Try Again' : 'Check Answers')"></span>
                    </button>
                    <p x-show="inputsLocked && passed" x-cloak class="text-xs font-medium text-green-600">You passed — nice work!</p>
                    <p x-show="inputsLocked && !passed" x-cloak class="text-xs font-medium text-red-600">No attempts remaining — correct answers are shown above.</p>
                    <p x-show="error" x-text="error" class="text-xs text-red-600"></p>
                </div>
            </div>
        </div>
    @endif
</div>
