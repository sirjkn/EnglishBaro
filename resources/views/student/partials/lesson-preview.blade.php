@php
    $previewVideoResources = $lesson->resources->filter(fn ($r) => $r->media?->type === 'video');
    $previewAudioResources = $lesson->resources->filter(fn ($r) => $r->media?->type === 'audio');
    $previewPrimaryVideo = $previewVideoResources->first()?->media ?? $lesson->video;
    $previewPrimaryAudio = $previewPrimaryVideo ? null : $previewAudioResources->first();
@endphp

<div class="space-y-3 px-2 pb-3">
    @if ($previewPrimaryVideo)
        <x-video-player :media="$previewPrimaryVideo" />
    @elseif ($previewPrimaryAudio)
        <x-audio-player :media="$previewPrimaryAudio->media" :title="$previewPrimaryAudio->title" />
    @else
        <p class="text-xs text-gray-400">No preview media for this lesson yet.</p>
    @endif

    @if ($lesson->activity && $lesson->activity->questions->isNotEmpty())
        <div class="space-y-2">
            <p class="text-[11px] font-bold uppercase tracking-wide text-gray-400">Questions in this lesson</p>
            @foreach ($lesson->activity->questions as $question)
                <div class="rounded-lg border border-gray-200 bg-gray-50 p-3 dark:border-gray-700 dark:bg-gray-900/40">
                    <p class="text-xs font-medium text-gray-900 dark:text-white">{{ $loop->iteration }}. {{ $question->question }}</p>
                    @if ($question->options->isNotEmpty())
                        <ul class="mt-1.5 space-y-1">
                            @foreach ($question->options as $option)
                                <li class="rounded-md border border-gray-200 px-2 py-1 text-xs text-gray-600 dark:border-gray-700 dark:text-gray-300">{{ $option->option_text }}</li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endforeach
        </div>
    @endif

    <a href="{{ route('student.tracks.learn', [$track, $level, $lesson]) }}" class="inline-flex items-center gap-1 text-xs font-semibold text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">
        {{ $lessonIsDone ? 'Open full lesson' : ($lessonHasStarted ? 'Continue full lesson' : 'Start full lesson') }}
        <x-icons.chevron-right class="h-3 w-3" />
    </a>
</div>
