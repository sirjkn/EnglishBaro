<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Section extends Model
{
    /** @use HasFactory<\Database\Factories\SectionFactory> */
    use HasFactory;

    /**
     * Every level carries exactly these four sections, in this order.
     */
    public const TYPES = ['grammar', 'listening', 'speaking', 'reading'];

    protected $fillable = ['level_id', 'type', 'title', 'description', 'order'];

    public static function titleFor(string $type): string
    {
        return ucfirst($type);
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class);
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class)->orderBy('order');
    }
}
