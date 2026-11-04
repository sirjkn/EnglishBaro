<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class MuxService
{
    private const API_BASE = 'https://api.mux.com';

    private const POLL_ATTEMPTS = 15;

    private const POLL_DELAY_SECONDS = 1;

    /**
     * Upload a file to Mux and wait briefly for the asset to be created.
     *
     * @return array{asset_id: ?string, playback_id: ?string, ready: bool}
     */
    public function uploadFile(UploadedFile $file): array
    {
        $upload = $this->client()->post('/video/v1/uploads', [
            'new_asset_settings' => [
                'playback_policy' => ['public'],
            ],
            'cors_origin' => '*',
        ]);

        if ($upload->failed()) {
            throw ValidationException::withMessages([
                'file' => 'Could not start the upload to Mux. Please try again.',
            ]);
        }

        $uploadId = $upload->json('data.id');
        $uploadUrl = $upload->json('data.url');

        $put = Http::withBody(file_get_contents($file->getRealPath()), $file->getMimeType() ?: 'application/octet-stream')
            ->timeout(1200)
            ->connectTimeout(30)
            ->put($uploadUrl);

        if ($put->failed()) {
            throw ValidationException::withMessages([
                'file' => 'Uploading the file to Mux failed. Please try again.',
            ]);
        }

        $assetId = $this->pollForAssetId($uploadId);

        if (! $assetId) {
            return ['asset_id' => null, 'playback_id' => null, 'ready' => false];
        }

        return $this->pollForPlaybackId($assetId);
    }

    private function pollForAssetId(string $uploadId): ?string
    {
        for ($i = 0; $i < self::POLL_ATTEMPTS; $i++) {
            $response = $this->client()->get("/video/v1/uploads/{$uploadId}");

            $assetId = $response->json('data.asset_id');

            if ($assetId) {
                return $assetId;
            }

            sleep(self::POLL_DELAY_SECONDS);
        }

        return null;
    }

    /**
     * @return array{asset_id: string, playback_id: ?string, ready: bool}
     */
    private function pollForPlaybackId(string $assetId): array
    {
        for ($i = 0; $i < self::POLL_ATTEMPTS; $i++) {
            $response = $this->client()->get("/video/v1/assets/{$assetId}");

            $status = $response->json('data.status');
            $playbackId = $response->json('data.playback_ids.0.id');

            if ($playbackId && $status === 'ready') {
                return ['asset_id' => $assetId, 'playback_id' => $playbackId, 'ready' => true];
            }

            if ($playbackId) {
                $readyPlaybackId = $playbackId;
            }

            sleep(self::POLL_DELAY_SECONDS);
        }

        return ['asset_id' => $assetId, 'playback_id' => $readyPlaybackId ?? null, 'ready' => false];
    }

    public function deleteAsset(string $assetId): void
    {
        $this->client()->delete("/video/v1/assets/{$assetId}");
    }

    /**
     * List existing Mux assets (for picking a previously uploaded video or
     * audio file instead of uploading the same one again). $type filters to
     * assets that do/don't have a video track, since Mux stores audio-only
     * uploads as video assets with just an audio track.
     *
     * @return array<int, array{asset_id: string, playback_id: ?string, duration: ?float, created_at: ?string}>
     */
    public function listAssets(int $limit = 25, int $page = 1, string $type = 'video'): array
    {
        $response = $this->client()->get('/video/v1/assets', [
            'limit' => $limit,
            'page' => $page,
        ]);

        if ($response->failed()) {
            return [];
        }

        return collect($response->json('data', []))
            ->filter(fn ($asset) => ($asset['status'] ?? null) === 'ready' && ! empty($asset['playback_ids']))
            ->filter(function ($asset) use ($type) {
                $hasVideoTrack = collect($asset['tracks'] ?? [])->contains(fn ($track) => ($track['type'] ?? null) === 'video');

                return $type === 'audio' ? ! $hasVideoTrack : $hasVideoTrack;
            })
            ->map(fn ($asset) => [
                'asset_id' => $asset['id'],
                'playback_id' => $asset['playback_ids'][0]['id'] ?? null,
                'duration' => $asset['duration'] ?? null,
                'created_at' => $asset['created_at'] ?? null,
            ])
            ->values()
            ->all();
    }

    public function findAsset(string $assetId): ?array
    {
        $response = $this->client()->get("/video/v1/assets/{$assetId}");

        if ($response->failed()) {
            return null;
        }

        $asset = $response->json('data');

        if (($asset['status'] ?? null) !== 'ready' || empty($asset['playback_ids'])) {
            return null;
        }

        return [
            'asset_id' => $asset['id'],
            'playback_id' => $asset['playback_ids'][0]['id'] ?? null,
        ];
    }

    private function client()
    {
        return Http::baseUrl(self::API_BASE)
            ->withBasicAuth(config('services.mux.token_id'), config('services.mux.token_secret'))
            ->acceptJson();
    }
}
