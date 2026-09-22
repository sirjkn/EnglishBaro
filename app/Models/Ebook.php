<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ebook extends Model
{
    protected $fillable = ['track_id', 'title', 'description', 'file_media_id', 'cover_media_id'];

    public function track(): BelongsTo
    {
        return $this->belongsTo(Track::class);
    }

    public function file(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'file_media_id');
    }

    public function cover(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'cover_media_id');
    }
}
