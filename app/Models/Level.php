<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Level extends Model
{
    /** @use HasFactory<\Database\Factories\LevelFactory> */
    use HasFactory;

    protected $fillable = ['track_id', 'number', 'title'];

    public function track(): BelongsTo
    {
        return $this->belongsTo(Track::class);
    }

    public function sections(): HasMany
    {
        return $this->hasMany(Section::class)->orderBy('order');
    }

    public function assessments(): HasMany
    {
        return $this->hasMany(Assessment::class);
    }

    public function lessons(): HasManyThrough
    {
        return $this->hasManyThrough(Lesson::class, Section::class, 'level_id', 'section_id')
            ->orderBy('sections.order')
            ->orderBy('lessons.order');
    }

    /**
     * Create the four fixed skill sections a level must always have.
     */
    public function ensureSections(): void
    {
        foreach (array_values(Section::TYPES) as $order => $type) {
            $this->sections()->firstOrCreate(
                ['type' => $type],
                ['title' => Section::titleFor($type), 'order' => $order]
            );
        }
    }

    public function getRouteKeyName(): string
    {
        return 'number';
    }
}
