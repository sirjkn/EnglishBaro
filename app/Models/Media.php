<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Media extends Model
{
    protected $fillable = [
        'type',
        'source_type',
        'provider',
        'url',
        'external_id',
        'mux_asset_id',
        'mux_playback_id',
        'mux_ready',
        'storage_path',
        'original_filename',
        'mime_type',
        'file_size',
        'alt_text',
        'title',
        'created_by',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function resources(): HasMany
    {
        return $this->hasMany(LessonResource::class);
    }

    public function isExternal(): bool
    {
        return $this->source_type === 'external';
    }

    public function isMux(): bool
    {
        return $this->provider === 'mux';
    }

    protected function casts(): array
    {
        return [
            'mux_ready' => 'boolean',
        ];
    }

    public function getResolvedUrlAttribute(): ?string
    {
        if ($this->isMux()) {
            return $this->mux_playback_id ? "https://stream.mux.com/{$this->mux_playback_id}.m3u8" : null;
        }

        if ($this->isExternal()) {
            return $this->url;
        }

        return $this->storage_path ? asset('storage/'.$this->storage_path) : null;
    }
}
