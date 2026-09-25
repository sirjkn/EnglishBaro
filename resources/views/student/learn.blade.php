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
                        @if ($section->activity)
                            @php
                                $sectionLessonsDone = $section->lessons->pluck('id')->diff($completedLessonIds)->isEmpty() && $section->lessons->isNotEmpty();
                            @endphp
                            @if ($sectionLessonsDone)
                                <a href="{{ route('student.tracks.section-activity', [$track, $level, $section]) }}"
                                   class="flex items-center gap-2 rounded-lg px-2 py-2 text-sm text-gray-600 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-700">
                                    <x-icons.medal class="h-3.5 w-3.5" />
                                    Activity Questions
                                </a>
                            @else
                                <span class="flex items-center gap-2 rounded-lg px-2 py-2 text-sm text-gray-400 dark:text-gray-500" title="Finish this section's lessons first">
                                    <x-icons.lock class="h-3.5 w-3.5" />
                                    Activity Questions
                                </span>
                            @endif
                        @endif
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Main content --}}
        <div class="order-1 lg:order-2 lg:col-span-3">
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
    </div>
</x-layouts.student>
