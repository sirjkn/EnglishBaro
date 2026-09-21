<?php

namespace App\Livewire\Admin;

use App\Models\Media;
use App\Services\MediaService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Modelable;
use Livewire\Component;
use Livewire\WithFileUploads;

class MediaSelector extends Component
{
    use WithFileUploads;

    public string $type = 'image';

    public string $label = 'Media';

    #[Modelable]
    public ?int $mediaId = null;

    public string $source = 'upload';

    public $file = null;

    public string $externalUrl = '';

    public ?Media $current = null;

    public function mount(): void
    {
        if ($this->mediaId) {
            $this->current = Media::find($this->mediaId);
            $this->source = $this->current?->source_type === 'external' ? 'external' : 'upload';
        }
    }

    public function updatedFile(): void
    {
        $this->validate([
            'file' => $this->type === 'video' ? 'file|max:204800' : 'file|max:5120',
        ]);
    }

    public function attach(MediaService $mediaService): void
    {
        if ($this->source === 'upload' && $this->file) {
            $media = $mediaService->storeUploadedFile($this->file, $this->type);
        } elseif ($this->source === 'external' && $this->externalUrl) {
            $media = $this->type === 'video'
                ? $mediaService->storeExternalVideo($this->externalUrl)
                : $mediaService->storeExternalImage($this->externalUrl);
        } else {
            $this->addError('file', 'Please upload a file or provide an external URL.');

            return;
        }

        $this->mediaId = $media->id;
        $this->current = $media;
        $this->file = null;
        $this->externalUrl = '';

        $this->dispatch('media-attached', mediaId: $media->id);
    }

    public function remove(): void
    {
        $this->mediaId = null;
        $this->current = null;
    }

    public function render()
    {
        return view('livewire.admin.media-selector');
    }
}
