<?php

namespace Database\Factories;

use App\Models\Course;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\CourseSection>
 */
class CourseSectionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'course_id' => Course::factory(),
            'title' => 'Section: '.fake()->words(2, true),
            'description' => fake()->sentence(),
            'order' => 0,
        ];
    }
}
