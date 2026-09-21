<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\Level;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Course>
 */
class CourseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->unique()->words(3, true).' English Course';

        return [
            'title' => Str::title($title),
            'slug' => Str::slug($title).'-'.fake()->unique()->numberBetween(1000, 9999),
            'description' => fake()->paragraphs(3, true),
            'learning_outcomes' => fake()->paragraph(),
            'level_id' => Level::factory(),
            'price' => fake()->randomElement([19.99, 29.99, 39.99, 49.99, 59.99]),
            'currency' => 'USD',
            'duration_days' => 120,
            'subscription_days' => 120,
            'status' => 'published',
            'is_featured' => fake()->boolean(30),
        ];
    }
}
