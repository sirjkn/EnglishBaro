@props(['media', 'title' => null])

@if ($media)
<div class="flex w-full flex-col items-center justify-center overflow-hidden rounded-xl bg-black px-6 py-4">
    @if ($media->provider === 'mux' && $media->mux_playback_id)
        @once
            <script src="https://cdn.jsdelivr.net/npm/@mux/mux-player@2.9.0/dist/mux-player.js" type="module"></script>
        @endonce
        @if ($media->mux_ready)
            @if ($title)
                <p class="mb-2 text-sm font-medium text-gray-300">{{ $title }}</p>
            @endif
            <mux-player audio class="w-full" playback-id="{{ $media->mux_playback_id }}" stream-type="on-demand" x-init="typeof initPlayer === 'function' && initPlayer($el)"></mux-player>
        @else
            <div class="flex w-full items-center justify-center py-6 text-sm text-gray-400">Audio is still processing. Please check back shortly.</div>
        @endif
    @else
        <div class="flex w-full items-center justify-center py-6 text-sm text-gray-400">Audio is not available for this lesson.</div>
    @endif
</div>
@endif
