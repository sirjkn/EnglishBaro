<?php

namespace Tests\Feature;

use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\Level;
use App\Models\Section;
use App\Models\Subscription;
use App\Models\Track;
use App\Models\TrackProgress;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrackStructureTest extends TestCase
{
    use RefreshDatabase;

    private function track(): Track
    {
        return Track::factory()->create([
            'track_code' => 'A1',
            'status' => 'published',
            'subscription_days' => 120,
        ]);
    }

    private function levelWithLessons(Track $track, int $number = 1): Level
    {
        $level = Level::factory()->create([
            'track_id' => $track->id,
            'number' => $number,
            'title' => "Level {$number}",
        ]);

        $level->ensureSections();

        foreach ($level->sections as $section) {
            Lesson::factory()->create(['section_id' => $section->id]);
        }

        return $level->fresh();
    }

    private function enrol(User $user, Track $track): Enrollment
    {
        $enrollment = Enrollment::create([
            'user_id' => $user->id,
            'track_id' => $track->id,
            'status' => 'active',
            'enrolled_at' => now(),
        ]);

        Subscription::create([
            'user_id' => $user->id,
            'track_id' => $track->id,
            'enrollment_id' => $enrollment->id,
            'starts_at' => now(),
            'expires_at' => now()->addDays($track->subscription_days),
            'duration_days' => $track->subscription_days,
            'status' => 'active',
        ]);

        return $enrollment;
    }

    public function test_every_level_gets_the_four_fixed_sections(): void
    {
        $track = $this->track();
        $level = $this->levelWithLessons($track);

        $this->assertSame(Section::TYPES, $level->sections->pluck('type')->all());
        $this->assertSame(4, $level->lessons()->count());
    }

    public function test_guest_can_browse_the_track_catalog_and_a_track(): void
    {
        $track = $this->track();
        $this->levelWithLessons($track);

        $this->get(route('tracks.index'))->assertOk()->assertSee($track->name);
        $this->get(route('tracks.show', $track))->assertOk()->assertSee('Level 1');
        $this->get(route('tracks.level', [$track, $track->levels()->first()]))->assertOk()->assertSee('Grammar');
    }

    public function test_subscribed_student_can_open_a_lesson_and_complete_it(): void
    {
        $track = $this->track();
        $level = $this->levelWithLessons($track);
        $student = User::factory()->create(['user_type' => 'student']);
        $this->enrol($student, $track);

        $lesson = $level->lessons()->first();

        $this->actingAs($student)
            ->get(route('student.tracks.learn', [$track, $level, $lesson]))
            ->assertOk()
            ->assertSee($lesson->title);

        $this->actingAs($student)
            ->post(route('student.tracks.complete-lesson', [$track, $level, $lesson]))
            ->assertRedirect();

        $progress = TrackProgress::where('user_id', $student->id)->where('track_id', $track->id)->first();

        $this->assertNotNull($progress);
        $this->assertSame(1, $progress->lessons_completed);
        $this->assertSame(4, $progress->total_lessons);
        $this->assertSame(1, $progress->total_levels);
    }

    public function test_student_without_a_subscription_cannot_learn(): void
    {
        $track = $this->track();
        $level = $this->levelWithLessons($track);
        $student = User::factory()->create(['user_type' => 'student']);

        $this->actingAs($student)
            ->get(route('student.tracks.learn', [$track, $level, $level->lessons()->first()]))
            ->assertForbidden();
    }

    public function test_enrolling_in_one_track_does_not_unlock_another(): void
    {
        $a1 = $this->track();
        $b1 = Track::factory()->create(['track_code' => 'B1', 'status' => 'published']);
        $otherLevel = $this->levelWithLessons($b1);

        $student = User::factory()->create(['user_type' => 'student']);
        $this->enrol($student, $a1);

        $this->actingAs($student)
            ->get(route('student.tracks.level', [$b1, $otherLevel]))
            ->assertForbidden();
    }
}
