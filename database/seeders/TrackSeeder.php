<?php

namespace Database\Seeders;

use App\Models\Media;
use App\Models\Track;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TrackSeeder extends Seeder
{
    /**
     * Regional price multipliers relative to the base price, derived from the
     * reference pricing example (Africa $25 / Europe $40 / North America $45 / Asia $30).
     */
    private const REGION_MULTIPLIERS = [
        'price_africa' => 1.0,
        'price_europe' => 1.6,
        'price_north_america' => 1.8,
        'price_asia' => 1.2,
    ];

    public function run(): void
    {
        $tracks = [
            // 'thumb_url' is only set for tracks whose thumbnail this seeder
            // owns and should keep refreshing. B1 is left out on purpose: it
            // already carries a hand-picked thumbnail that must never be
            // overwritten by a reseed.
            ['code' => 'A1', 'name' => 'English A1', 'price' => 24.99, 'order' => 1, 'is_default' => true, 'thumb_url' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/c/c2/Professor_and_students_in_a_university_classroom_in_Tennessee.jpg/960px-Professor_and_students_in_a_university_classroom_in_Tennessee.jpg'],
            ['code' => 'A2', 'name' => 'English A2', 'price' => 29.99, 'order' => 2, 'thumb_url' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/f/ff/Smiling_Student_Working_on_Assignments_at_Desk.jpg/960px-Smiling_Student_Working_on_Assignments_at_Desk.jpg'],
            ['code' => 'B1', 'name' => 'English B1', 'price' => 34.99, 'order' => 3],
            ['code' => 'B2', 'name' => 'English B2', 'price' => 39.99, 'order' => 4, 'thumb_url' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/3/38/Innopolis_University_Lecture_Hall.jpg/960px-Innopolis_University_Lecture_Hall.jpg'],
            ['code' => 'C1', 'name' => 'English C1', 'price' => 44.99, 'order' => 5, 'thumb_url' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/a/ab/AnnapolisGraduation.jpg/960px-AnnapolisGraduation.jpg'],
            ['code' => 'C2', 'name' => 'English C2', 'price' => 49.99, 'order' => 6, 'thumb_url' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/f/ff/Bookshelves_in_Hove_Library_2025-08-20.jpg/960px-Bookshelves_in_Hove_Library_2025-08-20.jpg'],
            ['code' => 'ES', 'name' => 'Spanish', 'price' => 29.99, 'order' => 7, 'thumb_url' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/a/a8/Madrid_%28Spain%29_%2833115369301%29.jpg/960px-Madrid_%28Spain%29_%2833115369301%29.jpg'],
            ['code' => 'CN', 'name' => 'Chinese', 'price' => 29.99, 'order' => 8, 'thumb_url' => 'https://upload.wikimedia.org/wikipedia/commons/5/55/Red_lanterns%2C_Spring_Festival%2C_Ditan_Park_Beijing.JPG'],
            ['code' => 'SW', 'name' => 'Swahili', 'price' => 29.99, 'order' => 9, 'thumb_url' => 'https://upload.wikimedia.org/wikipedia/commons/2/22/Stone_Town%2C_Zanzibar%2C_2021%2C_36.jpg'],
        ];

        foreach ($tracks as $data) {
            $regionalPrices = collect(self::REGION_MULTIPLIERS)
                ->mapWithKeys(fn ($multiplier, $column) => [$column => round($data['price'] * $multiplier, 2)])
                ->all();

            $track = Track::query()->updateOrCreate(
                ['track_code' => $data['code']],
                [
                    'name' => $data['name'],
                    'slug' => Str::slug($data['code'].'-'.$data['name']),
                    'description' => "The {$data['code']} ({$data['name']}) track: 100 levels of grammar, listening, speaking and reading, taught step by step.",
                    'learning_outcomes' => "By the end of the {$data['code']} track you will handle everyday {$data['name']} level content with confidence across all four skills.",
                    'price' => $data['price'],
                    'currency' => 'USD',
                    // One payment unlocks all 100 levels of the track for 120 days.
                    'subscription_days' => 120,
                    'status' => 'published',
                    'is_featured' => true,
                    'order' => $data['order'],
                    'is_default' => $data['is_default'] ?? false,
                    'is_active' => true,
                    ...$regionalPrices,
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
