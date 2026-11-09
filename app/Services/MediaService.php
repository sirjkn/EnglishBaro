<?php

namespace App\Services;

use App\Models\Media;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class MediaService
{
    private const IMAGE_MIMES = ['jpg', 'jpeg', 'png', 'webp'];

    private const VIDEO_MIMES = ['mp4', 'webm'];

    private const AUDIO_MIMES = ['mp3', 'wav', 'm4a'];

    private const DOCUMENT_MIMES = ['pdf', 'epub'];

    public function __construct(private readonly MuxService $muxService) {}

    public function storeUploadedFile(UploadedFile $file, string $type, ?string $title = null, bool $forceLocal = false): Media
    {
        $this->validateUploadedFile($file, $type);

        if (in_array($type, ['video', 'audio'], true) && ! $forceLocal && $this->muxConfigured()) {
            return $this->storeOnMux($file, $type, $title);
        }

        $disk = 'public';
        $directory = "media/{$type}";

        if ($type === 'image') {
            // Re-encoding (rather than storing the uploaded bytes as-is)
            // strips EXIF/GPS metadata and anything hidden outside the
            // actual pixel data, and the resize caps decompression-bomb
            // sized images.
            [$path, $mime] = $this->storeReencodedImage($file, $directory, $disk);
        } else {
            $path = $file->store($directory, $disk);
            $mime = $file->getClientMimeType();
        }

        return Media::create([
            'type' => $type,
            'source_type' => 'upload',
            'provider' => 'local',
            'storage_path' => $path,
            'original_filename' => $file->getClientOriginalName(),
            'mime_type' => $mime,
            'file_size' => Storage::disk($disk)->size($path),
            'title' => $title,
            'created_by' => Auth::id(),
        ]);
    }

    private function muxConfigured(): bool
    {
        return filled(config('services.mux.token_id')) && filled(config('services.mux.token_secret'));
    }

    private function storeOnMux(UploadedFile $file, string $type, ?string $title = null): Media
    {
        $result = $this->muxService->uploadFile($file);

        return Media::create([
            'type' => $type,
            'source_type' => 'upload',
            'provider' => 'mux',
            'mux_asset_id' => $result['asset_id'],
            'mux_playback_id' => $result['playback_id'],
            'mux_ready' => $result['ready'],
            'original_filename' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
            'title' => $title,
            'created_by' => Auth::id(),
        ]);
    }

    public function storeExternalImage(string $url, ?string $title = null): Media
    {
        $url = $this->validateExternalUrl($url);

        $provider = str_contains($url, 'imgur.com') ? 'imgur' : 'other';

        return Media::create([
            'type' => 'image',
            'source_type' => 'external',
            'provider' => $provider,
            'url' => $url,
            'title' => $title,
            'created_by' => Auth::id(),
        ]);
    }

    public function storeExternalVideo(string $url, ?string $title = null): Media
    {
        $url = $this->validateExternalUrl($url);

        $youtubeId = $this->extractYouTubeId($url);

        if ($youtubeId) {
            return Media::create([
                'type' => 'video',
                'source_type' => 'external',
                'provider' => 'youtube',
                'url' => $url,
                'external_id' => $youtubeId,
                'title' => $title,
                'created_by' => Auth::id(),
            ]);
        }

        return Media::create([
            'type' => 'video',
            'source_type' => 'external',
            'provider' => 'other',
            'url' => $url,
            'title' => $title,
            'created_by' => Auth::id(),
        ]);
    }

    public function extractYouTubeId(string $url): ?string
    {
        $patterns = [
            '/youtube\.com\/watch\?v=([a-zA-Z0-9_-]{11})/',
            '/youtu\.be\/([a-zA-Z0-9_-]{11})/',
            '/youtube\.com\/embed\/([a-zA-Z0-9_-]{11})/',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url, $matches)) {
                return $matches[1];
            }
        }

        return null;
    }

    public function validateExternalUrl(string $url): string
    {
        $url = trim($url);

        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            throw ValidationException::withMessages(['url' => 'Please provide a valid URL.']);
        }

        $scheme = parse_url($url, PHP_URL_SCHEME);

        if ($scheme !== 'https') {
            throw ValidationException::withMessages(['url' => 'Only secure (https) URLs are allowed.']);
        }

        if (Str::startsWith(Str::lower($url), 'javascript:')) {
            throw ValidationException::withMessages(['url' => 'Invalid URL.']);
        }

        return $url;
    }

    private function validateUploadedFile(UploadedFile $file, string $type): void
    {
        $extension = Str::lower($file->getClientOriginalExtension());

        $allowed = match ($type) {
            'image' => self::IMAGE_MIMES,
            'video' => self::VIDEO_MIMES,
            'audio' => self::AUDIO_MIMES,
            'document', 'ebook' => self::DOCUMENT_MIMES,
            default => [],
        };

        if (! in_array($extension, $allowed, true)) {
            throw ValidationException::withMessages([
                'file' => 'Unsupported file type. Allowed: '.implode(', ', $allowed),
            ]);
        }

        $maxKilobytes = match ($type) {
            'image' => 5 * 1024,
            'video' => 200 * 1024,
            'audio' => 100 * 1024,
            'document', 'ebook' => 50 * 1024,
            default => 5 * 1024,
        };

        if ($file->getSize() > $maxKilobytes * 1024) {
            throw ValidationException::withMessages([
                'file' => "File is too large. Maximum size is {$maxKilobytes} KB.",
            ]);
        }

        // The extension is just a label the uploader chose — confirm the
        // file's actual bytes match what it claims to be, so a disguised or
        // mismatched file can't pass as an image or document.
        if ($type === 'image' && ! $this->detectImageSignature($file)) {
            throw ValidationException::withMessages([
                'file' => 'That file is not a real JPG, PNG or WEBP image.',
            ]);
        }

        if (in_array($type, ['document', 'ebook'], true) && ! $this->documentSignatureMatches($file, $extension)) {
            throw ValidationException::withMessages([
                'file' => 'That file does not match its extension.',
            ]);
        }
    }

    /**
     * Reads the file's real signature bytes rather than trusting its name or
     * declared MIME type (both client-supplied and trivially spoofed).
     */
    private function detectImageSignature(UploadedFile $file): ?string
    {
        $bytes = @file_get_contents($file->getRealPath(), false, null, 0, 12);

        if ($bytes === false || strlen($bytes) < 4) {
            return null;
        }

        if (substr($bytes, 0, 3) === "\xFF\xD8\xFF") {
            return 'jpg';
        }

        if (substr($bytes, 0, 8) === "\x89\x50\x4E\x47\x0D\x0A\x1A\x0A") {
            return 'png';
        }

        if (strlen($bytes) >= 12 && substr($bytes, 0, 4) === 'RIFF' && substr($bytes, 8, 4) === 'WEBP') {
            return 'webp';
        }

        return null;
    }

    private function documentSignatureMatches(UploadedFile $file, string $extension): bool
    {
        $bytes = @file_get_contents($file->getRealPath(), false, null, 0, 8);

        if ($bytes === false) {
            return false;
        }

        return match ($extension) {
            'pdf' => str_starts_with($bytes, '%PDF-'),
            // EPUB is a ZIP container; a ZIP signature is the most that can
            // be confirmed here without unpacking it to read the mimetype
            // entry inside — a real but narrower check than for images.
            'epub' => str_starts_with($bytes, "PK\x03\x04"),
            default => true,
        };
    }

    private function storeReencodedImage(UploadedFile $file, string $directory, string $disk): array
    {
        $signature = $this->detectImageSignature($file);

        $source = match ($signature) {
            'jpg' => @imagecreatefromjpeg($file->getRealPath()),
            'png' => @imagecreatefrompng($file->getRealPath()),
            'webp' => @imagecreatefromwebp($file->getRealPath()),
            default => null,
        };

        if (! $source) {
            throw ValidationException::withMessages(['file' => 'That image could not be processed.']);
        }

        $width = imagesx($source);
        $height = imagesy($source);
        $maxSide = 2000;

        if (max($width, $height) > $maxSide) {
            $scale = $maxSide / max($width, $height);
            $newWidth = max(1, (int) round($width * $scale));
            $newHeight = max(1, (int) round($height * $scale));
            $resized = imagecreatetruecolor($newWidth, $newHeight);

            if ($signature !== 'jpg') {
                imagealphablending($resized, false);
                imagesavealpha($resized, true);
            }

            imagecopyresampled($resized, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
            imagedestroy($source);
            $source = $resized;
        }

        $mime = ['jpg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp'][$signature];
        $filename = Str::uuid()->toString().'.'.$signature;
        $path = "{$directory}/{$filename}";
        $tmpPath = tempnam(sys_get_temp_dir(), 'img');

        match ($signature) {
            'jpg' => imagejpeg($source, $tmpPath, 85),
            'png' => imagepng($source, $tmpPath, 6),
            'webp' => imagewebp($source, $tmpPath, 85),
        };

        imagedestroy($source);
        $contents = file_get_contents($tmpPath);
        @unlink($tmpPath);

        Storage::disk($disk)->put($path, $contents);

        return [$path, $mime];
    }

    public function delete(Media $media): void
    {
        if ($media->isMux() && $media->mux_asset_id) {
            $this->muxService->deleteAsset($media->mux_asset_id);
        } elseif ($media->source_type === 'upload' && $media->storage_path) {
            Storage::disk('public')->delete($media->storage_path);
        }

        $media->delete();
    }
}
