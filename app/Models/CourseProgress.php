<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseProgress extends Model
{
    protected $table = 'course_progress';

    protected $fillable = [
        'user_id',
        'course_id',
        'enrollment_id',
        'lessons_completed',
        'total_lessons',
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

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
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
