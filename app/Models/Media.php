<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Media extends Model
{
    protected $fillable = [
        'type',
        'source_type',
        'provider',
        'url',
        'external_id',
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

    public function isExternal(): bool
    {
        return $this->source_type === 'external';
    }

    public function getResolvedUrlAttribute(): ?string
    {
        if ($this->isExternal()) {
            return $this->url;
        }

        return $this->storage_path ? asset('storage/'.$this->storage_path) : null;
    }
}
