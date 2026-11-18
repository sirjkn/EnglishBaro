<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlacementQuestion extends Model
{
    protected $fillable = ['question', 'type', 'media_id', 'order'];

    public const TYPES = ['single_choice', 'multiple_choice'];

    public function options(): HasMany
    {
        return $this->hasMany(PlacementOption::class)->orderBy('order');
    }

    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }

    public function isMultipleChoice(): bool
    {
        return $this->type === 'multiple_choice';
    }
}
