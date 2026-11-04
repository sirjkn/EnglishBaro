<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Services\MediaService;
use App\Services\MuxService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MediaUploadController extends Controller
{
    private const ALLOWED_TYPES = ['video', 'audio', 'image', 'document'];

    public function store(Request $request, MediaService $mediaService): JsonResponse
    {
        $validated = $request->validate([
            'type' => ['required', 'string', 'in:'.implode(',', self::ALLOWED_TYPES)],
            'file' => ['required', 'file'],
        ]);

        $media = $mediaService->storeUploadedFile(
            $request->file('file'),
            $validated['type'],
            $request->file('file')->getClientOriginalName()
        );

        return response()->json([
            'id' => $media->id,
            'type' => $media->type,
            'title' => $media->title,
            'provider' => $media->provider,
            'ready' => $media->isMux() ? $media->mux_ready : true,
            'resolved_url' => $media->resolved_url,
        ]);
    }

    public function destroy(Media $media, MediaService $mediaService): JsonResponse
    {
        abort_unless($media->created_by === auth()->id(), 403);
        abort_if($media->resources()->exists(), 422, 'This file is already attached to a lesson.');

        $mediaService->delete($media);

        return response()->json(['deleted' => true]);
    }

    /**
     * Previously-uploaded local files (images/documents) the admin can
     * reattach instead of uploading the same file again.
     */
    public function libraryAssets(Request $request): JsonResponse
    {
        $type = $request->string('type', 'image')->value();
        $type = in_array($type, ['image', 'document'], true) ? $type : 'image';

        $items = Media::query()
            ->where('type', $type)
            ->where('provider', 'local')
            ->latest()
            ->limit(60)
            ->get(['id', 'title', 'storage_path'])
            ->map(fn ($media) => [
                'id' => $media->id,
                'title' => $media->title,
                'resolved_url' => $media->resolved_url,
            ])
            ->values();

        return response()->json(['data' => $items]);
    }

    public function muxAssets(Request $request, MuxService $muxService): JsonResponse
    {
        $page = max(1, (int) $request->integer('page', 1));
        $type = $request->string('type', 'video')->value();
        $type = in_array($type, ['video', 'audio'], true) ? $type : 'video';

        $assets = $muxService->listAssets(25, $page, $type);

        $existingByAssetId = Media::query()
            ->where('provider', 'mux')
            ->whereIn('mux_asset_id', collect($assets)->pluck('asset_id'))
            ->get()
            ->keyBy('mux_asset_id');

        $items = collect($assets)->map(function ($asset) use ($existingByAssetId) {
            $existing = $existingByAssetId->get($asset['asset_id']);

            return [
                'asset_id' => $asset['asset_id'],
                'playback_id' => $asset['playback_id'],
                'duration' => $asset['duration'],
                'created_at' => $asset['created_at'],
                'thumbnail_url' => $asset['playback_id'] ? "https://image.mux.com/{$asset['playback_id']}/thumbnail.png?width=160" : null,
                'title' => $existing?->title,
            ];
        })->values();

        return response()->json(['data' => $items, 'page' => $page]);
    }

    public function fromMux(Request $request, MuxService $muxService): JsonResponse
    {
        $validated = $request->validate([
            'asset_id' => ['required', 'string'],
            'type' => ['nullable', 'string', 'in:video,audio'],
            'title' => ['nullable', 'string', 'max:255'],
        ]);

        $type = $validated['type'] ?? 'video';

        $asset = $muxService->findAsset($validated['asset_id']);

        abort_unless($asset && $asset['playback_id'], 422, 'That Mux file is not available.');

        $media = Media::create([
            'type' => $type,
            'source_type' => 'upload',
            'provider' => 'mux',
            'mux_asset_id' => $asset['asset_id'],
            'mux_playback_id' => $asset['playback_id'],
            'mux_ready' => true,
            'title' => $validated['title'] ?? ('Existing Mux '.$type),
            'created_by' => Auth::id(),
        ]);

        return response()->json([
            'id' => $media->id,
            'type' => $media->type,
            'title' => $media->title,
            'provider' => $media->provider,
            'ready' => true,
            'resolved_url' => $media->resolved_url,
        ]);
    }
}
