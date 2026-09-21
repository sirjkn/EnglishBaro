<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\MediaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class MediaServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_stores_an_uploaded_image(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create());

        $file = UploadedFile::fake()->create('photo.jpg', 500, 'image/jpeg');

        $media = app(MediaService::class)->storeUploadedFile($file, 'image');

        $this->assertSame('upload', $media->source_type);
        $this->assertSame('local', $media->provider);
        Storage::disk('public')->assertExists($media->storage_path);
    }

    public function test_it_rejects_oversized_uploads(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create());

        $file = UploadedFile::fake()->create('photo.jpg', 6000, 'image/jpeg');

        $this->expectException(ValidationException::class);

        app(MediaService::class)->storeUploadedFile($file, 'image');
    }

    public function test_it_stores_an_external_imgur_image(): void
    {
        $this->actingAs(User::factory()->create());

        $media = app(MediaService::class)->storeExternalImage('https://i.imgur.com/example.jpg');

        $this->assertSame('external', $media->source_type);
        $this->assertSame('imgur', $media->provider);
    }

    public function test_it_extracts_youtube_video_id(): void
    {
        $this->actingAs(User::factory()->create());

        $media = app(MediaService::class)->storeExternalVideo('https://www.youtube.com/watch?v=dQw4w9WgXcQ');

        $this->assertSame('youtube', $media->provider);
        $this->assertSame('dQw4w9WgXcQ', $media->external_id);
    }

    public function test_it_extracts_youtube_short_url_id(): void
    {
        $this->actingAs(User::factory()->create());

        $media = app(MediaService::class)->storeExternalVideo('https://youtu.be/dQw4w9WgXcQ');

        $this->assertSame('dQw4w9WgXcQ', $media->external_id);
    }

    public function test_it_rejects_non_https_external_urls(): void
    {
        $this->actingAs(User::factory()->create());

        $this->expectException(ValidationException::class);

        app(MediaService::class)->storeExternalImage('http://i.imgur.com/example.jpg');
    }

    public function test_it_rejects_javascript_urls(): void
    {
        $this->actingAs(User::factory()->create());

        $this->expectException(ValidationException::class);

        app(MediaService::class)->storeExternalImage('javascript:alert(1)');
    }
}
