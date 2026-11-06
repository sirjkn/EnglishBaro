<?php

namespace Database\Seeders;

use App\Models\Course;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CourseSeeder extends Seeder
{
    public function run(): void
    {
        Course::query()->updateOrCreate(
            ['course_code' => 'ENG'],
            [
                'name' => 'English',
                'slug' => Str::slug('English'),
                'description' => 'Learn English from absolute beginner to advanced, one unit at a time.',
                'learning_outcomes' => 'By the end of this course you will handle everyday English content with confidence across grammar, listening, speaking and reading.',
                'price' => 24.99,
                'price_africa' => 24.99,
                'price_europe' => 39.99,
                'price_north_america' => 44.99,
                'price_asia' => 29.99,
                'currency' => 'USD',
                // One payment unlocks every unit (track) in the course for 120 days.
                'subscription_days' => 120,
                'status' => 'published',
                'is_featured' => true,
                'order' => 1,
                'is_default' => true,
                'is_active' => true,
            ]
        );
    }
}
