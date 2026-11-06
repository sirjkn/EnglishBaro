<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<\App\Models\Course>
 */
class CourseFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->word();

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1000, 9999),
            'course_code' => Str::upper(fake()->unique()->lexify('???')),
            'description' => fake()->paragraph(),
            'learning_outcomes' => fake()->paragraph(),
            'price' => fake()->randomElement([19.99, 29.99, 39.99, 49.99]),
            'currency' => 'USD',
            'subscription_days' => 120,
            'status' => 'published',
            'is_featured' => true,
            'order' => fake()->unique()->numberBetween(1, 1000),
            'is_default' => false,
            'is_active' => true,
        ];
    }
}
