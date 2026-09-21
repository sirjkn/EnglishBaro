@props(['media'])

<div class="aspect-video w-full overflow-hidden rounded-xl bg-black">
    @if (! $media)
        <div class="flex h-full w-full items-center justify-center text-sm text-gray-400">No video available for this lesson.</div>
    @elseif ($media->provider === 'youtube' && $media->external_id)
        <iframe
            class="h-full w-full"
            src="https://www.youtube-nocookie.com/embed/{{ $media->external_id }}"
            title="Lesson video"
            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
            allowfullscreen
            referrerpolicy="strict-origin-when-cross-origin"
        ></iframe>
    @elseif ($media->source_type === 'external')
        <video class="h-full w-full" controls preload="metadata" src="{{ $media->url }}"></video>
    @else
        <video class="h-full w-full" controls preload="metadata" src="{{ $media->resolved_url }}"></video>
    @endif
</div>
