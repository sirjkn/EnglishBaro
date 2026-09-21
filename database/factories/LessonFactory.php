<?php

namespace Database\Factories;

use App\Models\CourseSection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Lesson>
 */
class LessonFactory extends Factory
{
    public function definition(): array
    {
        $section = CourseSection::factory();

        return [
            'course_section_id' => $section,
            'course_id' => fn (array $attributes) => CourseSection::find($attributes['course_section_id'])?->course_id
                ?? \App\Models\Course::factory(),
            'title' => 'Lesson: '.fake()->words(3, true),
            'description' => fake()->paragraph(),
            'order' => 0,
            'duration_seconds' => fake()->numberBetween(180, 900),
            'is_preview' => false,
        ];
    }
}
