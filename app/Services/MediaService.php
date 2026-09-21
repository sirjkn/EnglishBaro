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

    private const DOCUMENT_MIMES = ['pdf', 'epub'];

    public function storeUploadedFile(UploadedFile $file, string $type, ?string $title = null): Media
    {
        $this->validateUploadedFile($file, $type);

        $disk = 'public';
        $directory = "media/{$type}";
        $path = $file->store($directory, $disk);

        return Media::create([
            'type' => $type,
            'source_type' => 'upload',
            'provider' => 'local',
            'storage_path' => $path,
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
            'document', 'ebook' => 50 * 1024,
            default => 5 * 1024,
        };

        if ($file->getSize() > $maxKilobytes * 1024) {
            throw ValidationException::withMessages([
                'file' => "File is too large. Maximum size is {$maxKilobytes} KB.",
            ]);
        }
    }

    public function delete(Media $media): void
    {
        if ($media->source_type === 'upload' && $media->storage_path) {
            Storage::disk('public')->delete($media->storage_path);
        }

        $media->delete();
    }
}
