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
        {{-- YouTube's embed policy doesn't allow hiding the title/controls
        chrome outright, so instead we show just a thumbnail + play button
        and only load the real (fully-chromed) player once clicked. --}}
        <div x-data="{ started: false }" class="relative h-full w-full">
            <button
                type="button"
                x-show="! started"
                @click="started = true"
                class="group absolute inset-0 flex h-full w-full items-center justify-center bg-cover bg-center"
                style="background-image: url('https://img.youtube.com/vi/{{ $media->external_id }}/hqdefault.jpg')"
                aria-label="Play video"
            >
                <span class="absolute inset-0 bg-black/20 transition group-hover:bg-black/30"></span>
                <x-icons.play-circle class="relative h-16 w-16 text-white drop-shadow-lg" />
            </button>

            <template x-if="started">
                <iframe
                    class="h-full w-full"
                    src="https://www.youtube-nocookie.com/embed/{{ $media->external_id }}?autoplay=1"
                    title="Video"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                    allowfullscreen
                    referrerpolicy="strict-origin-when-cross-origin"
                ></iframe>
            </template>
        </div>
    @elseif ($media->provider === 'vimeo' && $media->external_id)
        {{-- Same click-to-play facade as YouTube above, so only the play
        button shows until the viewer actually starts the video. --}}
        <div x-data="{ started: false }" class="relative h-full w-full">
            <button
                type="button"
                x-show="! started"
                @click="started = true"
                class="group absolute inset-0 flex h-full w-full items-center justify-center bg-gray-900"
                aria-label="Play video"
            >
                <span class="absolute inset-0 bg-black/20 transition group-hover:bg-black/30"></span>
                <x-icons.play-circle class="relative h-16 w-16 text-white drop-shadow-lg" />
            </button>

            <template x-if="started">
                <iframe
                    class="h-full w-full"
                    src="https://player.vimeo.com/video/{{ $media->external_id }}?autoplay=1&title=0&byline=0&portrait=0"
                    title="Video"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                    allowfullscreen
                    referrerpolicy="strict-origin-when-cross-origin"
                ></iframe>
            </template>
        </div>
    @elseif ($media->source_type === 'external' && (str_contains($media->url ?? '', 'youtube.com') || str_contains($media->url ?? '', 'youtu.be') || str_contains($media->url ?? '', 'vimeo.com')))
        {{-- A YouTube/Vimeo link we couldn't extract a video ID from (unusual
        URL format) — a <video> tag can't play a webpage, so don't even try. --}}
        <div class="flex h-full w-full flex-col items-center justify-center gap-2 p-4 text-center text-gray-400">
            <x-icons.play-circle class="h-10 w-10" />
            <span class="text-sm">Couldn't recognize this video link. Please check the URL and try again.</span>
        </div>
    @elseif ($media->source_type === 'external')
        <video class="h-full w-full" controls preload="metadata" src="{{ $media->url }}" x-init="typeof initPlayer === 'function' && initPlayer($el)">
            Your browser can't play this video. <a href="{{ $media->url }}" target="_blank" rel="noopener" class="text-indigo-300 underline">Open the link directly</a>.
        </video>
    @else
        <video class="h-full w-full" controls preload="metadata" src="{{ $media->resolved_url }}" x-init="typeof initPlayer === 'function' && initPlayer($el)"></video>
    @endif
</div>
