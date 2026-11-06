<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Media;
use App\Models\Track;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TrackSeeder extends Seeder
{
    public function run(): void
    {
        $courseId = Course::query()->where('course_code', 'ENG')->value('id');

        if (! $courseId) {
            return;
        }

        $tracks = [
            // 'thumb_url' is only set for tracks whose thumbnail this seeder
            // owns and should keep refreshing. B1 is left out on purpose: it
            // already carries a hand-picked thumbnail that must never be
            // overwritten by a reseed.
            ['code' => 'A1', 'name' => 'English A1', 'order' => 1, 'is_default' => true, 'thumb_url' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/c/c2/Professor_and_students_in_a_university_classroom_in_Tennessee.jpg/960px-Professor_and_students_in_a_university_classroom_in_Tennessee.jpg'],
            ['code' => 'A2', 'name' => 'English A2', 'order' => 2, 'thumb_url' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/f/ff/Smiling_Student_Working_on_Assignments_at_Desk.jpg/960px-Smiling_Student_Working_on_Assignments_at_Desk.jpg'],
            ['code' => 'B1', 'name' => 'English B1', 'order' => 3],
            ['code' => 'B2', 'name' => 'English B2', 'order' => 4, 'thumb_url' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/3/38/Innopolis_University_Lecture_Hall.jpg/960px-Innopolis_University_Lecture_Hall.jpg'],
        ];

        foreach ($tracks as $data) {
            $track = Track::query()->updateOrCreate(
                ['track_code' => $data['code']],
                [
                    'course_id' => $courseId,
                    'name' => $data['name'],
                    'slug' => Str::slug($data['code'].'-'.$data['name']),
                    'description' => "The {$data['code']} ({$data['name']}) unit: 100 levels of grammar, listening, speaking and reading, taught step by step.",
                    'learning_outcomes' => "By the end of the {$data['code']} unit you will handle everyday {$data['name']} level content with confidence across all four skills.",
                    'status' => 'published',
                    'order' => $data['order'],
                    'is_default' => $data['is_default'] ?? false,
                    'is_active' => true,
                ]
            );

            if (isset($data['thumb_url'])) {
                $thumbnail = Media::query()->updateOrCreate(
                    ['url' => $data['thumb_url']],
                    [
                        'type' => 'image',
                        'source_type' => 'external',
                        'provider' => 'other',
                        'alt_text' => "{$data['name']} track thumbnail",
                        'title' => "{$data['code']} education-themed thumbnail",
                    ]
                );

                $track->update(['thumbnail_id' => $thumbnail->id]);
            }
        }
    }
}
