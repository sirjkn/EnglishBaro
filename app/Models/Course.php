<?php

namespace App\Models;

use Database\Factories\CourseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Course extends Model
{
    /** @use HasFactory<CourseFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'learning_outcomes',
        'level_id',
        'thumbnail_id',
        'price',
        'price_africa',
        'price_europe',
        'price_north_america',
        'price_asia',
        'currency',
        'duration_days',
        'subscription_days',
        'status',
        'is_featured',
        'seo_title',
        'seo_description',
        'created_by',
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

    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class);
    }

    public function thumbnail(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'thumbnail_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function sections(): HasMany
    {
        return $this->hasMany(CourseSection::class)->orderBy('order');
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class)->orderBy('order');
    }

    public function ebooks(): HasMany
    {
        return $this->hasMany(Ebook::class);
    }

    public function resources(): HasMany
    {
        return $this->hasMany(Resource::class);
    }

    public function assessments(): HasMany
    {
        return $this->hasMany(Assessment::class);
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

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
