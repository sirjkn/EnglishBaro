<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lesson extends Model
{
    use HasFactory;

    protected $fillable = [
        'section_id',
        'title',
        'description',
        'video_media_id',
        'ebook_id',
        'order',
        'duration_seconds',
        'is_preview',
    ];

    protected function casts(): array
    {
        return [
            'is_preview' => 'boolean',
        ];
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function video(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'video_media_id');
    }

    public function ebook(): BelongsTo
    {
        return $this->belongsTo(Ebook::class);
    }

    public function resources(): HasMany
    {
        return $this->hasMany(LessonResource::class)->orderBy('order');
    }

    public function progress(): HasMany
    {
        return $this->hasMany(LessonProgress::class);
    }
}
