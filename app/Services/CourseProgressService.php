<?php

namespace App\Services;

use App\Models\Certificate;
use App\Models\Course;
use App\Models\CourseProgress;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Support\Str;

class CourseProgressService
{
    public function recalculate(User $user, Course $course, Enrollment $enrollment): CourseProgress
    {
        $totalLessons = $course->lessons()->count();
        $completedLessons = $enrollment->id
            ? \App\Models\LessonProgress::query()
                ->where('enrollment_id', $enrollment->id)
                ->where('status', 'completed')
                ->count()
            : 0;

        $percent = $totalLessons > 0 ? round(($completedLessons / $totalLessons) * 100, 2) : 0;

        $progress = CourseProgress::query()->updateOrCreate(
            ['user_id' => $user->id, 'course_id' => $course->id],
            [
                'enrollment_id' => $enrollment->id,
                'lessons_completed' => $completedLessons,
                'total_lessons' => $totalLessons,
                'percent_complete' => $percent,
            ]
        );

        if ($percent >= 100 && ! $progress->completed_at) {
            $progress->update(['completed_at' => now()]);
            $this->issueCertificateIfMissing($user, $course);
        }

        return $progress;
    }

    private function issueCertificateIfMissing(User $user, Course $course): void
    {
        $exists = Certificate::query()
            ->where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->exists();

        if ($exists) {
            return;
        }

        Certificate::create([
            'certificate_number' => 'EB-CERT-'.Str::upper(Str::random(10)),
            'user_id' => $user->id,
            'course_id' => $course->id,
            'issued_at' => now(),
        ]);
    }
}
