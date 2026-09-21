<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\CourseProgress;
use App\Models\Enrollment;
use App\Models\LessonProgress;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoEnrollmentSeeder extends Seeder
{
    public function run(): void
    {
        $student = User::query()->where('email', 'student@englishbaro.test')->first();
        $course = Course::query()->where('slug', 'english-foundations-for-beginners')->first();

        if (! $student || ! $course) {
            return;
        }

        $enrollment = Enrollment::query()->updateOrCreate(
            ['user_id' => $student->id, 'course_id' => $course->id],
            ['status' => 'active', 'enrolled_at' => now()->subDays(10)]
        );

        Subscription::query()->updateOrCreate(
            ['enrollment_id' => $enrollment->id],
            [
                'user_id' => $student->id,
                'course_id' => $course->id,
                'starts_at' => now()->subDays(10),
                'expires_at' => now()->addDays(110),
                'duration_days' => 120,
                'status' => 'active',
            ]
        );

        $lessons = $course->lessons()->orderBy('order')->get();
        $totalLessons = $lessons->count();
        $completedLessons = $lessons->take(3);

        foreach ($completedLessons as $lesson) {
            LessonProgress::query()->updateOrCreate(
                ['user_id' => $student->id, 'lesson_id' => $lesson->id],
                [
                    'enrollment_id' => $enrollment->id,
                    'status' => 'completed',
                    'video_progress_seconds' => $lesson->duration_seconds,
                    'started_at' => now()->subDays(9),
                    'completed_at' => now()->subDays(8),
                    'last_viewed_at' => now()->subDays(8),
                ]
            );
        }

        $nextLesson = $lessons->skip(3)->first();

        if ($nextLesson) {
            LessonProgress::query()->updateOrCreate(
                ['user_id' => $student->id, 'lesson_id' => $nextLesson->id],
                [
                    'enrollment_id' => $enrollment->id,
                    'status' => 'started',
                    'video_progress_seconds' => 60,
                    'started_at' => now()->subHours(2),
                    'last_viewed_at' => now()->subHours(2),
                ]
            );

            $enrollment->update([
                'last_viewed_lesson_id' => $nextLesson->id,
                'last_viewed_at' => now()->subHours(2),
            ]);
        }

        $percent = $totalLessons > 0 ? round(($completedLessons->count() / $totalLessons) * 100, 2) : 0;

        CourseProgress::query()->updateOrCreate(
            ['user_id' => $student->id, 'course_id' => $course->id],
            [
                'enrollment_id' => $enrollment->id,
                'lessons_completed' => $completedLessons->count(),
                'total_lessons' => $totalLessons,
                'percent_complete' => $percent,
            ]
        );
    }
}
