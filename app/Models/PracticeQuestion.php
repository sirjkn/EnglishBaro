<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PracticeQuestion extends Model
{
    protected $fillable = ['category', 'track_id', 'question', 'order'];

    public function track(): BelongsTo
    {
        return $this->belongsTo(Track::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(PracticeOption::class)->orderBy('order');
    }
}
