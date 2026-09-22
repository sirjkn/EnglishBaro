<?php

namespace App\Services;

use App\Models\Course;
use App\Models\Level;
use Illuminate\Support\Facades\DB;

class CourseCodeGenerator
{
    /**
     * Generate the next sequential course code for a level, e.g. A2001, A2002.
     * Falls back to a generic "GEN" prefix when the level has no code.
     */
    public function generate(?Level $level): string
    {
        $prefix = $level?->code ?: 'GEN';

        return DB::transaction(function () use ($prefix) {
            $last = Course::query()
                ->where('course_code', 'like', "{$prefix}%")
                ->lockForUpdate()
                ->orderByDesc('course_code')
                ->value('course_code');

            $nextNumber = 1;

            if ($last && preg_match('/(\d+)$/', $last, $matches)) {
                $nextNumber = ((int) $matches[1]) + 1;
            }

            $candidate = sprintf('%s%03d', $prefix, $nextNumber);

            while (Course::query()->where('course_code', $candidate)->exists()) {
                $nextNumber++;
                $candidate = sprintf('%s%03d', $prefix, $nextNumber);
            }

            return $candidate;
        });
    }
}
