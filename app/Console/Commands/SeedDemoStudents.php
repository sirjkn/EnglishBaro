<?php

namespace App\Console\Commands;

use App\Models\Enrollment;
use App\Models\LessonProgress;
use App\Models\Media;
use App\Models\Payment;
use App\Models\Role;
use App\Models\StudentProfile;
use App\Models\Subscription;
use App\Models\Track;
use App\Models\User;
use App\Services\StudentIdGenerator;
use App\Services\TrackProgressService;
use App\Support\PhoneNumberFormatter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SeedDemoStudents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:seed-demo-students';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'One-off: creates 5 demo students across different countries, tracks and levels, each with a profile photo';

    /**
     * @var array<int, array<string, mixed>>
     */
    private const STUDENTS = [
        [
            'name' => 'Amina Njeri',
            'email' => 'amina.njeri@demo.englishbaro.test',
            'phone' => '0712345671',
            'country' => 'Kenya',
            'gender' => 'female',
            'track_code' => 'A1',
            'level_number' => 3,
            'avatar_seed' => 'amina-njeri',
        ],
        [
            'name' => 'Chidi Okafor',
            'email' => 'chidi.okafor@demo.englishbaro.test',
            'phone' => '08031234567',
            'country' => 'Nigeria',
            'gender' => 'male',
            'track_code' => 'A2',
            'level_number' => 12,
            'avatar_seed' => 'chidi-okafor',
        ],
        [
            'name' => 'Priya Sharma',
            'email' => 'priya.sharma@demo.englishbaro.test',
            'phone' => '9876543210',
            'country' => 'India',
            'gender' => 'female',
            'track_code' => 'B1',
            'level_number' => 21,
            'avatar_seed' => 'priya-sharma',
        ],
        [
            'name' => 'James Carter',
            'email' => 'james.carter@demo.englishbaro.test',
            'phone' => '07911123456',
            'country' => 'United Kingdom',
            'gender' => 'male',
            'track_code' => 'B2',
            'level_number' => 5,
            'avatar_seed' => 'james-carter',
        ],
        [
            'name' => 'Maria Garcia',
            'email' => 'maria.garcia@demo.englishbaro.test',
            'phone' => '2025550164',
            'country' => 'United States',
            'gender' => 'female',
            'track_code' => 'A1',
            'level_number' => 40,
            'avatar_seed' => 'maria-garcia',
        ],
    ];

    public function handle(StudentIdGenerator $studentIdGenerator, TrackProgressService $trackProgressService): int
    {
        $studentRole = Role::query()->where('slug', 'student')->first();

        foreach (self::STUDENTS as $data) {
            $track = Track::query()->where('track_code', $data['track_code'])->first();

            if (! $track) {
                $this->warn("Track {$data['track_code']} not found — skipping {$data['name']}.");

                continue;
            }

            $this->info("Seeding {$data['name']} ({$data['country']}, {$track->track_code} level {$data['level_number']})...");

            DB::transaction(function () use ($data, $track, $studentRole, $studentIdGenerator, $trackProgressService) {
                $user = User::query()->updateOrCreate(
                    ['email' => $data['email']],
                    [
                        'name' => $data['name'],
                        'password' => Hash::make('Demo@12345'),
                        'user_type' => 'student',
                        'is_active' => true,
                        'email_verified_at' => now(),
                    ]
                );

                if ($studentRole && ! $user->roles()->where('roles.id', $studentRole->id)->exists()) {
                    $user->roles()->attach($studentRole->id);
                }

                $mediaId = $this->fetchAvatar($data['avatar_seed'], $data['name']);

                $profile = StudentProfile::query()->updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'student_id' => $user->studentProfile?->student_id ?? $this->uniqueStudentId($studentIdGenerator),
                        'phone' => PhoneNumberFormatter::normalize($data['phone'], $data['country']),
                        'country' => $data['country'],
                        'gender' => $data['gender'],
                        'track_id' => $track->id,
                        'profile_image_id' => $mediaId,
                    ]
                );

                $this->enrollAcrossCourse($user, $track);

                $enrollment = Enrollment::query()->where('user_id', $user->id)->where('track_id', $track->id)->first();

                $this->advanceToLevel($user, $track, $enrollment, (int) $data['level_number']);

                $trackProgressService->recalculate($user, $track, $enrollment);
            });
        }

        $this->info('Done.');

        return self::SUCCESS;
    }

    private function uniqueStudentId(StudentIdGenerator $generator): string
    {
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $candidate = $generator->generate();

            if (! StudentProfile::query()->where('student_id', $candidate)->exists()) {
                return $candidate;
            }
        }

        return $generator->generate().'-'.uniqid();
    }

    /**
     * Downloads a realistic placeholder headshot (pravatar.cc, seeded so the
     * same student always gets the same photo) and stores it as local Media,
     * the same way an admin-uploaded profile photo is stored.
     */
    private function fetchAvatar(string $seed, string $name): ?int
    {
        $existing = Media::query()->where('title', "Demo avatar: {$name}")->first();

        if ($existing) {
            return $existing->id;
        }

        $url = 'https://i.pravatar.cc/400?u='.urlencode($seed);
        $contents = $this->curlGet($url);

        if ($contents === null) {
            return null;
        }

        $filename = Str::uuid()->toString().'.jpg';
        $path = "media/image/{$filename}";

        Storage::disk('public')->put($path, $contents);

        $media = Media::create([
            'type' => 'image',
            'source_type' => 'upload',
            'provider' => 'local',
            'storage_path' => $path,
            'original_filename' => $filename,
            'mime_type' => 'image/jpeg',
            'file_size' => strlen($contents),
            'title' => "Demo avatar: {$name}",
        ]);

        return $media->id;
    }

    /**
     * file_get_contents() can't be relied on for remote URLs — allow_url_fopen
     * is disabled on production — so this goes through cURL directly instead.
     */
    private function curlGet(string $url): ?string
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

        $body = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($body === false || $status !== 200) {
            return null;
        }

        return $body;
    }

    /**
     * Mirrors what a real course payment produces: an active Enrollment +
     * Subscription for every published track in the course, backed by one
     * successful Payment, so the demo student's access matches a paying
     * student's.
     */
    private function enrollAcrossCourse(User $user, Track $track): void
    {
        $course = $track->course;
        $courseTracks = $course->tracks()->where('status', 'published')->get();

        $payment = Payment::query()->updateOrCreate(
            ['user_id' => $user->id, 'course_id' => $course->id],
            [
                'transaction_id' => 'DEMO-'.Str::upper(Str::random(10)),
                'track_id' => $track->id,
                'amount' => $course->priceForRegion('Africa'),
                'currency' => $course->currency,
                'region' => 'Africa',
                'payment_method' => 'flutterwave',
                'status' => 'successful',
                'subscription_days' => $course->subscription_days,
                'verified_at' => now()->subDays(10),
            ]
        );

        $payment->forceFill(['created_at' => now()->subDays(10)])->save();

        foreach ($courseTracks as $courseTrack) {
            $enrollment = Enrollment::query()->updateOrCreate(
                ['user_id' => $user->id, 'track_id' => $courseTrack->id],
                ['status' => 'active', 'enrolled_at' => now()->subDays(10)]
            );

            Subscription::query()->updateOrCreate(
                ['enrollment_id' => $enrollment->id],
                [
                    'user_id' => $user->id,
                    'track_id' => $courseTrack->id,
                    'payment_id' => $payment->id,
                    'starts_at' => now()->subDays(10),
                    'expires_at' => now()->addDays($course->subscription_days - 10),
                    'duration_days' => $course->subscription_days,
                    'status' => 'active',
                    'region' => 'Africa',
                ]
            );
        }
    }

    /**
     * Marks every lesson up to (but not including) the target level as
     * completed, and the target level's first lesson as in progress — so
     * the student shows up genuinely "at" that level, not past it.
     */
    private function advanceToLevel(User $user, Track $track, Enrollment $enrollment, int $levelNumber): void
    {
        $levels = $track->levels()->with('sections.lessons')->orderBy('number')->get();

        $priorLessons = $levels->filter(fn ($level) => $level->number < $levelNumber)
            ->flatMap(fn ($level) => $level->sections->flatMap->lessons);

        foreach ($priorLessons as $lesson) {
            LessonProgress::query()->updateOrCreate(
                ['user_id' => $user->id, 'lesson_id' => $lesson->id],
                [
                    'enrollment_id' => $enrollment->id,
                    'status' => 'completed',
                    'video_progress_seconds' => $lesson->duration_seconds ?? 0,
                    'started_at' => now()->subDays(9),
                    'completed_at' => now()->subDays(9),
                    'last_viewed_at' => now()->subDays(9),
                ]
            );
        }

        $targetLevel = $levels->firstWhere('number', $levelNumber);
        $currentLesson = $targetLevel?->sections->flatMap->lessons->first();

        if ($currentLesson) {
            LessonProgress::query()->updateOrCreate(
                ['user_id' => $user->id, 'lesson_id' => $currentLesson->id],
                [
                    'enrollment_id' => $enrollment->id,
                    'status' => 'started',
                    'video_progress_seconds' => 45,
                    'started_at' => now()->subHours(3),
                    'last_viewed_at' => now()->subHours(3),
                ]
            );

            $enrollment->update([
                'last_viewed_lesson_id' => $currentLesson->id,
                'last_viewed_at' => now()->subHours(3),
            ]);
        }
    }
}
