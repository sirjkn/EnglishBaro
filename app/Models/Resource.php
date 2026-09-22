<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Resource extends Model
{
    protected $fillable = ['track_id', 'lesson_id', 'title', 'description', 'media_id', 'order'];

    public function track(): BelongsTo
    {
        return $this->belongsTo(Track::class);
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }
}
