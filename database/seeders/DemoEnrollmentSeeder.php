<?php

namespace Database\Seeders;

use App\Models\Enrollment;
use App\Models\LessonProgress;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\Track;
use App\Models\TrackProgress;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoEnrollmentSeeder extends Seeder
{
    public function run(): void
    {
        $student = User::query()->where('email', 'student@englishbaro.test')->first();
        $track = Track::query()->where('track_code', 'A1')->first();

        if (! $student || ! $track) {
            return;
        }

        $enrollment = Enrollment::query()->updateOrCreate(
            ['user_id' => $student->id, 'track_id' => $track->id],
            ['status' => 'active', 'enrolled_at' => now()->subDays(4)]
        );

        // Tracks are prepaid in full up front, so this enrollment must be backed
        // by a real (successful) Payment record — visible in Payment History,
        // not shown as an "upcoming" payment since it's already been paid.
        $payment = Payment::query()->updateOrCreate(
            ['user_id' => $student->id, 'track_id' => $track->id],
            [
                'transaction_id' => 'DEMO-'.Str::upper(Str::random(10)),
                'amount' => $track->priceForRegion('Africa'),
                'currency' => $track->currency,
                'region' => 'Africa',
                'payment_method' => 'flutterwave',
                'status' => 'successful',
                'subscription_days' => $track->subscription_days,
                'verified_at' => now()->subDays(4),
                'created_at' => now()->subDays(4),
            ]
        );

        $subscription = Subscription::query()->updateOrCreate(
            ['enrollment_id' => $enrollment->id],
            [
                'user_id' => $student->id,
                'track_id' => $track->id,
                'payment_id' => $payment->id,
                'starts_at' => now()->subDays(4),
                'expires_at' => now()->addDays($track->subscription_days - 4),
                'duration_days' => $track->subscription_days,
                'status' => 'active',
                'region' => 'Africa',
            ]
        );

        $firstLevel = $track->levels()->first();

        if (! $firstLevel) {
            return;
        }

        $lessons = $firstLevel->lessons()->get();
        $completedLessons = $lessons->take(3);

        foreach ($completedLessons as $lesson) {
            LessonProgress::query()->updateOrCreate(
                ['user_id' => $student->id, 'lesson_id' => $lesson->id],
                [
                    'enrollment_id' => $enrollment->id,
                    'status' => 'completed',
                    'video_progress_seconds' => $lesson->duration_seconds,
                    'started_at' => now()->subDays(3),
                    'completed_at' => now()->subDays(3),
                    'last_viewed_at' => now()->subDays(3),
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

        $totalLessons = $track->lessonCount();
        $totalLevels = $track->levels()->count();
        $percent = $totalLessons > 0 ? round(($completedLessons->count() / $totalLessons) * 100, 2) : 0;

        TrackProgress::query()->updateOrCreate(
            ['user_id' => $student->id, 'track_id' => $track->id],
            [
                'enrollment_id' => $enrollment->id,
                'lessons_completed' => $completedLessons->count(),
                'total_lessons' => $totalLessons,
                'levels_completed' => 0,
                'total_levels' => $totalLevels,
                'percent_complete' => $percent,
            ]
        );
    }
}
