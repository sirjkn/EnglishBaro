@props(['media', 'placeholder' => 'No video uploaded yet.'])

<div class="aspect-video w-full overflow-hidden rounded-xl bg-black">
    @if (! $media)
        <div class="flex h-full w-full flex-col items-center justify-center gap-2 text-gray-400">
            <x-icons.play-circle class="h-10 w-10" />
            <span class="text-sm">{{ $placeholder }}</span>
        </div>
    @elseif ($media->provider === 'mux' && $media->mux_playback_id)
        @once
            <script src="https://cdn.jsdelivr.net/npm/@mux/mux-player@2.9.0/dist/mux-player.js" type="module"></script>
        @endonce
        @if ($media->mux_ready)
            <mux-player class="h-full w-full" playback-id="{{ $media->mux_playback_id }}" stream-type="on-demand" x-init="typeof initPlayer === 'function' && initPlayer($el)"></mux-player>
        @else
            <div class="flex h-full w-full items-center justify-center text-sm text-gray-400">Video is still processing. Please check back shortly.</div>
        @endif
    @elseif ($media->provider === 'mux')
        <div class="flex h-full w-full items-center justify-center text-sm text-gray-400">Video is still processing. Please check back shortly.</div>
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
        <video class="h-full w-full" controls preload="metadata" src="{{ $media->url }}" x-init="typeof initPlayer === 'function' && initPlayer($el)"></video>
    @else
        <video class="h-full w-full" controls preload="metadata" src="{{ $media->resolved_url }}" x-init="typeof initPlayer === 'function' && initPlayer($el)"></video>
    @endif
</div>
