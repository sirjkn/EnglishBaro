<?php

namespace App\Services;

use App\Models\Enrollment;
use App\Models\Level;
use App\Models\LessonProgress;

class LevelAccessService
{
    /**
     * Checkpoint levels a student may jump straight into: level 1, and every
     * 10th level after it (10, 20, ... 100). Any other level (e.g. 34, 45)
     * can only be reached by finishing the level right before it.
     */
    public function isCheckpoint(int $number): bool
    {
        return $number === 1 || $number % 10 === 0;
    }

    public function canAccess(Enrollment $enrollment, Level $level): bool
    {
        if ($this->isCheckpoint($level->number)) {
            return true;
        }

        $previousLevel = Level::query()
            ->where('track_id', $level->track_id)
            ->where('number', $level->number - 1)
            ->first();

        if (! $previousLevel) {
            return true;
        }

        return $this->isLevelCompleted($enrollment, $previousLevel);
    }

    /**
     * The nearest checkpoint at or before this level, used to redirect a
     * student who tries to jump straight into a locked mid-decade level.
     */
    public function nearestCheckpointNumber(int $number): int
    {
        return $number === 1 ? 1 : intdiv($number - 1, 10) * 10;
    }

    private function isLevelCompleted(Enrollment $enrollment, Level $level): bool
    {
        $lessonIds = $level->lessons()->pluck('lessons.id');

        if ($lessonIds->isEmpty()) {
            return false;
        }

        $completedCount = LessonProgress::query()
            ->where('enrollment_id', $enrollment->id)
            ->where('status', 'completed')
            ->whereIn('lesson_id', $lessonIds)
            ->count();

        return $completedCount === $lessonIds->count();
    }
}
