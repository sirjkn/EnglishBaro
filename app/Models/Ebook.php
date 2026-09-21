<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ebook extends Model
{
    protected $fillable = ['course_id', 'title', 'description', 'file_media_id', 'cover_media_id'];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
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
