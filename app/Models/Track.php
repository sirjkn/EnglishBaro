<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Track extends Model
{
    /** @use HasFactory<\Database\Factories\TrackFactory> */
    use HasFactory;

    /**
     * The four CEFR tracks the platform sells.
     */
    public const CODES = ['A1', 'A2', 'B1', 'B2'];

    protected $fillable = [
        'course_id',
        'name',
        'slug',
        'track_code',
        'description',
        'learning_outcomes',
        'thumbnail_id',
        'status',
        'order',
        'is_default',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Pricing now lives on the parent course — a track (unit) is unlocked as
     * part of buying the whole course, not sold on its own. These delegate
     * so any existing caller asking a track its price still gets a sensible
     * answer.
     */
    public function priceForRegion(?string $region): float
    {
        return $this->course->priceForRegion($region);
    }

    public function defaultPrice(): float
    {
        return $this->course->defaultPrice();
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function levels(): HasMany
    {
        return $this->hasMany(Level::class)->orderBy('number');
    }

    public function sections(): HasManyThrough
    {
        return $this->hasManyThrough(Section::class, Level::class);
    }

    public function lessons(): Builder
    {
        return Lesson::query()->whereIn(
            'section_id',
            Section::query()->select('id')->whereIn(
                'level_id',
                Level::query()->select('id')->where('track_id', $this->id)
            )
        );
    }

    public function thumbnail(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'thumbnail_id');
    }

    public function studentProfiles(): HasMany
    {
        return $this->hasMany(StudentProfile::class);
    }

    public function ebooks(): HasMany
    {
        return $this->hasMany(Ebook::class);
    }

    public function resources(): HasMany
    {
        return $this->hasMany(Resource::class);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'enrollments')->withPivot('status', 'enrolled_at')->withTimestamps();
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }

    public function lessonCount(): int
    {
        return $this->lessons()->count();
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    public function getRouteKeyName(): string
    {
        return 'track_code';
    }
}
