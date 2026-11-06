<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'course_code',
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

    /**
     * Columns holding this course's per-region overrides.
     */
    public const REGION_PRICE_COLUMNS = [
        'price_africa',
        'price_europe',
        'price_north_america',
        'price_asia',
    ];

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

        return $this->defaultPrice();
    }

    /**
     * The base price used whenever no regional override applies: the highest
     * of the base price and any configured regional price, unless an admin
     * has explicitly set a lower base price.
     */
    public function defaultPrice(): float
    {
        $regionalPrices = collect(self::REGION_PRICE_COLUMNS)
            ->map(fn ($column) => $this->{$column})
            ->filter(fn ($value) => $value !== null)
            ->map(fn ($value) => (float) $value);

        return $regionalPrices->isEmpty()
            ? (float) $this->price
            : max((float) $this->price, $regionalPrices->max());
    }

    public function tracks(): HasMany
    {
        return $this->hasMany(Track::class)->orderBy('order');
    }

    public function thumbnail(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'thumbnail_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * The track a student lands on first — the one placement test results
     * and "View Course" links point to.
     */
    public function defaultTrack(): ?Track
    {
        return $this->tracks->firstWhere('is_default', true) ?? $this->tracks->first();
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    public function getRouteKeyName(): string
    {
        return 'course_code';
    }
}
