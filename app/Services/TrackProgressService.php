<?php

namespace App\Services;

use App\Models\Certificate;
use App\Models\Enrollment;
use App\Models\LessonProgress;
use App\Models\Track;
use App\Models\TrackProgress;
use App\Models\User;
use Illuminate\Support\Str;

class TrackProgressService
{
    public function recalculate(User $user, Track $track, Enrollment $enrollment): TrackProgress
    {
        $lessonIds = $track->lessons()->pluck('lessons.id');
        $totalLessons = $lessonIds->count();

        $completedLessonIds = LessonProgress::query()
            ->where('enrollment_id', $enrollment->id)
            ->where('status', 'completed')
            ->whereIn('lesson_id', $lessonIds)
            ->pluck('lesson_id');

        $completedLessons = $completedLessonIds->count();
        $percent = $totalLessons > 0 ? round(($completedLessons / $totalLessons) * 100, 2) : 0;

        $levels = $track->levels()->with('sections.lessons:id,section_id')->get();
        $totalLevels = $levels->count();

        $levelsCompleted = $levels->filter(function ($level) use ($completedLessonIds) {
            $ids = $level->sections->flatMap->lessons->pluck('id');

            return $ids->isNotEmpty() && $ids->diff($completedLessonIds)->isEmpty();
        })->count();

        $progress = TrackProgress::query()->updateOrCreate(
            ['user_id' => $user->id, 'track_id' => $track->id],
            [
                'enrollment_id' => $enrollment->id,
                'lessons_completed' => $completedLessons,
                'total_lessons' => $totalLessons,
                'levels_completed' => $levelsCompleted,
                'total_levels' => $totalLevels,
                'percent_complete' => $percent,
            ]
        );

        if ($percent >= 100 && ! $progress->completed_at) {
            $progress->update(['completed_at' => now()]);
            $this->issueCertificateIfMissing($user, $track);
            $this->unlockNextTrackIfCurrent($user, $track);
        }

        return $progress;
    }

    /**
     * A student may only enroll up to the track they were placed into. When
     * they finish that ceiling track, promote them to the next one so they
     * can enroll in it too — without this, TrackPolicy::enroll would keep
     * them stuck on a track they've already completed. An admin can still
     * move a student to any track manually at any time.
     */
    private function unlockNextTrackIfCurrent(User $user, Track $track): void
    {
        $profile = $user->studentProfile;

        if (! $profile || $profile->track_id !== $track->id) {
            return;
        }

        $nextTrack = Track::query()->where('order', '>', $track->order)->orderBy('order')->first();

        if ($nextTrack) {
            $profile->update(['track_id' => $nextTrack->id]);
        }
    }

    private function issueCertificateIfMissing(User $user, Track $track): void
    {
        $exists = Certificate::query()
            ->where('user_id', $user->id)
            ->where('track_id', $track->id)
            ->exists();

        if ($exists) {
            return;
        }

        Certificate::create([
            'certificate_number' => 'EB-CERT-'.Str::upper(Str::random(10)),
            'user_id' => $user->id,
            'track_id' => $track->id,
            'issued_at' => now(),
        ]);
    }
}
