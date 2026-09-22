<?php

namespace Database\Factories;

use App\Models\Section;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Lesson>
 */
class LessonFactory extends Factory
{
    public function definition(): array
    {
        return [
            'section_id' => Section::factory(),
            'title' => 'Lesson: '.fake()->words(3, true),
            'description' => fake()->paragraph(),
            'order' => 0,
            'duration_seconds' => fake()->numberBetween(180, 900),
            'is_preview' => false,
        ];
    }
}
