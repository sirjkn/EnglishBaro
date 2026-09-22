<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrackProgress extends Model
{
    protected $table = 'track_progress';

    protected $fillable = [
        'user_id',
        'track_id',
        'enrollment_id',
        'lessons_completed',
        'total_lessons',
        'levels_completed',
        'total_levels',
        'percent_complete',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'percent_complete' => 'decimal:2',
            'completed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function track(): BelongsTo
    {
        return $this->belongsTo(Track::class);
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function isComplete(): bool
    {
        return $this->completed_at !== null;
    }
}
