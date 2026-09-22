<x-layouts.student :title="$lesson->title">
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-4">
        {{-- Sidebar: sections/lessons for this level --}}
        <div class="order-2 lg:order-1 lg:col-span-1">
            <div class="rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
                <div class="border-b border-gray-200 p-4 dark:border-gray-700">
                    <p class="text-xs font-medium uppercase text-indigo-600 dark:text-indigo-400">Track {{ $track->track_code }}</p>
                    <a href="{{ route('student.tracks.level', [$track, $level]) }}" class="text-sm font-semibold text-gray-900 hover:text-indigo-600 dark:text-white">
                        {{ $level->title }}
                    </a>
                </div>
                <div class="max-h-[32rem] overflow-y-auto p-2">
                    @foreach ($sections as $section)
                        <p class="px-2 pt-3 pb-1 text-xs font-semibold uppercase text-gray-400">{{ $section->title }}</p>
                        @foreach ($section->lessons as $sectionLesson)
                            <a href="{{ route('student.tracks.learn', [$track, $level, $sectionLesson]) }}"
                               class="flex items-center gap-2 rounded-lg px-2 py-2 text-sm {{ $sectionLesson->id === $lesson->id ? 'bg-indigo-50 font-medium text-indigo-600 dark:bg-indigo-900/40 dark:text-indigo-300' : 'text-gray-600 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-700' }}">
                                @if ($completedLessonIds->contains($sectionLesson->id))
                                    <span class="text-green-500">&#10003;</span>
                                @else
                                    <span class="h-2 w-2 rounded-full border border-gray-300 dark:border-gray-600"></span>
                                @endif
                                {{ $sectionLesson->title }}
                            </a>
                        @endforeach
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Main content --}}
        @php
            $initialAnswers = [];
            $initialFeedback = [];
            if ($attempt) {
                foreach ($attempt->answers as $answer) {
                    $initialAnswers[$answer->assessment_question_id] = $answer->assessment_option_id ?? $answer->short_answer_text;
                    $q = $assessment->questions->firstWhere('id', $answer->assessment_question_id);
                    $correctOption = $q?->options->firstWhere('is_correct', true);
                    $initialFeedback[$answer->assessment_question_id] = [
                        'correct' => (bool) $answer->is_correct,
                        'correct_option_id' => $correctOption?->id,
                        'correct_text' => $q?->type === 'short_answer' ? $q->correct_short_answer : null,
                    ];
                }
            }
        @endphp
        <div class="order-1 lg:order-2 lg:col-span-3"
             x-data="grammarActivity({
                 checkUrl: @js($assessment ? route('student.tracks.check-answers', [$track, $level, $lesson]) : null),
                 requiresCheck: @js((bool) $assessment),
                 alreadyChecked: @js($checked),
                 initialAnswers: @js($initialAnswers),
                 initialFeedback: @js($initialFeedback),
                 initialScore: @js($attempt->score ?? 0),
                 initialPassed: @js((bool) ($attempt->passed ?? false)),
             })">
            <x-video-player :media="$lesson->video" />

            <h1 class="mt-4 text-xl font-bold text-gray-900 dark:text-white">{{ $lesson->title }}</h1>
            @if ($lesson->description)
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">{{ $lesson->description }}</p>
            @endif

            @if ($assessment)
                <div class="mt-6 rounded-xl border border-indigo-200 bg-indigo-50/50 p-5 dark:border-indigo-900 dark:bg-indigo-950/30">
                    <div class="flex items-center justify-between">
                        <h2 class="font-semibold text-gray-900 dark:text-white">{{ $assessment->title }}</h2>
                        <span x-show="checked" x-cloak class="rounded-full px-3 py-1 text-xs font-bold" :class="passed ? 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300' : 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300'">
                            <span x-text="score"></span>% <span x-text="passed ? 'Passed' : 'Try again'"></span>
                        </span>
                    </div>
                    @if ($assessment->description)
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">{{ $assessment->description }}</p>
                    @endif

                    <div class="mt-4 space-y-5">
                        @foreach ($assessment->questions as $question)
                            <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $loop->iteration }}. {{ $question->question }}</p>

                                @if ($question->type === 'short_answer')
                                    <input type="text"
                                           x-model="answers[{{ $question->id }}]"
                                           :disabled="checked"
                                           class="mt-3 w-full rounded-md border-gray-300 text-sm disabled:bg-gray-100 dark:border-gray-600 dark:bg-gray-900 dark:text-white dark:disabled:bg-gray-800"
                                           placeholder="Type your answer">
                                @else
                                    <div class="mt-3 space-y-2">
                                        @foreach ($question->options as $option)
                                            <label class="flex items-center gap-2 rounded-md border px-3 py-2 text-sm"
                                                   :class="feedback[{{ $question->id }}] ? (feedback[{{ $question->id }}].correct_option_id === {{ $option->id }} ? 'border-green-400 bg-green-50 dark:bg-green-900/20' : (answers[{{ $question->id }}] == {{ $option->id }} ? 'border-red-400 bg-red-50 dark:bg-red-900/20' : 'border-gray-200 dark:border-gray-700')) : 'border-gray-200 dark:border-gray-700'">
                                                <input type="radio"
                                                       name="question-{{ $question->id }}"
                                                       value="{{ $option->id }}"
                                                       x-model="answers[{{ $question->id }}]"
                                                       :disabled="checked"
                                                       class="text-indigo-600">
                                                <span class="text-gray-700 dark:text-gray-200">{{ $option->option_text }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                @endif

                                <template x-if="feedback[{{ $question->id }}]">
                                    <p class="mt-2 text-xs font-medium" :class="feedback[{{ $question->id }}].correct ? 'text-green-600' : 'text-red-600'">
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

                    <div class="mt-4 flex items-center gap-3">
                        <button type="button" @click="submit()" :disabled="submitting"
                                class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500 disabled:opacity-50">
                            <span x-show="!checked" x-text="submitting ? 'Checking...' : 'Check Answers'"></span>
                            <span x-show="checked">Re-check Answers</span>
                        </button>
                        <p x-show="error" x-text="error" class="text-sm text-red-600"></p>
                    </div>
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
                    @if ($previousLesson)
                        <a href="{{ route('student.tracks.learn', [$track, $level, $previousLesson]) }}" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-800">
                            &larr; Previous
                        </a>
                    @endif
                    @if ($nextLesson)
                        <a href="{{ route('student.tracks.learn', [$track, $level, $nextLesson]) }}"
                           x-bind:class="nextDisabled ? 'pointer-events-none opacity-50 cursor-not-allowed' : ''"
                           x-bind:aria-disabled="nextDisabled.toString()"
                           class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-800">
                            Next &rarr;
                        </a>
                        <p x-show="nextDisabled" x-cloak class="self-center text-xs text-gray-500 dark:text-gray-400">Check your answers to continue</p>
                    @endif
                </div>

                <form method="POST" action="{{ route('student.tracks.complete-lesson', [$track, $level, $lesson]) }}">
                    @csrf
                    <button type="submit" :disabled="{{ $isCompleted ? 'true' : 'nextDisabled' }}" class="rounded-md bg-indigo-600 px-5 py-2 text-sm font-semibold text-white hover:bg-indigo-500 disabled:opacity-50">
                        {{ $isCompleted ? 'Completed' : 'Mark Completed' }}
                    </button>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('grammarActivity', (config) => ({
                    answers: config.initialAnswers || {},
                    feedback: config.initialFeedback || {},
                    checked: config.alreadyChecked,
                    score: config.initialScore,
                    passed: config.initialPassed,
                    submitting: false,
                    error: null,

                    get nextDisabled() {
                        return config.requiresCheck && !this.checked;
                    },

                    submit() {
                        if (!config.checkUrl) return;

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
