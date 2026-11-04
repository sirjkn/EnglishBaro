<x-layouts.student :title="$lesson->title">
    <div class="grid grid-cols-1 gap-6" :class="distractionFree ? '' : 'lg:grid-cols-[18rem_minmax(0,1fr)]'">
        {{-- Sidebar: every level of the track, current one expanded --}}
        <div class="order-2 lg:order-1" x-show="!distractionFree">
            <div class="rounded-xl border border-black/10 bg-white dark:border-white/10 dark:bg-gray-800">
                <div class="border-b border-gray-200 p-4 dark:border-gray-700">
                    <p class="text-xs font-medium uppercase text-indigo-600 dark:text-indigo-400">Track {{ $track->track_code }}</p>
                    <a href="{{ route('student.tracks.show', $track) }}" class="text-sm font-semibold text-gray-900 hover:text-indigo-600 dark:text-white">
                        {{ $track->name }}
                    </a>
                </div>

                <p class="px-4 pt-3 text-xs font-semibold uppercase tracking-wide text-gray-400">Course Material</p>

                <div class="max-h-[36rem] overflow-y-auto p-2" x-data="{ open: {{ $level->number }} }">
                    @foreach ($levelStatuses as $number => $status)
                        @continue ($number !== $level->number)
                        @php $isCurrent = $number === $level->number; @endphp
                        <div class="border-b border-gray-100 last:border-0 dark:border-gray-700">
                            <button
                                type="button"
                                @click="open = (open === {{ $number }} ? null : {{ $number }})"
                                :class="open === {{ $number }} ? 'bg-indigo-50 dark:bg-indigo-900/30' : ''"
                                class="flex w-full items-center gap-2 rounded-lg px-2 py-2.5 text-left text-sm font-medium {{ $status['unlocked'] ? 'text-gray-900 dark:text-white' : 'text-gray-400 dark:text-gray-500' }}"
                            >
                                @if ($status['completed'])
                                    <span class="flex h-4 w-4 flex-none items-center justify-center rounded-full bg-green-500 text-[10px] text-white">&#10003;</span>
                                @elseif (! $status['unlocked'])
                                    <x-icons.lock class="h-3.5 w-3.5 flex-none" />
                                @else
                                    <span class="h-4 w-4 flex-none rounded-full border-2 {{ $isCurrent ? 'border-indigo-600' : 'border-gray-300 dark:border-gray-600' }}"></span>
                                @endif
                                <span class="truncate">Level {{ $number }}</span>
                            </button>

                            <div x-show="open === {{ $number }}" x-cloak x-transition class="pb-2 pl-8 pr-2">
                                @if ($status['unlocked'])
                                    @if ($isCurrent)
                                        <div x-data="{ openSection: {{ $lesson->section_id }} }">
                                        @foreach ($sections as $section)
                                            <button
                                                type="button"
                                                @click="openSection = (openSection === {{ $section->id }} ? null : {{ $section->id }})"
                                                class="flex w-full items-center justify-between pt-2 pb-1 text-xs font-semibold uppercase text-indigo-600 dark:text-indigo-400"
                                            >
                                                <span>{{ $section->title }}</span>
                                                <x-icons.chevron-left class="h-3 w-3 flex-none transition-transform duration-200" x-bind:class="openSection === {{ $section->id }} ? '-rotate-90' : 'rotate-90'" />
                                            </button>
                                            <div x-show="openSection === {{ $section->id }}" x-cloak x-transition>
                                            @foreach ($section->lessons as $sectionLesson)
                                                <a href="{{ route('student.tracks.learn', [$track, $level, $sectionLesson]) }}"
                                                   class="flex items-center gap-2 rounded-lg px-2 py-1.5 text-sm {{ $sectionLesson->id === $lesson->id ? 'bg-indigo-50 font-medium text-indigo-600 dark:bg-indigo-900/40 dark:text-indigo-300' : 'text-gray-600 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-700' }}">
                                                    @if ($completedLessonIds->contains($sectionLesson->id))
                                                        <span class="text-green-500">&#10003;</span>
                                                    @else
                                                        <span class="h-2 w-2 rounded-full border border-gray-300 dark:border-gray-600"></span>
                                                    @endif
                                                    <span class="truncate">{{ $sectionLesson->title }}</span>
                                                </a>
                                                @if ($sectionLesson->activity)
                                                    @if ($completedLessonIds->contains($sectionLesson->id))
                                                        <a href="{{ route('student.tracks.lesson-activity', [$track, $level, $sectionLesson]) }}"
                                                           class="ml-4 flex items-center gap-2 rounded-lg px-2 py-1.5 text-sm text-gray-600 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-700">
                                                            <x-icons.medal class="h-3.5 w-3.5" />
                                                            Activity Questions
                                                        </a>
                                                    @else
                                                        <span class="ml-4 flex items-center gap-2 rounded-lg px-2 py-1.5 text-sm text-gray-400 dark:text-gray-500" title="Finish this lesson first">
                                                            <x-icons.lock class="h-3.5 w-3.5" />
                                                            Activity Questions
                                                        </span>
                                                    @endif
                                                @endif
                                            @endforeach
                                            </div>
                                        @endforeach
                                        </div>
                                    @else
                                        <a href="{{ route('student.tracks.resume', [$track, $status['level']]) }}" class="block rounded-lg px-2 py-1.5 text-sm text-indigo-600 hover:bg-gray-50 dark:text-indigo-400 dark:hover:bg-gray-700">
                                            Go to Level {{ $number }} &rarr;
                                        </a>
                                    @endif
                                @else
                                    <p class="pt-1 text-xs text-gray-400 dark:text-gray-500">Finish level {{ $number - 1 }} to unlock.</p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Main content --}}
        <div class="order-1 lg:order-2" x-data="lessonVideoTracker({{ (int) $resumeSeconds }}, '{{ route('student.tracks.video-progress', [$track, $level, $lesson]) }}')">
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

            <div class="mb-2 flex justify-end">
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
                    @if ($nextStep)
                        <a href="{{ $nextStep['url'] }}" class="rounded-md border border-gray-300 px-3 py-2 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-800 sm:px-4 sm:text-sm">
                            {{ $nextStep['label'] }} &rarr;
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
        </div>

    </div>
</x-layouts.student>
