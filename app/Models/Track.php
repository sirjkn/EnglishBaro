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
        'name',
        'slug',
        'track_code',
        'description',
        'learning_outcomes',
        'thumbnail_id',
        'price',
        'price_africa',
        'price_europe',
        'price_north_america',
        'price_asia',
        'currency',
        'subscription_days',
        'status',
        'is_featured',
        'seo_title',
        'seo_description',
        'order',
        'is_default',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'price_africa' => 'decimal:2',
            'price_europe' => 'decimal:2',
            'price_north_america' => 'decimal:2',
            'price_asia' => 'decimal:2',
            'is_featured' => 'boolean',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function priceForRegion(?string $region): float
    {
        $column = match ($region) {
            'Africa' => 'price_africa',
            'Europe' => 'price_europe',
            'North America' => 'price_north_america',
            'Asia' => 'price_asia',
            default => null,
        };

        if ($column && $this->{$column} !== null) {
            return (float) $this->{$column};
        }

        return (float) $this->price;
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
